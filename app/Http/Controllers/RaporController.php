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
    }

    private function buildRaporData(Student $student, Request $request): array
    {
        $student->load(['classroom.waliKelas', 'program', 'academicYear']);

        $academicYearId = $request->input('academic_year_id', $student->tahun_ajaran_id);
        $selectedAcademicYear = AcademicYear::find($academicYearId) ?? $student->academicYear ?? AcademicYear::latest()->first();

        $semesterId = $request->input('semester_id');
        $selectedSemester = $semesterId ? Semester::find($semesterId) : Semester::where('is_active', true)->first() ?? Semester::first();

        // Query Grades
        $gradesQuery = Grade::with(['penugasanMengajar.subject', 'penugasanMengajar.tutor'])
            ->where('student_id', $student->id);

        if ($selectedAcademicYear) {
            $gradesQuery->whereHas('penugasanMengajar', fn ($q) => $q->where('academic_year_id', $selectedAcademicYear->id));
        }
        if ($selectedSemester) {
            $gradesQuery->whereHas('penugasanMengajar', fn ($q) => $q->where('semester_id', $selectedSemester->id));
        }

        $grades = $gradesQuery->get();

        // Absensi
        $attendanceQuery = Attendance::where('student_id', $student->id);
        if ($selectedAcademicYear || $selectedSemester) {
            $attendanceQuery->whereHas('schedule.penugasanMengajar', function ($q) use ($selectedAcademicYear, $selectedSemester) {
                if ($selectedAcademicYear) {
                    $q->where('academic_year_id', $selectedAcademicYear->id);
                }
                if ($selectedSemester) {
                    $q->where('semester_id', $selectedSemester->id);
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
