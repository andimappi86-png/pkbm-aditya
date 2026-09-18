<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\PenugasanMengajar;
use App\Models\Program;
use App\Models\Student;
use App\Models\Submission;
use App\Models\Tutor;
use Illuminate\Http\Request;

class RekapAkademikController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_if(! $user->can('reports.view') && ! $user->hasAnyRole(['tutor', 'peserta-didik']), 403);

        $roles = $user->getRoleNames();

        if ($roles->contains('peserta-didik')) {
            $student = $user->student;
            abort_if(! $student, 403);

            return $this->showStudentDetail($student);
        }

        $query = Student::with(['classroom', 'program', 'academicYear']);

        $filters = [
            'academic_year_id' => $request->input('academic_year_id'),
            'program_id' => $request->input('program_id'),
            'classroom_id' => $request->input('classroom_id'),
            'keyword' => $request->input('keyword'),
        ];

        // Role-based restrictions
        if ($roles->contains('wali-kelas')) {
            $wali = Tutor::where('user_id', $user->id)->first();
            $classroomIds = Classroom::where('wali_kelas_id', $wali?->id)->pluck('id');
            $query->whereIn('rombel_id', $classroomIds);
        } elseif ($roles->contains('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            $classroomIds = PenugasanMengajar::where('tutor_id', $tutor?->id)->pluck('classroom_id')->unique();
            $query->whereIn('rombel_id', $classroomIds);
        }

        if ($filters['academic_year_id']) {
            $query->where('tahun_ajaran_id', $filters['academic_year_id']);
        }

        if ($filters['program_id']) {
            $query->where('program_id', $filters['program_id']);
        }

        if ($filters['classroom_id']) {
            $query->where('rombel_id', $filters['classroom_id']);
        }

        if ($filters['keyword']) {
            $keyword = $filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_lengkap', 'like', "%{$keyword}%")
                    ->orWhere('nomor_induk', 'like', "%{$keyword}%");
            });
        }

        $students = $query->latest()->paginate(15)->withQueryString();

        $academicYears = AcademicYear::latest()->get();
        $programs = Program::all();
        $classrooms = Classroom::all();

        return view('rekap.index', compact('students', 'filters', 'academicYears', 'programs', 'classrooms'));
    }

    public function show(Student $student)
    {
        $user = auth()->user();
        abort_if(! $user->can('reports.view') && ! $user->hasAnyRole(['tutor', 'peserta-didik']), 403);

        $roles = $user->getRoleNames();

        if ($roles->contains('peserta-didik')) {
            abort_if($user->student?->id !== $student->id, 403);
        } elseif ($roles->contains('wali-kelas')) {
            $wali = Tutor::where('user_id', $user->id)->first();
            $classroomIds = Classroom::where('wali_kelas_id', $wali?->id)->pluck('id');
            abort_if(! $classroomIds->contains($student->rombel_id), 403);
        } elseif ($roles->contains('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            $classroomIds = PenugasanMengajar::where('tutor_id', $tutor?->id)->pluck('classroom_id');
            abort_if(! $classroomIds->contains($student->rombel_id), 403);
        }

        return $this->showStudentDetail($student);
    }

    private function showStudentDetail(Student $student)
    {
        $student->load(['classroom', 'program', 'academicYear']);

        // Absensi statistics
        $attendanceStats = Attendance::where('student_id', $student->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalAttendance = array_sum($attendanceStats);
        $attendancePercentage = $totalAttendance > 0 ? round((($attendanceStats['hadir'] ?? 0) / $totalAttendance) * 100, 1) : 0;

        // Grades
        $grades = Grade::with(['penugasanMengajar.subject', 'penugasanMengajar.tutor', 'penugasanMengajar.semester'])
            ->where('student_id', $student->id)
            ->get();

        $averageGrade = $grades->avg('nilai_akhir') ?? 0;

        // Submissions
        $submissions = Submission::with('assignment.penugasanMengajar.subject')
            ->where('student_id', $student->id)
            ->get();

        $totalSubmissions = $submissions->count();
        $gradedSubmissions = $submissions->where('status', 'graded')->count();
        $averageSubmissionScore = $submissions->where('status', 'graded')->avg('nilai') ?? 0;

        return view('rekap.show', compact(
            'student',
            'attendanceStats',
            'totalAttendance',
            'attendancePercentage',
            'grades',
            'averageGrade',
            'submissions',
            'totalSubmissions',
            'gradedSubmissions',
            'averageSubmissionScore'
        ));
    }
}
