<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('classrooms.view');

        $query = Classroom::with(['program', 'academicYear', 'waliKelas', 'students']);

        $user = auth()->user();

        // Scope access based on roles
        if ($user->hasRole('wali-kelas')) {
            $query->where('wali_kelas_id', $user->id);
        } elseif ($user->hasRole('tutor')) {
            $query->whereHas('penugasanMengajar', function ($q) use ($user) {
                if ($user->tutor) {
                    $q->where('tutor_id', $user->tutor->id);
                }
            });
        } elseif ($user->hasRole('peserta-didik')) {
            if ($user->student) {
                $query->where('id', $user->student->rombel_id);
            }
        }

        // Filter by program
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by tahun ajaran
        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        // Filter by status aktif
        if ($request->filled('status_aktif')) {
            $status = $request->status_aktif === 'true' || $request->status_aktif === '1' ? true : false;
            $query->where('status_aktif', $status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_rombel', 'like', "%{$search}%")
                    ->orWhere('kode_rombel', 'like', "%{$search}%");
            });
        }

        $classrooms = $query->latest()->paginate(10)->withQueryString();

        $programs = Program::where('status_aktif', true)->get();
        $academicYears = AcademicYear::where('status_aktif', true)->get();

        return view('classrooms.index', compact('classrooms', 'programs', 'academicYears'));
    }

    public function create(): View
    {
        Gate::authorize('classrooms.create');

        $programs = Program::where('status_aktif', true)->get()->unique('nama');
        $academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->get();
        $waliKelas = User::role('wali-kelas')->orderBy('name')->get();

        return view('classrooms.create', compact('programs', 'academicYears', 'waliKelas'));
    }

    public function store(StoreClassroomRequest $request): RedirectResponse
    {
        Gate::authorize('create', Classroom::class);

        $validated = $request->validated();

        Classroom::create($validated);

        return redirect()->route('rombel.index')
            ->with('success', 'Rombel berhasil ditambahkan.');
    }

    public function show(Classroom $classroom): View
    {
        Gate::authorize('view', $classroom);

        $classroom->load(['program', 'academicYear', 'waliKelas', 'students.user']);

        return view('classrooms.show', compact('classroom'));
    }

    public function edit(Classroom $classroom)
    {
        Gate::authorize('update', $classroom);

        $classroom->load('waliKelas');
        $programs = Program::where('status_aktif', true)->get()->unique('nama');
        $academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->get();
        $waliKelas = User::role('wali-kelas')->orderBy('name')->get();

        return view('classrooms.edit', compact('classroom', 'programs', 'academicYears', 'waliKelas'));
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroom): RedirectResponse
    {
        Gate::authorize('update', $classroom);

        $validated = $request->validated();

        $classroom->update($validated);

        return redirect()->route('rombel.index')
            ->with('success', 'Data rombel berhasil diperbarui.');
    }

    public function destroy(Classroom $classroom): RedirectResponse
    {
        Gate::authorize('delete', $classroom);

        $classroom->delete();

        return redirect()->route('rombel.index')
            ->with('success', 'Rombel berhasil dihapus.');
    }

    public function searchWaliKelas(Request $request): JsonResponse
    {
        Gate::authorize('classrooms.view');

        $q = trim((string) $request->query('q', ''));

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $waliKelasList = User::role('wali-kelas')
            ->where('name', 'like', "%{$q}%")
            ->select(['id', 'name', 'email'])
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($waliKelasList);
    }
}
