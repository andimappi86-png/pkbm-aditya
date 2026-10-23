<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectClassroomRequest;
use App\Http\Requests\StoreTutorClassroomRequest;
use App\Http\Requests\StoreTutorSubjectRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Tutor;
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

        $pms = PenugasanMengajar::with(['tutor', 'subject', 'classroom'])
            ->latest()
            ->get();

        $seenKeys = [];
        $seenClassrooms = [];
        $assignments = collect();

        foreach ($pms as $pm) {
            $key = $pm->tutor_id.'_'.$pm->subject_id;
            $seenKeys[$key] = true;
            if ($pm->classroom_id) {
                $seenClassrooms[$pm->tutor_id.'_'.$pm->classroom_id] = true;
            }
            $assignments->push((object) [
                'id' => $pm->id,
                'tutor_id' => $pm->tutor_id,
                'tutor_nama' => optional($pm->tutor)->nama_lengkap ?? '-',
                'subject_nama' => optional($pm->subject)->nama ?? '-',
                'classroom_nama' => optional($pm->classroom)->nama_rombel ?? 'Belum dialokasikan ke rombel',
            ]);
        }

        $tutors = Tutor::with(['subjects', 'classrooms'])
            ->where(function ($q) {
                $q->has('subjects')->orHas('classrooms');
            })
            ->latest()
            ->get();

        foreach ($tutors as $tutor) {
            foreach ($tutor->subjects as $subject) {
                $key = $tutor->id.'_'.$subject->id;
                if (! isset($seenKeys[$key])) {
                    $assignments->push((object) [
                        'id' => 'ts_'.$tutor->id.'_'.$subject->id,
                        'tutor_id' => $tutor->id,
                        'tutor_nama' => $tutor->nama_lengkap,
                        'subject_nama' => $subject->nama,
                        'classroom_nama' => 'Belum dialokasikan ke rombel',
                    ]);
                }
            }
            // Add entries for tutor-classroom allocations without subject
            foreach ($tutor->classrooms as $classroom) {
                $key = $tutor->id.'_'.$classroom->id;
                if (! isset($seenClassrooms[$key])) {
                    $seenClassrooms[$key] = true;
                    $assignments->push((object) [
                        'id' => 'tc_'.$tutor->id.'_'.$classroom->id,
                        'tutor_id' => $tutor->id,
                        'tutor_nama' => $tutor->nama_lengkap,
                        'subject_nama' => '-',
                        'classroom_nama' => $classroom->nama_rombel ?? '-',
                    ]);
                }
            }
        }

        $perPage = 10;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $paginatedAssignments = new LengthAwarePaginator(
            $assignments->forPage($page, $perPage)->values(),
            $assignments->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('classroom-assignments.index', [
            'assignments' => $paginatedAssignments,
        ]);
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

        // Create PenugasanMengajar for subjects that are already in a classroom with this tutor
        foreach ($validated['subjects'] as $subjectId) {
            $classrooms = $tutor->classrooms()->whereHas('subjects', function ($q) use ($subjectId) {
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

        DB::transaction(function () use ($validated) {
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

            // Create PenugasanMengajar if valid combinations exist
            $subjects = $tutor->subjects()->whereHas('classrooms', function ($q) use ($classroom) {
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

        DB::transaction(function () use ($subjectIds, $classroom) {
            foreach ($subjectIds as $subjectId) {
                $subject = Subject::findOrFail($subjectId);
                $subject->classrooms()->syncWithoutDetaching([$classroom->id]);

                // Create PenugasanMengajar for tutors that teach this subject in this classroom
                $tutors = $subject->tutors()->whereHas('classrooms', function ($q) use ($classroom) {
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
