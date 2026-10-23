<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\PenugasanMengajar;
use App\Models\Program;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Tutor;
use Illuminate\Http\Request;

class RaporController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_if(! $user->can('reports.view') && ! $user->hasAnyRole(['tutor', 'peserta-didik']), 403);

        $roles = $user->getRoleNames();

        if ($roles->contains('peserta-didik')) {
            $student = $user->student;
            abort_if(! $student, 403);

            return redirect()->route('rapor.show', $student);
        }

        $query = Student::with(['classroom', 'program', 'academicYear']);

        $filters = [
            'academic_year_id' => $request->input('academic_year_id'),
            'semester_id' => $request->input('semester_id'),
            'program_id' => $request->input('program_id'),
            'classroom_id' => $request->input('classroom_id'),
            'keyword' => $request->input('keyword'),
        ];

        // Role-based restrictions
        $isTutor = $roles->contains('tutor');
        $isWali = $roles->contains('wali-kelas');

        if ($isTutor && $isWali) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            $tutorClassroomIds = $tutor
                ? PenugasanMengajar::where('tutor_id', $tutor->id)->pluck('classroom_id')->unique()
                : collect();
            $waliClassroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
            $classroomIds = $tutorClassroomIds->merge($waliClassroomIds)->unique();
            $query->whereIn('rombel_id', $classroomIds);
        } elseif ($isWali) {
            $classroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
            $query->whereIn('rombel_id', $classroomIds);
        } elseif ($isTutor) {
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
        $semesters = Semester::all();
        $programs = Program::all();
        $classrooms = Classroom::all();

        return view('rapor.index', compact('students', 'filters', 'academicYears', 'semesters', 'programs', 'classrooms'));
    }

    public function show(Request $request, Student $student)
    {
        $this->authorizeStudentAccess($student);

        $data = $this->buildRaporData($student, $request);

        return view('rapor.show', $data);
    }

    public function print(Request $request, Student $student)
    {
        $this->authorizeStudentAccess($student);

        $data = $this->buildRaporData($student, $request);

        return view('rapor.print', $data);
    }

    private function authorizeStudentAccess(Student $student): void
    {
        $user = auth()->user();
        abort_if(! $user->can('reports.view') && ! $user->hasAnyRole(['tutor', 'peserta-didik']), 403);

        $roles = $user->getRoleNames();

        // Admin/operator roles with reports.view permission have full access
        if ($user->hasAnyRole(['operator', 'kepala-pkbm', 'ketua-yayasan'])) {
            return;
        }

        $canAccess = false;

        if ($roles->contains('peserta-didik')) {
            $canAccess = $user->student?->id === $student->id;
        }

        if ($roles->contains('wali-kelas')) {
            $classroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
            $canAccess = $canAccess || $classroomIds->contains($student->rombel_id);
        }

        if ($roles->contains('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            $classroomIds = PenugasanMengajar::where('tutor_id', $tutor?->id)->pluck('classroom_id');
            $canAccess = $canAccess || $classroomIds->contains($student->rombel_id);
        }

        abort_if(! $canAccess, 403);
    }

    private function buildRaporData(Student $student, Request $request): array
    {
        $student->load(['classroom.waliKelas', 'program', 'academicYear']);

        $academicYearId = $request->input('academic_year_id', $student->tahun_ajaran_id);
        $selectedAcademicYear = is_numeric($academicYearId) ? AcademicYear::find($academicYearId) : null;
        if (! $selectedAcademicYear) {
            $selectedAcademicYear = $student->academicYear ?? AcademicYear::latest()->first();
        }
        $selectedAcademicYearId = $selectedAcademicYear instanceof AcademicYear ? $selectedAcademicYear->id : null;

        $semesterId = $request->input('semester_id');
        $selectedSemester = $semesterId ? Semester::find($semesterId) : Semester::where('status_aktif', true)->first() ?? Semester::first();
        $selectedSemesterId = $selectedSemester?->id;

        // Query Grades
        $gradesQuery = Grade::with(['penugasanMengajar.subject', 'penugasanMengajar.tutor'])
            ->where('student_id', $student->id);

        if ($selectedAcademicYearId) {
            $gradesQuery->whereHas('penugasanMengajar', fn ($q) => $q->where('academic_year_id', $selectedAcademicYearId));
        }
        if ($selectedSemesterId) {
            $gradesQuery->whereHas('penugasanMengajar', fn ($q) => $q->where('semester_id', $selectedSemesterId));
        }

        $grades = $gradesQuery->get();

        // Absensi
        $attendanceQuery = Attendance::where('student_id', $student->id);
        if ($selectedAcademicYearId || $selectedSemesterId) {
            $attendanceQuery->whereHas('schedule.penugasanMengajar', function ($q) use ($selectedAcademicYearId, $selectedSemesterId) {
                if ($selectedAcademicYearId) {
                    $q->where('academic_year_id', $selectedAcademicYearId);
                }
                if ($selectedSemesterId) {
                    $q->where('semester_id', $selectedSemesterId);
                }
            });
        }

        $attendanceStats = $attendanceQuery->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalHadir = $attendanceStats['hadir'] ?? 0;
        $totalIzin = $attendanceStats['izin'] ?? 0;
        $totalSakit = $attendanceStats['sakit'] ?? 0;
        $totalAlpa = $attendanceStats['alpa'] ?? 0;

        $averageGrade = $grades->count() > 0 ? round($grades->avg('nilai_akhir'), 2) : 0;
        $statusKelulusan = $averageGrade >= 70 ? 'LULUS / TUNTAS' : 'BELUM TUNTAS';

        $academicYears = AcademicYear::latest()->get();
        $semesters = Semester::all();

        return compact(
            'student',
            'selectedAcademicYear',
            'selectedSemester',
            'academicYears',
            'semesters',
            'grades',
            'totalHadir',
            'totalIzin',
            'totalSakit',
            'totalAlpa',
            'averageGrade',
            'statusKelulusan'
        );
    }
}
