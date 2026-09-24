<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QuestionController extends Controller
{
    public function create(Quiz $quiz)
    {
        Gate::authorize('materials.manage');
        $this->authorizeQuiz($quiz);

        return view('questions.create', compact('quiz'));
    }

    public function store(Request $request, Quiz $quiz)
    {
        Gate::authorize('materials.manage');
        $this->authorizeQuiz($quiz);

        $validated = $request->validate([
            'pertanyaan' => 'required|string',
            'tipe' => 'required|in:pilihan_ganda,esai',
            'bobot' => 'required|integer|min:1',
            'options' => 'required_if:tipe,pilihan_ganda|array|min:2',
            'options.*.teks' => 'required|string',
            'options.*.is_benar' => 'boolean',
        ]);

        $question = $quiz->questions()->create([
            'pertanyaan' => $validated['pertanyaan'],
            'tipe' => $validated['tipe'],
            'bobot' => $validated['bobot'],
        ]);

        if ($validated['tipe'] === 'pilihan_ganda') {
            foreach ($validated['options'] as $opt) {
                $question->options()->create([
                    'teks' => $opt['teks'],
                    'is_benar' => $opt['is_benar'] ?? false,
                ]);
            }
        }

        return redirect()->route('quiz.show', $quiz)->with('success', 'Soal ditambahkan.');
    }

    public function edit(Question $question)
    {
        Gate::authorize('materials.manage');
        $this->authorizeQuiz($question->quiz);
        $question->load('options');

        return view('questions.edit', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        Gate::authorize('materials.manage');
        $this->authorizeQuiz($question->quiz);

        $validated = $request->validate([
            'pertanyaan' => 'required|string',
            'tipe' => 'required|in:pilihan_ganda,esai',
            'bobot' => 'required|integer|min:1',
            'options' => 'required_if:tipe,pilihan_ganda|array|min:2',
            'options.*.teks' => 'required|string',
            'options.*.is_benar' => 'boolean',
        ]);

        $question->update($validated);

        if ($validated['tipe'] === 'pilihan_ganda') {
            $question->options()->delete();
            foreach ($validated['options'] as $opt) {
                $question->options()->create([
                    'teks' => $opt['teks'],
                    'is_benar' => $opt['is_benar'] ?? false,
                ]);
            }
        }

        return redirect()->route('quiz.show', $question->quiz)->with('success', 'Soal diperbarui.');
    }

    public function destroy(Question $question)
    {
        Gate::authorize('materials.manage');
        $quiz = $question->quiz;
        $this->authorizeQuiz($quiz);
        $question->delete();

        return redirect()->route('quiz.show', $quiz)->with('success', 'Soal dihapus.');
    }

    private function authorizeQuiz(Quiz $quiz)
    {
        $penugasan = $quiz->penugasanMengajar;
        $user = auth()->user();

        if ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            abort_if($penugasan->tutor_id !== $tutor?->id, 403);
        }
    }
}
