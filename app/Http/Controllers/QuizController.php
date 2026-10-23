<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Quiz;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('quizzes.view');

        $user = $request->user();
        $query = Quiz::with(['penugasanMengajar.subject', 'penugasanMengajar.classroom'])
            ->where('status_aktif', true);

        if ($request->filled('penugasan_mengajar_id')) {
            $query->where('penugasan_mengajar_id', $request->penugasan_mengajar_id);
        }

        $this->filterAccess($query, $user);

        $quizzes = $query->latest()->paginate(15);
        $penugasans = $this->getMyPenugasans($user);

        return view('quizzes.index', compact('quizzes', 'penugasans'));
    }

    public function create(Request $request)
    {
        Gate::authorize('create', Quiz::class);
        $penugasans = $this->getMyPenugasans($request->user());

        return view('quizzes.create', compact('penugasans'));
    }

    public function store(StoreQuizRequest $request)
    {
        Gate::authorize('create', Quiz::class);
        $validated = $request->validated();

        $penugasan = PenugasanMengajar::findOrFail($validated['penugasan_mengajar_id']);
        $this->authorizePenugasan($penugasan, $request->user());

        Quiz::create($validated);

        return redirect()->route('quiz.index')->with('success', 'Quiz berhasil dibuat.');
    }

    public function show(Quiz $quiz)
    {
        Gate::authorize('view', $quiz);

        $quiz->load('questions.options');

        return view('quizzes.show', compact('quiz'));
    }

    public function edit(Quiz $quiz)
    {
        Gate::authorize('update', $quiz);
        $penugasans = $this->getMyPenugasans(auth()->user());

        return view('quizzes.edit', compact('quiz', 'penugasans'));
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        Gate::authorize('update', $quiz);
        $quiz->update($request->validated());

        return redirect()->route('quiz.show', $quiz)->with('success', 'Quiz diperbarui.');
    }

    public function destroy(Quiz $quiz)
    {
        Gate::authorize('delete', $quiz);
        $quiz->delete();

        return redirect()->route('quiz.index')->with('success', 'Quiz dihapus.');
    }

    private function getMyPenugasans($user)
    {
        $query = PenugasanMengajar::with(['subject', 'classroom']);
        if ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            $query->where('tutor_id', $tutor?->id);
        }

        return $query->where('status_aktif', true)->get();
    }

    private function filterAccess($query, $user)
    {
        if ($user->hasRole('peserta-didik')) {
            $student = $user->student;
            $query->whereHas('penugasanMengajar', fn ($q) => $q->where('classroom_id', $student?->rombel_id));
        } elseif ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            $query->whereHas('penugasanMengajar', fn ($q) => $q->where('tutor_id', $tutor?->id));
        } elseif ($user->hasRole('wali-kelas')) {
            $classroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
            $query->whereHas('penugasanMengajar', fn ($q) => $q->whereIn('classroom_id', $classroomIds));
        }
    }

    private function authorizePenugasan($penugasan, $user)
    {
        if ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            abort_if($penugasan->tutor_id !== $tutor?->id, 403);
        }
    }
}
