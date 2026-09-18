<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectClassroomRequest;
use App\Http\Requests\StoreTutorClassroomRequest;
use App\Http\Requests\StoreTutorSubjectRequest;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Tutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ClassroomAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('classrooms.manage');

        $tutors = Tutor::with(['user', 'subjects', 'classrooms'])->latest()->paginate(10);
        $subjects = Subject::with(['tutors', 'classrooms'])->latest()->paginate(10);
        $classrooms = Classroom::with(['program', 'academicYear', 'homeroomTeacher', 'tutors', 'subjects'])->latest()->paginate(10);

        // Filter
        if ($request->filled('tutor_id')) {
            $tutors->filter(function ($item) use ($request) {
                return $item->id == $request->tutor_id;
            });
        }
        if ($request->filled('subject_id')) {
            $subjects->filter(function ($item) use ($request) {
                return $item->id == $request->subject_id;
            });
        }

        return view('classroom-assignments.index', compact('tutors', 'subjects', 'classrooms'));
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

        $tutor = Tutor::firstOrCreate(['user_id' => $validated['tutor_id']]);
        $subject = Subject::firstOrCreate(['kode' => $validated['subject_kode'], 'nama' => $validated['subject_nama']]);

        $tutor->subjects()->syncWithoutDetaching([$subject->id]);

        return redirect()->route('classroom-assignments.index')
            ->with('success', 'Tutor '.$tutor->nama_lengkap.' berhasil dialokasikan ke mata pelajaran '.$subject->nama.'.');
    }

    public function createTutorClassroom(): View
    {
        Gate::authorize('classrooms.manage');

        $tutors = Tutor::with('user')->latest()->get();
        $classrooms = Classroom::with(['program', 'academicYear'])->latest()->get();

        return view('classroom-assignments.create-tutor-classroom', compact('tutors', 'classrooms'));
    }

    public function storeTutorClassroom(StoreTutorClassroomRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $tutor = Tutor::firstOrCreate(['user_id' => $validated['tutor_id']]);
        $classroom = Classroom::firstOrCreate([
            'kode_rombel' => $validated['classroom_kode'],
            'nama_rombel' => $validated['classroom_nama'],
            'program_id' => $validated['program_id'],
            'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
        ]);

        $tutor->classrooms()->syncWithoutDetaching([$classroom->id]);

        return redirect()->route('classroom-assignments.index')
            ->with('success', 'Tutor '.$tutor->nama_lengkap.' berhasil dialokasikan ke rombel '.$classroom->nama_rombel.'.');
    }

    public function createSubjectClassroom(): View
    {
        Gate::authorize('classrooms.manage');

        $subjects = Subject::latest()->get();
        $classrooms = Classroom::with(['program', 'academicYear'])->latest()->get();

        return view('classroom-assignments.create-subject-classroom', compact('subjects', 'classrooms'));
    }

    public function storeSubjectClassroom(StoreSubjectClassroomRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $subject = Subject::firstOrCreate(['kode' => $validated['subject_kode'], 'nama' => $validated['subject_nama']]);
        $classroom = Classroom::firstOrCreate([
            'kode_rombel' => $validated['classroom_kode'],
            'nama_rombel' => $validated['classroom_nama'],
            'program_id' => $validated['program_id'],
            'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
        ]);

        $subject->classrooms()->syncWithoutDetaching([$classroom->id]);

        return redirect()->route('classroom-assignments.index')
            ->with('success', 'Mata pelajaran '.$subject->nama.' berhasil dialokasikan ke rombel '.$classroom->nama_rombel.'.');
    }

    public function showAssignments(Tutor $tutor): View
    {
        Gate::authorize('classrooms.manage');

        $tutor->load(['user', 'subjects.classrooms', 'classrooms.subjects']);

        return view('classroom-assignments.tutor-assignments', compact('tutor'));
    }
}
