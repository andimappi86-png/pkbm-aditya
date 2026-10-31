<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizGradeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class QuizEssayGradeController extends Controller
{
    public function index(Quiz $quiz)
    {
        Gate::authorize('gradeEssay', $quiz);

        $quiz->load(['penugasanMengajar.subject', 'penugasanMengajar.classroom']);

        $attempts = QuizAttempt::with(['student.user', 'answers.question'])
            ->where('quiz_id', $quiz->id)
            ->where('selesai', true)
            ->whereHas('answers.question', fn ($q) => $q->where('tipe', 'esai'))
            ->orderBy('mulai_waktu')
            ->paginate(20);

        return view('quizzes.essay-grade.index', compact('quiz', 'attempts'));
    }

    public function show(Quiz $quiz, QuizAttempt $attempt)
    {
        Gate::authorize('gradeEssay', $quiz);

        abort_if($attempt->quiz_id !== $quiz->id, 404);
        abort_if(! $attempt->selesai, 404);

        $quiz->load('questions');
        $attempt->load(['answers.question', 'student.user']);

        $essayAnswers = $attempt->answers->where('question.tipe', 'esai')->values();

        return view('quizzes.essay-grade.show', compact('quiz', 'attempt', 'essayAnswers'));
    }

    public function grade(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        Gate::authorize('gradeEssay', $quiz);

        abort_if($attempt->quiz_id !== $quiz->id, 404);
        abort_if(! $attempt->selesai, 404);

        $validated = $request->validate([
            'grades' => ['required', 'array'],
            'grades.*.question_id' => ['required', 'exists:questions,id'],
            'grades.*.nilai' => ['required', 'numeric', 'min:0'],
            'grades.*.catatan' => ['nullable', 'string'],
        ]);

        $quiz->load('questions');
        $attempt->load('answers');

        $finalScore = DB::transaction(function () use ($validated, $quiz, $attempt) {
            $totalScore = 0;
            $maxScore = 0;

            foreach ($quiz->questions as $question) {
                $maxScore += $question->bobot;

                $answer = $attempt->answers->where('question_id', $question->id)->first();

                if (! $answer) {
                    continue;
                }

                if ($question->tipe === 'pilihan_ganda') {
                    // Already graded in submit
                    $totalScore += $answer->benar ? $question->bobot : 0;
                } elseif ($question->tipe === 'esai') {
                    $gradeData = collect($validated['grades'])
                        ->firstWhere('question_id', $question->id);

                    if ($gradeData) {
                        $nilai = min($gradeData['nilai'], $question->bobot);

                        $answer->update([
                            'nilai' => $nilai,
                            'catatan' => $gradeData['catatan'] ?? null,
                            'dinilai_oleh' => auth()->id(),
                            'dinilai_pada' => now(),
                        ]);

                        // Log the grade
                        QuizGradeLog::create([
                            'quiz_attempt_id' => $attempt->id,
                            'question_id' => $question->id,
                            'tutor_id' => auth()->id(),
                            'nilai_lama' => $answer->getOriginal('nilai'),
                            'nilai_baru' => $nilai,
                            'catatan' => $gradeData['catatan'] ?? 'Penilaian esai',
                        ]);

                        $totalScore += $nilai;
                    }
                }
            }

            $calculatedScore = $maxScore > 0 ? round(($totalScore / $maxScore) * 100) : 0;

            $attempt->update([
                'nilai' => $calculatedScore,
            ]);

            return $calculatedScore;
        });

        return back()->with('success', 'Penilaian esai berhasil disimpan. Nilai akhir: '.$finalScore);
    }

    public function completeGrading(Quiz $quiz, QuizAttempt $attempt)
    {
        Gate::authorize('gradeEssay', $quiz);

        abort_if($attempt->quiz_id !== $quiz->id, 404);
        abort_if(! $attempt->selesai, 404);

        // Check if all essay questions are graded
        $ungradedEssays = $attempt->answers()
            ->whereHas('question', fn ($q) => $q->where('tipe', 'esai'))
            ->whereNull('nilai')
            ->count();

        if ($ungradedEssays > 0) {
            return back()->with('error', "Masih ada {$ungradedEssays} soal esai yang belum dinilai.");
        }

        return redirect()->route('quiz.recap', $quiz)->with('success', 'Semua soal esai sudah dinilai. Nilai akhir sudah final.');
    }
}
