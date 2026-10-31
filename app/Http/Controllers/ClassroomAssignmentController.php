<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignWaliKelasRequest;
use App\Http\Requests\StoreSubjectClassroomRequest;
use App\Http\Requests\StoreTeachingAssignmentRequest;
use App\Http\Requests\StoreTutorClassroomRequest;
use App\Http\Requests\StoreTutorSubjectRequest;
use App\Http\Requests\UpdateTeachingAssignmentRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ClassroomAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('classrooms.manage');

        $activeTab = $request->query('tab', 'wali-kelas');

        $academicYears = AcademicYear::orderByDesc('tanggal_mulai')->get();
        $activeYear = $academicYears->firstWhere('status_aktif', true) ?? $academicYears->first();
        $selectedYearId = $request->filled('academic_year_id')
            ? (int) $request->query('academic_year_id')
            : (int) ($activeYear ? $activeYear->id : 0);

        // 1. Wali Kelas Query
        $waliQuery = Classroom::with(['program', 'academicYear', 'waliKelas'])
            ->when($selectedYearId > 0, fn ($q) => $q->where('tahun_ajaran_id', $selectedYearId));

        if ($request->filled('wali_search')) {
            $search = trim((string) $request->query('wali_search'));
            $waliQuery->where(function ($q) use ($search): void {
                $q->where('nama_rombel', 'like', "%{$search}%")
                    ->orWhere('kode_rombel', 'like', "%{$search}%")
                    ->orWhereHas('waliKelas', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $waliClassrooms = $waliQuery->orderBy('nama_rombel')->paginate(10, ['*'], 'wali_page')->withQueryString();

        $eligibleWaliKelas = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['wali-kelas', 'wali_kelas', 'wali kelas']))
            ->orderBy('name')
            ->get();

        // 2. Teaching Assignments Query
        $teachingQuery = PenugasanMengajar::with(['tutor.user', 'subject', 'classroom.program', 'academicYear'])
            ->when($selectedYearId > 0, fn ($q) => $q->where('academic_year_id', $selectedYearId));

        if ($request->filled('teaching_search')) {
            $search = trim((string) $request->query('teaching_search'));
            $teachingQuery->where(function ($q) use ($search): void {
                $q->whereHas('tutor', fn ($tq) => $tq->where('nama_lengkap', 'like', "%{$search}%"))
                    ->orWhereHas('subject', fn ($sq) => $sq->where('nama', 'like', "%{$search}%")->orWhere('kode', 'like', "%{$search}%"))
                    ->orWhereHas('classroom', fn ($cq) => $cq->where('nama_rombel', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status_filter')) {
            $status = in_array($request->query('status_filter'), ['1', 'true', 'active'], true);
            $teachingQuery->where('status_aktif', $status);
        }

        $teachingAssignments = $teachingQuery->latest()->paginate(10, ['*'], 'teaching_page')->withQueryString();

        $tutors = Tutor::with('user')->orderBy('nama_lengkap')->get();
        $subjects = Subject::where('status_aktif', true)->orderBy('nama')->get();
        $classroomsForSelect = Classroom::with(['program', 'academicYear'])
            ->when($selectedYearId > 0, fn ($q) => $q->where('tahun_ajaran_id', $selectedYearId))
            ->where('status_aktif', true)
            ->orderBy('nama_rombel')
            ->get();

        // Legacy compatibility: aggregate assignments for tests/legacy views
        $pms = PenugasanMengajar::with(['tutor', 'subject', 'classroom'])->latest()->get();
        $seenKeys = [];
        $seenClassrooms = [];
        $legacyAssignments = collect();

        foreach ($pms as $pm) {
            $key = $pm->tutor_id.'_'.$pm->subject_id;
            $seenKeys[$key] = true;
            if ($pm->classroom_id) {
                $seenClassrooms[$pm->tutor_id.'_'.$pm->classroom_id] = true;
            }
            $legacyAssignments->push((object) [
                'id' => $pm->id,
                'tutor_id' => $pm->tutor_id,
                'tutor_nama' => optional($pm->tutor)->nama_lengkap ?? '-',
                'subject_nama' => optional($pm->subject)->nama ?? '-',
                'classroom_nama' => optional($pm->classroom)->nama_rombel ?? 'Belum dialokasikan ke rombel',
            ]);
        }

        $legacyTutors = Tutor::with(['subjects', 'classrooms'])
            ->where(function ($q): void {
                $q->has('subjects')->orHas('classrooms');
            })
            ->latest()
            ->get();

        foreach ($legacyTutors as $tutor) {
            foreach ($tutor->subjects as $subject) {
                $key = $tutor->id.'_'.$subject->id;
                if (! isset($seenKeys[$key])) {
                    $legacyAssignments->push((object) [
                        'id' => 'ts_'.$tutor->id.'_'.$subject->id,
                        'tutor_id' => $tutor->id,
                        'tutor_nama' => $tutor->nama_lengkap,
                        'subject_nama' => $subject->nama,
                        'classroom_nama' => 'Belum dialokasikan ke rombel',
                    ]);
                }
            }
            foreach ($tutor->classrooms as $classroom) {
                $key = $tutor->id.'_'.$classroom->id;
                if (! isset($seenClassrooms[$key])) {
                    $seenClassrooms[$key] = true;
                    $legacyAssignments->push((object) [
                        'id' => 'tc_'.$tutor->id.'_'.$classroom->id,
                        'tutor_id' => $tutor->id,
                        'tutor_nama' => $tutor->nama_lengkap,
                        'subject_nama' => '-',
                        'classroom_nama' => $classroom->nama_rombel ?? '-',
                    ]);
                }
            }
        }

        $page = Paginator::resolveCurrentPage('page') ?: 1;
        $assignments = new LengthAwarePaginator(
            $legacyAssignments->forPage($page, 10)->values(),
            $legacyAssignments->count(),
            10,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('classroom-assignments.index', compact(
            'activeTab',
            'academicYears',
            'selectedYearId',
            'waliClassrooms',
            'eligibleWaliKelas',
            'teachingAssignments',
            'tutors',
            'subjects',
            'classroomsForSelect',
            'assignments'
        ));
    }

    public function assignWaliKelas(AssignWaliKelasRequest $request): RedirectResponse
    {
        $classroom = Classroom::findOrFail($request->integer('classroom_id'));
        $waliKelas = User::findOrFail($request->integer('wali_kelas_id'));

        $classroom->update([
            'wali_kelas_id' => $waliKelas->id,
        ]);

        return redirect()->route('classroom-assignments.index', [
            'tab' => 'wali-kelas',
            'academic_year_id' => $classroom->tahun_ajaran_id,
        ])->with('success', "Wali kelas untuk rombel {$classroom->nama_rombel} berhasil ditetapkan kepada {$waliKelas->name}.");
    }

    public function removeWaliKelas(Classroom $classroom): RedirectResponse
    {
        Gate::authorize('classrooms.manage');

        $yearId = $classroom->tahun_ajaran_id;
        $classroom->update(['wali_kelas_id' => null]);

        return redirect()->route('classroom-assignments.index', [
            'tab' => 'wali-kelas',
            'academic_year_id' => $yearId,
        ])->with('success', "Penetapan wali kelas untuk rombel {$classroom->nama_rombel} berhasil dihapus/dikosongkan.");
    }

    public function storeTeachingAssignment(StoreTeachingAssignmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $statusAktif = $request->boolean('status_aktif', true);

        PenugasanMengajar::create([
            'academic_year_id' => $validated['academic_year_id'],
            'tutor_id' => $validated['tutor_id'],
            'subject_id' => $validated['subject_id'],
            'classroom_id' => $validated['classroom_id'],
            'status_aktif' => $statusAktif,
        ]);

        // Maintain legacy pivot synchronization
        $tutor = Tutor::find($validated['tutor_id']);
        $subject = Subject::find($validated['subject_id']);
        $classroom = Classroom::find($validated['classroom_id']);

        if ($tutor && $subject && $classroom) {
            $tutor->subjects()->syncWithoutDetaching([$subject->id]);
            $tutor->classrooms()->syncWithoutDetaching([$classroom->id]);
            $subject->classrooms()->syncWithoutDetaching([$classroom->id]);
        }

        return redirect()->route('classroom-assignments.index', [
            'tab' => 'teaching',
            'academic_year_id' => $validated['academic_year_id'],
        ])->with('success', 'Penugasan mengajar tutor berhasil ditambahkan.');
    }

    public function updateTeachingAssignment(UpdateTeachingAssignmentRequest $request, PenugasanMengajar $penugasanMengajar): RedirectResponse
    {
        $validated = $request->validated();
        $statusAktif = $request->has('status_aktif') ? $request->boolean('status_aktif') : $penugasanMengajar->status_aktif;

        $penugasanMengajar->update([
            'academic_year_id' => $validated['academic_year_id'],
            'tutor_id' => $validated['tutor_id'],
            'subject_id' => $validated['subject_id'],
            'classroom_id' => $validated['classroom_id'],
            'status_aktif' => $statusAktif,
        ]);

        $tutor = Tutor::find($validated['tutor_id']);
        $subject = Subject::find($validated['subject_id']);
        $classroom = Classroom::find($validated['classroom_id']);

        if ($tutor && $subject && $classroom) {
            $tutor->subjects()->syncWithoutDetaching([$subject->id]);
            $tutor->classrooms()->syncWithoutDetaching([$classroom->id]);
            $subject->classrooms()->syncWithoutDetaching([$classroom->id]);
        }

        return redirect()->route('classroom-assignments.index', [
            'tab' => 'teaching',
            'academic_year_id' => $validated['academic_year_id'],
        ])->with('success', 'Penugasan mengajar berhasil diperbarui.');
    }

    public function toggleTeachingStatus(PenugasanMengajar $penugasanMengajar): RedirectResponse
    {
        Gate::authorize('classrooms.manage');

        $penugasanMengajar->update([
            'status_aktif' => ! $penugasanMengajar->status_aktif,
        ]);

        $statusText = $penugasanMengajar->status_aktif ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('classroom-assignments.index', [
            'tab' => 'teaching',
            'academic_year_id' => $penugasanMengajar->academic_year_id,
        ])->with('success', "Penugasan mengajar berhasil {$statusText}.");
    }

    public function destroyTeachingAssignment(PenugasanMengajar $penugasanMengajar): RedirectResponse
    {
        Gate::authorize('classrooms.manage');

        $yearId = $penugasanMengajar->academic_year_id;
        $penugasanMengajar->delete();

        return redirect()->route('classroom-assignments.index', [
            'tab' => 'teaching',
            'academic_year_id' => $yearId,
        ])->with('success', 'Penugasan mengajar berhasil dihapus.');
    }

    public function createTutorSubject(): View
    {
        Gate::authorize('classrooms.manage');

        $tutors = Tutor::with('user')->latest()->get();
        $subjects = Subject::latest()->get();

        return view('classroom-assignments.create-tutor-subject', compact('tutors', 'subjects'));
    }

    public function storeTutorSubject(StoreTutorSubjectRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $tutor = Tutor::findOrFail($validated['tutor_id']);

        $tutor->subjects()->syncWithoutDetaching($validated['subjects']);

        foreach ($validated['subjects'] as $subjectId) {
            $classrooms = $tutor->classrooms()->whereHas('subjects', function ($q) use ($subjectId): void {
                $q->where('subjects.id', $subjectId);
            })->get();

            foreach ($classrooms as $classroom) {
                PenugasanMengajar::firstOrCreate([
                    'tutor_id' => $tutor->id,
                    'subject_id' => $subjectId,
                    'classroom_id' => $classroom->id,
                ], [
                    'academic_year_id' => $classroom->tahun_ajaran_id,
                    'status_aktif' => true,
                ]);
            }
        }

        return redirect()->route('classroom-assignments.index')
            ->with('success', 'Tutor '.$tutor->nama_lengkap.' berhasil dialokasikan ke mata pelajaran.');
    }

    public function createTutorClassroom(): View
    {
        Gate::authorize('classrooms.manage');

        $tutors = Tutor::with('user')->latest()->get();
        $classrooms = Classroom::with(['program', 'academicYear'])->latest()->get();
        $programs = Program::where('status_aktif', true)->latest()->get();
        $academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->get();

        return view('classroom-assignments.create-tutor-classroom', compact('tutors', 'classrooms', 'programs', 'academicYears'));
    }

    public function storeTutorClassroom(StoreTutorClassroomRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $tutor = $validated['mode_tutor'] === 'select'
                ? Tutor::findOrFail($validated['tutor_id'])
                : Tutor::create(['nama_lengkap' => $validated['tutor_nama'], 'status_tutor' => 'aktif']);

            $classroom = $validated['mode_rombel'] === 'select'
                ? Classroom::findOrFail($validated['classroom_id'])
                : Classroom::create([
                    'kode_rombel' => $validated['classroom_kode'],
                    'nama_rombel' => $validated['classroom_nama'],
                    'program_id' => $validated['program_id'],
                    'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
                ]);

            $tutor->classrooms()->syncWithoutDetaching([$classroom->id]);

            $subjects = $tutor->subjects()->whereHas('classrooms', function ($q) use ($classroom): void {
                $q->where('classrooms.id', $classroom->id);
            })->get();

            foreach ($subjects as $subject) {
                PenugasanMengajar::firstOrCreate([
                    'tutor_id' => $tutor->id,
                    'subject_id' => $subject->id,
                    'classroom_id' => $classroom->id,
                ], [
                    'academic_year_id' => $classroom->tahun_ajaran_id,
                    'status_aktif' => true,
                ]);
            }
        });

        return redirect()->route('classroom-assignments.index')
            ->with('success', 'Alokasi tutor ke rombel berhasil disimpan.');
    }

    public function createSubjectClassroom(): View
    {
        Gate::authorize('classrooms.manage');

        $subjects = Subject::latest()->get();
        $classrooms = Classroom::with(['program', 'academicYear'])->latest()->get();
        $programs = Program::where('status_aktif', true)->latest()->get();
        $academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->get();

        return view('classroom-assignments.create-subject-classroom', compact('subjects', 'classrooms', 'programs', 'academicYears'));
    }

    public function storeSubjectClassroom(StoreSubjectClassroomRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $subjectIds = $validated['subjects'] ?? [];
        $classroom = Classroom::findOrFail($validated['classroom_id']);

        DB::transaction(function () use ($subjectIds, $classroom): void {
            foreach ($subjectIds as $subjectId) {
                $subject = Subject::findOrFail($subjectId);
                $subject->classrooms()->syncWithoutDetaching([$classroom->id]);

                $tutors = $subject->tutors()->whereHas('classrooms', function ($q) use ($classroom): void {
                    $q->where('classrooms.id', $classroom->id);
                })->get();

                foreach ($tutors as $tutor) {
                    PenugasanMengajar::firstOrCreate([
                        'tutor_id' => $tutor->id,
                        'subject_id' => $subject->id,
                        'classroom_id' => $classroom->id,
                    ], [
                        'academic_year_id' => $classroom->tahun_ajaran_id,
                        'status_aktif' => true,
                    ]);
                }
            }
        });

        return redirect()->route('classroom-assignments.index')
            ->with('success', count($subjectIds).' mata pelajaran berhasil dialokasikan ke rombel '.$classroom->nama_rombel.'.');
    }

    public function showAssignments(Tutor $tutor): View
    {
        Gate::authorize('classrooms.manage');

        $tutor->load(['user', 'subjects.classrooms', 'classrooms.subjects']);

        return view('classroom-assignments.tutor-assignments', compact('tutor'));
    }
}
