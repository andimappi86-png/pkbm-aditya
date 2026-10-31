<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnswerQuizRequest;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class QuizAttemptController extends Controller
{
    public function start(Request $request, Quiz $quiz)
    {
        $user = $request->user();
        $student = $user?->student;

        if ($user && $user->hasRole('peserta-didik') && $user->can('quizzes.take') && $student && $student->rombel_id === $quiz->penugasanMengajar->classroom_id && $quiz->isOpen()) {
            $existing = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', $student->id)
                ->where('selesai', false)
                ->first();

            if ($existing && ! $existing->isExpired()) {
                return redirect()->route('quiz.attempts.show', [$quiz, $existing]);
            }

            $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', $student->id)
                ->count();

            if ($attemptCount >= $quiz->max_attempts) {
                $latest = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('student_id', $student->id)
                    ->latest('id')
                    ->first();

                if ($latest) {
                    return redirect()->route('quiz.attempts.show', [$quiz, $latest]);
                }
            }
        }

        Gate::authorize('create', [QuizAttempt::class, $quiz]);

        abort_if($student === null, 403);
        abort_if(! $quiz->isOpen(), 403);

        try {
            $attempt = DB::transaction(function () use ($quiz, $student) {
                // Lock quiz row to serialize concurrent start requests
                Quiz::whereKey($quiz->id)->lockForUpdate()->first();

                // Check existing incomplete attempt
                $existing = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('student_id', $student->id)
                    ->where('selesai', false)
                    ->first();

                if ($existing && ! $existing->isExpired()) {
                    return $existing;
                }

                // Check max attempts
                $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('student_id', $student->id)
                    ->count();

                if ($attemptCount >= $quiz->max_attempts) {
                    abort(403, 'Batas percobaan telah tercapai.');
                }

                // Get next attempt number
                $attemptNumber = $attemptCount + 1;

                return QuizAttempt::create([
                    'quiz_id' => $quiz->id,
                    'student_id' => $student->id,
                    'mulai_waktu' => now(),
                    'selesai' => false,
                    'attempt_number' => $attemptNumber,
                ]);
            });
        } catch (QueryException $exception) {
            if (! $this->isDuplicateQuizAttemptViolation($exception)) {
                throw $exception;
            }

            $attempt = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', $student->id)
                ->first();

            if ($attempt === null) {
                throw $exception;
            }
        }

        return redirect()->route('quiz.attempts.show', [$quiz, $attempt]);
    }

    private function isDuplicateQuizAttemptViolation(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());

        if (! in_array($sqlState, ['23000', '23505'], true)) {
            return false;
        }

        $message = $exception->getMessage();

        return str_contains($message, 'quiz_attempts_quiz_id_student_id_unique')
            || str_contains($message, 'UNIQUE constraint failed: quiz_attempts.quiz_id, quiz_attempts.student_id');
    }

    public function show(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        abort_if($attempt->quiz_id !== $quiz->id, 404);
        Gate::authorize('view', $attempt);

        if ($attempt->selesai) {
            return $this->viewResult($quiz, $attempt);
        }

        // Check if quiz is still open
        if (! $quiz->isOpen()) {
            return $this->processSubmit($attempt);
        }

        // Check if attempt expired
        if ($attempt->isExpired()) {
            return $this->processSubmit($attempt);
        }

        $quiz->load(['questions' => function ($q) {
            $q->with('options')->orderBy('urutan');
        }]);

        // For student view, shuffle questions if acak_soal is true
        if (auth()->user()->hasRole('peserta-didik') && $quiz->acak_soal) {
            $quiz->setRelation('questions', $quiz->questions->shuffle()->values());
        }

        // Calculate time left (server authority) - use model method
        $timeLeft = $attempt->getRemainingSeconds();

        // Load existing answers
        $existingAnswers = $attempt->answers->keyBy('question_id');

        return view('quizzes.attempts.show', compact('quiz', 'attempt', 'existingAnswers', 'timeLeft'));
    }

    public function answer(AnswerQuizRequest $request, Quiz $quiz, QuizAttempt $attempt)
    {
        abort_if($attempt->quiz_id !== $quiz->id, 404);
        Gate::authorize('update', $attempt);

        // Check if quiz is still open
        if (! $quiz->isOpen()) {
            return response()->json(['success' => false, 'message' => 'Waktu kuis sudah berakhir.'], 403);
        }

        // Check if attempt expired
        if ($attempt->isExpired()) {
            return response()->json(['success' => false, 'message' => 'Waktu pengerjaan habis.'], 403);
        }

        $validated = $request->validated();

        foreach ($validated['answers'] as $answerData) {
            $question = Question::find($answerData['question_id']);

            if (! $question || $question->quiz_id !== $quiz->id) {
                continue;
            }

            $updateData = [];

            if ($question->tipe === 'pilihan_ganda' && isset($answerData['option_id'])) {
                $updateData['option_id'] = $answerData['option_id'];
                $updateData['answer_text'] = null;
            } elseif ($question->tipe === 'esai') {
                $updateData['answer_text'] = $answerData['answer_text'] ?? null;
                $updateData['option_id'] = null;
            } else {
                $updateData['answer_text'] = $answerData['answer_text'] ?? null;
                $updateData['option_id'] = $answerData['option_id'] ?? null;
            }

            if (isset($answerData['media_path'])) {
                $updateData['media_path'] = $answerData['media_path'];
            }

            QuizAnswer::updateOrCreate(
                ['quiz_attempt_id' => $attempt->id, 'question_id' => $question->id],
                $updateData
            );
        }

        return response()->json(['success' => true]);
    }

    public function submit(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        abort_if($attempt->quiz_id !== $quiz->id, 404);
        Gate::authorize('submit', $attempt);

        return $this->processSubmit($attempt);
    }

    public function viewResult(Quiz $quiz, QuizAttempt $attempt)
    {
        abort_if($attempt->quiz_id !== $quiz->id, 404);
        Gate::authorize('viewResult', $attempt);

        $quiz->load('questions.options');
        $attempt->load('answers.question');

        $canViewAnswers = Gate::allows('viewAnswers', $attempt);

        return view('quizzes.attempts.result', compact('quiz', 'attempt', 'canViewAnswers'));
    }

    private function processSubmit(QuizAttempt $attempt)
    {
        if ($attempt->selesai) {
            return redirect()->route('quiz.attempts.show', [$attempt->quiz, $attempt]);
        }

        $quiz = $attempt->quiz;
        $quiz->load('questions.options');
        $answers = $attempt->answers;

        $totalScore = 0;
        $maxScore = $quiz->questions->sum('bobot');

        foreach ($quiz->questions as $question) {
            $answer = $answers->where('question_id', $question->id)->first();

            if (! $answer) {
                continue;
            }

            if (in_array($question->tipe, ['pilihan_ganda', 'benar_salah']) && $answer->option_id) {
                $option = $question->options->where('id', $answer->option_id)->first();
                if ($option && $option->is_benar) {
                    $answer->update(['benar' => true]);
                    $totalScore += $question->bobot;
                } else {
                    $answer->update(['benar' => false]);
                }
            } elseif ($question->tipe === 'isian_singkat' && $answer->answer_text) {
                $correctOption = $question->options->first();
                $isCorrect = $correctOption && trim(strtolower((string) $answer->answer_text)) === trim(strtolower((string) $correctOption->teks));
                $answer->update(['benar' => $isCorrect]);
                if ($isCorrect) {
                    $totalScore += $question->bobot;
                }
            }
            // Essay questions are not auto-graded, marked as pending
        }

        $finalScore = $maxScore > 0 ? round(($totalScore / $maxScore) * 100) : 0;

        $attempt->update([
            'selesai_waktu' => now(),
            'selesai' => true,
            'nilai' => $finalScore,
        ]);

        return redirect()->route('quiz.attempts.show', [$quiz, $attempt])
            ->with('success', 'Kuis berhasil diselesaikan. Nilai pilihan ganda: '.$finalScore.($quiz->questions->where('tipe', 'esai')->count() > 0 ? ' (esai menunggu penilaian)' : ''));
    }
}
