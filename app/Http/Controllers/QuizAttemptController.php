<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QuizAttemptController extends Controller
{
    public function start(Request $request, Quiz $quiz)
    {
        Gate::authorize('materials.view');

        $user = $request->user();
        abort_if(! $user->hasRole('peserta-didik'), 403);

        $student = $user->student;
        abort_if(! $student, 403);
        abort_if($quiz->penugasanMengajar->classroom_id !== $student->rombel_id, 403);
        abort_if(! $quiz->status_aktif, 403);

        // Cek apakah sudah pernah attempt
        $existing = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            return redirect()->route('quiz.attempts.show', [$quiz, $existing]);
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'mulai_waktu' => now(),
            'selesai' => false,
        ]);

        return redirect()->route('quiz.attempts.show', [$quiz, $attempt]);
    }

    public function show(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        Gate::authorize('materials.view');

        $user = $request->user();
        abort_if($attempt->student->user_id !== $user->id, 403);

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
        Gate::authorize('materials.view');

        $user = $request->user();
        abort_if($attempt->student->user_id !== $user->id, 403);
        abort_if($attempt->selesai, 403);

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
        Gate::authorize('materials.view');

        $user = $request->user();
        abort_if($attempt->student->user_id !== $user->id, 403);
        abort_if($attempt->selesai, 403);

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
