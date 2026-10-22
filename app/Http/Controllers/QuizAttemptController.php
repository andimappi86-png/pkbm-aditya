<?php

namespace App\Http\Controllers;

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
        Gate::authorize('create', [QuizAttempt::class, $quiz]);

        $user = $request->user();
        $student = $user->student;
        abort_if($student === null, 403);
        abort_if(! $quiz->status_aktif, 403);

        try {
            $attempt = DB::transaction(function () use ($quiz, $student) {
                // Serialize concurrent start requests for the same quiz, then re-check.
                Quiz::whereKey($quiz->id)->lockForUpdate()->first();

                $existing = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('student_id', $student->id)
                    ->first();

                if ($existing) {
                    return $existing;
                }

                return QuizAttempt::create([
                    'quiz_id' => $quiz->id,
                    'student_id' => $student->id,
                    'mulai_waktu' => now(),
                    'selesai' => false,
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

    /**
     * Detect only the unique-index violation for one attempt per quiz and student.
     */
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
            return view('quizzes.attempts.result', compact('quiz', 'attempt'));
        }

        $quiz->load('questions.options');

        // Cek waktu
        $elapsed = now()->diffInMinutes($attempt->mulai_waktu);
        if ($elapsed >= $quiz->durasi_menit) {
            return $this->processSubmit($attempt);
        }

        return view('quizzes.attempts.show', compact('quiz', 'attempt'));
    }

    public function answer(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        abort_if($attempt->quiz_id !== $quiz->id, 404);
        Gate::authorize('update', $attempt);

        $validated = $request->validate([
            'answers' => 'array',
        ]);

        if (isset($validated['answers'])) {
            foreach ($validated['answers'] as $qId => $optId) {
                $question = Question::find($qId);
                if ($question && $question->quiz_id === $quiz->id) {
                    QuizAnswer::updateOrCreate(
                        ['quiz_attempt_id' => $attempt->id, 'question_id' => $qId],
                        ['option_id' => $optId]
                    );
                }
            }
        }

        return response()->json(['success' => true]);
    }

    public function submit(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        abort_if($attempt->quiz_id !== $quiz->id, 404);
        Gate::authorize('update', $attempt);

        return $this->processSubmit($attempt);
    }

    private function processSubmit(QuizAttempt $attempt)
    {
        $quiz = $attempt->quiz;
        $quiz->load('questions.options');
        $answers = $attempt->answers;

        $totalScore = 0;
        $maxScore = $quiz->questions->sum('bobot');

        foreach ($quiz->questions as $question) {
            $answer = $answers->where('question_id', $question->id)->first();

            if ($question->tipe === 'pilihan_ganda' && $answer && $answer->option_id) {
                $option = $question->options->where('id', $answer->option_id)->first();
                if ($option && $option->is_benar) {
                    $answer->update(['benar' => true]);
                    $totalScore += $question->bobot;
                } else {
                    $answer->update(['benar' => false]);
                }
            }
        }

        $finalScore = $maxScore > 0 ? round(($totalScore / $maxScore) * 100) : 0;

        $attempt->update([
            'selesai_waktu' => now(),
            'selesai' => true,
            'nilai' => $finalScore,
        ]);

        return redirect()->route('quiz.attempts.show', [$quiz, $attempt])->with('success', 'Kuis berhasil diselesaikan.');
    }
}
