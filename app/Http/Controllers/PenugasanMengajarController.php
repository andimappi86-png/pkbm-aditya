<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenugasanMengajarRequest;
use App\Http\Requests\UpdatePenugasanMengajarRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Tutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PenugasanMengajarController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('classrooms.manage');

        $query = PenugasanMengajar::with(['tutor', 'subject', 'classroom', 'academicYear', 'semester']);

        // Filter by tutor
        if ($request->filled('tutor_id')) {
            $query->where('tutor_id', $request->tutor_id);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by classroom
        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        // Filter by academic year
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by semester
        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        // Filter by status aktif
        if ($request->filled('status_aktif')) {
            $status = $request->status_aktif === 'true' || $request->status_aktif === '1' ? true : false;
            $query->where('status_aktif', $status);
        }

        $penugasanans = $query->latest()->paginate(10)->withQueryString();

        $tutors = Tutor::latest()->get();
        $subjects = Subject::where('status_aktif', true)->latest()->get();
        $classrooms = Classroom::with(['program', 'academicYear'])->latest()->get();
        $academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->get();
        $semesters = Semester::where('status_aktif', true)->latest('tanggal_mulai')->get();

        return view('penugasan-mengajar.index', compact(
            'penugasanans',
            'tutors',
            'subjects',
            'classrooms',
            'academicYears',
            'semesters'
        ));
    }

    public function create(): View
    {
        Gate::authorize('classrooms.manage');

        $tutors = Tutor::with('user')->latest()->get();
        $subjects = Subject::where('status_aktif', true)->latest()->get();
        $classrooms = Classroom::with(['program', 'academicYear'])->latest()->get();
        $academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->get();
        $semesters = Semester::where('status_aktif', true)->latest('tanggal_mulai')->get();

        return view('penugasan-mengajar.create', compact(
            'tutors',
            'subjects',
            'classrooms',
            'academicYears',
            'semesters'
        ));
    }

    public function store(StorePenugasanMengajarRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        PenugasanMengajar::create($validated);

        return redirect()->route('penugasan-mengajar.index')
            ->with('success', 'Penugasan mengajar berhasil ditambahkan.');
    }

    public function show(PenugasanMengajar $penugasanMengajar): View
    {
        Gate::authorize('classrooms.manage', $penugasanMengajar);

        return view('penugasan-mengajar.show', compact('penugasanMengajar'));
    }

    public function edit(PenugasanMengajar $penugasanMengajar): View
    {
        Gate::authorize('classrooms.manage', $penugasanMengajar);

        $tutors = Tutor::with('user')->latest()->get();
        $subjects = Subject::where('status_aktif', true)->latest()->get();
        $classrooms = Classroom::with(['program', 'academicYear'])->latest()->get();
        $academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->get();
        $semesters = Semester::where('status_aktif', true)->latest('tanggal_mulai')->get();

        return view('penugasan-mengajar.edit', compact(
            'penugasanMengajar',
            'tutors',
            'subjects',
            'classrooms',
            'academicYears',
            'semesters'
        ));
    }

    public function update(UpdatePenugasanMengajarRequest $request, PenugasanMengajar $penugasanMengajar): RedirectResponse
    {
        Gate::authorize('classrooms.manage', $penugasanMengajar);

        $validated = $request->validated();

        $penugasanMengajar->update($validated);

        return redirect()->route('penugasan-mengajar.index')
            ->with('success', 'Penugasan mengajar berhasil diperbarui.');
    }

    public function destroy(PenugasanMengajar $penugasanMengajar): RedirectResponse
    {
        Gate::authorize('classrooms.manage', $penugasanMengajar);

        $penugasanMengajar->delete();

        return redirect()->route('penugasan-mengajar.index')
            ->with('success', 'Penugasan mengajar berhasil dihapus.');
    }
}
