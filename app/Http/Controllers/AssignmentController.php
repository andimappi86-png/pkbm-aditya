<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('assignments.view');

        $user = $request->user();
        $roles = $user->getRoleNames();

        $query = Assignment::with(['penugasanMengajar.tutor.user', 'penugasanMengajar.subject', 'penugasanMengajar.classroom'])
            ->where('is_active', true);

        // Filters
        if ($request->filled('penugasan_mengajar_id')) {
            $query->where('penugasan_mengajar_id', $request->input('penugasan_mengajar_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where('judul', 'like', "%{$keyword}%");
        }

        // Role-based filtering
        if ($roles->contains('peserta-didik')) {
            $student = $user->student;
            abort_if(! $student, 403);
            $penugasanIds = $student->classroom ? $student->classroom->penugasanMengajar()->where('status_aktif', true)->pluck('id') : [];
            $query->whereIn('penugasan_mengajar_id', $penugasanIds);
        } elseif ($roles->contains('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            abort_if(! $tutor, 403);
            $query->whereHas('penugasanMengajar', fn ($q) => $q->where('tutor_id', $tutor->id));
        } elseif ($roles->contains('wali-kelas')) {
            $classroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
            $query->whereHas('penugasanMengajar', fn ($q) => $q->whereIn('classroom_id', $classroomIds));
        }

        $assignments = $query->latest()->paginate(15)->withQueryString();

        // For filter dropdown
        $penugasans = $this->getPenugasansForUser($user, $roles);

        return view('assignments.index', compact('assignments', 'penugasans'));
    }

    public function create(Request $request)
    {
        Gate::authorize('assignments.manage');

        $user = $request->user();
        $roles = $user->getRoleNames();

        $penugasans = $this->getPenugasansForUser($user, $roles);

        return view('assignments.create', compact('penugasans'));
    }

    public function store(StoreAssignmentRequest $request)
    {
        Gate::authorize('assignments.manage');

        $validated = $request->validated();
        $user = $request->user();

        $penugasan = PenugasanMengajar::findOrFail($validated['penugasan_mengajar_id']);
        $this->authorizePenugasan($penugasan, $user);

        if ($request->hasFile('file_materi')) {
            $path = $request->file('file_materi')->store('assignments', 'public');
            $validated['file_materi'] = $path;
        }

        Assignment::create($validated);

        return redirect()->route('tugas.index')->with('success', 'Tugas/Quiz berhasil dibuat.');
    }

    public function show(Assignment $assignment)
    {
        Gate::authorize('assignments.view');
        $this->authorizeAssignment($assignment, auth()->user());

        // Load submissions if tutor/admin
        $submissions = collect();
        if (auth()->user()->hasAnyRole(['kepala-pkbm', 'tutor'])) {
            $submissions = $assignment->submissions()->with('student')->get();
        } elseif (auth()->user()->hasRole('peserta-didik')) {
            $submissions = $assignment->submissions()->where('student_id', auth()->user()->student->id)->get();
        }

        return view('assignments.show', compact('assignment', 'submissions'));
    }

    public function edit(Assignment $assignment)
    {
        Gate::authorize('assignments.manage');
        $this->authorizeAssignment($assignment, auth()->user());

        $penugasans = $this->getPenugasansForUser(auth()->user(), auth()->user()->getRoleNames());

        return view('assignments.edit', compact('assignment', 'penugasans'));
    }

    public function update(UpdateAssignmentRequest $request, Assignment $assignment)
    {
        Gate::authorize('assignments.manage');
        $this->authorizeAssignment($assignment, $request->user());

        $validated = $request->validated();

        if ($request->hasFile('file_materi')) {
            if ($assignment->file_materi) {
                Storage::disk('public')->delete($assignment->file_materi);
            }
            $path = $request->file('file_materi')->store('assignments', 'public');
            $validated['file_materi'] = $path;
        }

        $assignment->update($validated);

        return redirect()->route('tugas.index')->with('success', 'Tugas/Quiz berhasil diperbarui.');
    }

    public function destroy(Assignment $assignment)
    {
        Gate::authorize('assignments.manage');
        $this->authorizeAssignment($assignment, auth()->user());

        if ($assignment->file_materi) {
            Storage::disk('public')->delete($assignment->file_materi);
        }

        $assignment->delete();

        return redirect()->route('tugas.index')->with('success', 'Tugas/Quiz berhasil dihapus.');
    }

    private function getPenugasansForUser($user, $roles)
    {
        $query = PenugasanMengajar::with(['tutor', 'subject', 'classroom'])->where('status_aktif', true);

        if ($roles->contains('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            if ($tutor) {
                $query->where('tutor_id', $tutor->id);
            }
        }

        return $query->get();
    }

    private function authorizePenugasan(PenugasanMengajar $penugasan, $user): void
    {
        if ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            abort_if(! $tutor || $penugasan->tutor_id !== $tutor->id, 403);
        }
    }

    private function authorizeAssignment(Assignment $assignment, $user): void
    {
        if ($user->hasRole('peserta-didik')) {
            $student = $user->student;
            abort_if(! $student || ! $student->classroom || $assignment->penugasanMengajar->classroom_id !== $student->classroom->id, 403);
        } elseif ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            abort_if(! $tutor || $assignment->penugasanMengajar->tutor_id !== $tutor->id, 403);
        } elseif ($user->hasRole('wali-kelas')) {
            $wali = Tutor::where('user_id', $user->id)->first();
            $classroomIds = Classroom::where('wali_kelas_id', $wali?->id)->pluck('id');
            abort_if(! $classroomIds->contains($assignment->penugasanMengajar->classroom_id), 403);
        }
    }
}
