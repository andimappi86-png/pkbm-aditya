<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('classrooms.view');

        $query = Classroom::with(['program', 'academicYear', 'homeroomTeacher', 'students']);

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

        $programs = Program::where('status_aktif', true)->get();
        $academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->get();

        return view('classrooms.create', compact('programs', 'academicYears'));
    }

    public function store(StoreClassroomRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Classroom::create($validated);

        return redirect()->route('rombel.index')
            ->with('success', 'Rombel berhasil ditambahkan.');
    }

    public function show(Classroom $rombel): View
    {
        Gate::authorize('classrooms.view', $rombel);

        $rombel->load(['program', 'academicYear', 'homeroomTeacher', 'students.user']);

        return view('classrooms.show', compact('rombel'));
    }

    public function edit(Classroom $rombel): View
    {
        Gate::authorize('classrooms.update', $rombel);

        $programs = Program::where('status_aktif', true)->get();
        $academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->get();

        return view('classrooms.edit', compact('rombel', 'programs', 'academicYears'));
    }

    public function update(UpdateClassroomRequest $request, Classroom $rombel): RedirectResponse
    {
        Gate::authorize('classrooms.update', $rombel);

        $validated = $request->validated();

        $rombel->update($validated);

        return redirect()->route('rombel.index')
            ->with('success', 'Data rombel berhasil diperbarui.');
    }

    public function destroy(Classroom $rombel): RedirectResponse
    {
        Gate::authorize('classrooms.delete', $rombel);

        $rombel->delete();

        return redirect()->route('rombel.index')
            ->with('success', 'Rombel berhasil dihapus.');
    }
}
