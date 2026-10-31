<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Program;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                abort_unless(auth()->user()->hasAnyRole(['operator', 'kepala-pkbm', 'ketua-yayasan']), 403, 'Anda tidak memiliki akses ke laporan institusi.');

                return $next($request);
            }),
        ];
    }

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('reports.view'), 403);

        $filters = $this->getFilters($request);

        $programs = Program::where('status_aktif', true)->get();
        $classrooms = Classroom::where('status_aktif', true)->get();

        return view('reports.index', compact('filters', 'programs', 'classrooms'));
    }

    public function students(Request $request): View
    {
        abort_unless(auth()->user()->can('reports.view'), 403);

        $filters = $this->getFilters($request);
        $query = Student::with(['program', 'classroom', 'user']);

        $this->applyStudentFilters($query, $filters);

        $students = $query->orderBy('nis')->paginate(20)->withQueryString();

        return view('reports.students', compact('students', 'filters'));
    }

    public function tutors(Request $request): View
    {
        abort_unless(auth()->user()->can('reports.view'), 403);

        $filters = $this->getFilters($request);
        $query = Tutor::with(['user', 'subjects']);

        if ($filters['program_id']) {
            $query->whereHas('subjects', function ($q) use ($filters) {
                $q->where('program_id', $filters['program_id']);
            });
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_lengkap', 'like', "%{$filters['search']}%")
                    ->orWhere('nomor_induk', 'like', "%{$filters['search']}%")
                    ->orWhereHas('user', function ($uq) use ($filters) {
                        $uq->where('name', 'like', "%{$filters['search']}%");
                    });
            });
        }

        $tutors = $query->orderBy('nama_lengkap')->paginate(20)->withQueryString();

        return view('reports.tutors', compact('tutors', 'filters'));
    }

    public function classrooms(Request $request): View
    {
        abort_unless(auth()->user()->can('reports.view'), 403);

        $filters = $this->getFilters($request);
        $query = Classroom::with(['program', 'academicYear', 'waliKelas'])->withCount('students');

        $this->applyClassroomFilters($query, $filters);

        $classrooms = $query->orderBy('nama_rombel')->paginate(20)->withQueryString();

        return view('reports.classrooms', compact('classrooms', 'filters'));
    }

    public function attendance(Request $request): View
    {
        abort_unless(auth()->user()->can('reports.view'), 403);

        $filters = $this->getFilters($request);

        $query = Attendance::with(['student.user', 'student.classroom', 'schedule.penugasanMengajar.subject', 'schedule.penugasanMengajar.tutor.user'])
            ->latest('tanggal');

        $this->applyFilters($query, $filters);

        $attendances = $query->paginate(50)->withQueryString();

        // Summary stats
        $total = $attendances->total();
        $hadir = $attendances->whereIn('status', ['hadir', 'izin'])->count();
        $alpha = $attendances->where('status', 'alpha')->count();
        $rate = $total > 0 ? round(($hadir / $total) * 100) : 0;

        $stats = compact('total', 'hadir', 'alpha', 'rate');

        return view('reports.attendance', compact('attendances', 'filters', 'stats'));
    }

    public function grades(Request $request): View
    {
        abort_unless(auth()->user()->can('reports.view'), 403);

        $filters = $this->getFilters($request);

        $query = Grade::with(['student.user', 'student.classroom', 'penugasanMengajar.subject'])
            ->latest('created_at');

        $this->applyFilters($query, $filters);

        $grades = $query->paginate(50)->withQueryString();

        // Summary
        $avg = $grades->avg('nilai_akhir') ?? 0;
        $count = $grades->total();
        $stats = compact('avg', 'count');

        return view('reports.grades', compact('grades', 'filters', 'stats'));
    }

    public function assignments(Request $request): View
    {
        abort_unless(auth()->user()->can('reports.view'), 403);

        $filters = $this->getFilters($request);

        $query = Assignment::with(['penugasanMengajar.subject', 'penugasanMengajar.classroom', 'penugasanMengajar.tutor.user', 'submissions'])
            ->latest('created_at');

        $this->applyFilters($query, $filters);

        $assignments = $query->paginate(20)->withQueryString();

        return view('reports.assignments', compact('assignments', 'filters'));
    }

    public function learningActivity(Request $request): View
    {
        abort_unless(auth()->user()->can('reports.view'), 403);

        $filters = $this->getFilters($request);

        $query = Assignment::with(['penugasanMengajar.subject', 'penugasanMengajar.classroom', 'penugasanMengajar.tutor.user', 'submissions'])
            ->latest('created_at');

        $this->applyFilters($query, $filters);

        $assignments = $query->get();

        $quizQuery = Quiz::with(['penugasanMengajar.subject', 'penugasanMengajar.classroom', 'penugasanMengajar.tutor.user', 'attempts'])
            ->latest('created_at');

        $this->applyFilters($quizQuery, $filters);
        $quizzes = $quizQuery->get();

        $materialQuery = Material::with(['penugasanMengajar.subject', 'penugasanMengajar.classroom', 'penugasanMengajar.tutor.user'])
            ->latest('created_at');

        $this->applyFilters($materialQuery, $filters);
        $materials = $materialQuery->get();

        $stats = [
            'total_materi' => $materials->count(),
            'total_tugas' => $assignments->count(),
            'total_kuis' => $quizzes->count(),
            'total_pengumpulan' => $assignments->sum('submissions_count'),
            'total_kuis_diikuti' => $quizzes->sum('attempts_count'),
        ];

        return view('reports.learning', compact('stats', 'filters', 'assignments', 'quizzes', 'materials'));
    }

    public function print(Request $request, string $type): View
    {
        abort_unless(auth()->user()->can('reports.view'), 403);

        $filters = $this->getFilters($request);

        return match ($type) {
            'students' => $this->printStudents($filters),
            'tutors' => $this->printTutors($filters),
            'classrooms' => $this->printClassrooms($filters),
            'attendance' => $this->printAttendance($filters),
            'grades' => $this->printGrades($filters),
            'assignments' => $this->printAssignments($filters),
            'learning' => $this->printLearning($filters),
            default => abort(404),
        };
    }

    private function applyClassroomFilters($query, array $filters): void
    {
        if (! empty($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }

        if (! empty($filters['tahun_ajaran_id'])) {
            $query->where('tahun_ajaran_id', $filters['tahun_ajaran_id']);
        }

        if (! empty($filters['search'])) {
            $query->where('nama_rombel', 'like', "%{$filters['search']}%");
        }
    }

    private function applyStudentFilters($query, array $filters): void
    {
        if ($filters['tahun_ajaran_id']) {
            $query->where('tahun_ajaran_id', $filters['tahun_ajaran_id']);
        }
        if ($filters['program_id']) {
            $query->where('program_id', $filters['program_id']);
        }
        if ($filters['rombel_id']) {
            $query->where('rombel_id', $filters['rombel_id']);
        }
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }
        if ($filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }
    }

    private function getFilters(Request $request): array
    {
        return [
            'tahun_ajaran_id' => $request->input('tahun_ajaran_id'),
            'semester_id' => $request->input('semester_id'),
            'program_id' => $request->input('program_id'),
            'rombel_id' => $request->input('rombel_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'search' => $request->input('search'),
            'status' => $request->input('status'),
        ];
    }

    private function applyFilters($query, array $filters): void
    {
        if ($filters['tahun_ajaran_id']) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('tahun_ajaran_id', $filters['tahun_ajaran_id']);
            })->orWhereHas('penugasanMengajar', function ($q) use ($filters) {
                $q->where('tahun_ajaran_id', $filters['tahun_ajaran_id']);
            });
        }

        if ($filters['semester_id']) {
            $query->whereHas('penugasanMengajar', function ($q) use ($filters) {
                $q->where('semester_id', $filters['semester_id']);
            });
        }

        if ($filters['program_id']) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('program_id', $filters['program_id']);
            })->orWhereHas('penugasanMengajar.classroom', function ($q) use ($filters) {
                $q->where('program_id', $filters['program_id']);
            });
        }

        if ($filters['rombel_id']) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('rombel_id', $filters['rombel_id']);
            })->orWhereHas('penugasanMengajar', function ($q) use ($filters) {
                $q->where('classroom_id', $filters['rombel_id']);
            });
        }

        if ($filters['date_from']) {
            $query->whereDate('tanggal', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $query->whereDate('tanggal', '<=', $filters['date_to']);
        }
        if ($filters['search']) {
            $query->whereHas('student.user', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%");
            });
        }
    }

    private function printStudents(array $filters): View
    {
        $query = Student::with(['program', 'classroom', 'user']);
        $this->applyStudentFilters($query, $filters);
        $students = $query->orderBy('nis')->get();

        return view('reports.print.students', compact('students', 'filters'));
    }

    private function printTutors(array $filters): View
    {
        $query = Tutor::with(['user', 'subjects']);
        if ($filters['program_id']) {
            $query->whereHas('subjects', function ($q) use ($filters) {
                $q->where('program_id', $filters['program_id']);
            });
        }
        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_lengkap', 'like', "%{$filters['search']}%")
                    ->orWhere('nomor_induk', 'like', "%{$filters['search']}%")
                    ->orWhereHas('user', function ($uq) use ($filters) {
                        $uq->where('name', 'like', "%{$filters['search']}%");
                    });
            });
        }
        $tutors = $query->orderBy('nama_lengkap')->get();

        return view('reports.print.tutors', compact('tutors', 'filters'));
    }

    private function printClassrooms(array $filters): View
    {
        $query = Classroom::with(['program', 'academicYear', 'waliKelas'])->withCount('students');
        $this->applyClassroomFilters($query, $filters);
        $classrooms = $query->orderBy('nama_rombel')->get();

        return view('reports.print.classrooms', compact('classrooms', 'filters'));
    }

    private function printAttendance(array $filters): View
    {
        $query = Attendance::with(['student.user', 'student.classroom', 'schedule.penugasanMengajar.subject', 'schedule.penugasanMengajar.tutor.user'])
            ->latest('tanggal');
        $this->applyPrintFilters($query, $filters);
        $attendances = $query->get();

        $total = $attendances->count();
        $hadir = $attendances->whereIn('status', ['hadir', 'izin'])->count();
        $alpha = $attendances->where('status', 'alpha')->count();
        $rate = $total > 0 ? round(($hadir / $total) * 100) : 0;

        return view('reports.print.attendance', compact('attendances', 'filters', 'total', 'hadir', 'alpha', 'rate'));
    }

    private function printGrades(array $filters): View
    {
        $query = Grade::with(['student.user', 'student.classroom', 'penugasanMengajar.subject'])
            ->latest('created_at');
        $this->applyPrintFilters($query, $filters);
        $grades = $query->get();

        $avg = $grades->avg('nilai_akhir') ?? 0;

        return view('reports.print.grades', compact('grades', 'filters', 'avg'));
    }

    private function printAssignments(array $filters): View
    {
        $query = Assignment::with(['penugasanMengajar.subject', 'penugasanMengajar.classroom', 'penugasanMengajar.tutor.user', 'submissions'])
            ->latest('created_at');
        $this->applyPrintFilters($query, $filters);
        $assignments = $query->get();

        return view('reports.print.assignments', compact('assignments', 'filters'));
    }

    private function printLearning(array $filters): View
    {
        $query = Assignment::with(['penugasanMengajar.subject', 'penugasanMengajar.classroom', 'penugasanMengajar.tutor.user', 'submissions'])
            ->latest('created_at');
        $this->applyPrintFilters($query, $filters);
        $assignments = $query->get();

        $quizQuery = Quiz::with(['penugasanMengajar.subject', 'penugasanMengajar.classroom', 'penugasanMengajar.tutor.user', 'attempts'])
            ->latest('created_at');
        $this->applyPrintFilters($quizQuery, $filters);
        $quizzes = $quizQuery->get();

        $materialQuery = Material::with(['penugasanMengajar.subject', 'penugasanMengajar.classroom', 'penugasanMengajar.tutor.user'])
            ->latest('created_at');
        $this->applyPrintFilters($materialQuery, $filters);
        $materials = $materialQuery->get();

        $stats = [
            'total_materi' => $materials->count(),
            'total_tugas' => $assignments->count(),
            'total_kuis' => $quizzes->count(),
            'total_pengumpulan' => $assignments->sum('submissions_count'),
            'total_kuis_diikuti' => $quizzes->sum('attempts_count'),
        ];

        return view('reports.print.learning', compact('stats', 'filters'));
    }

    private function applyPrintFilters($query, array $filters): void
    {
        if ($filters['tahun_ajaran_id']) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('tahun_ajaran_id', $filters['tahun_ajaran_id']);
            })->orWhereHas('penugasanMengajar', function ($q) use ($filters) {
                $q->where('tahun_ajaran_id', $filters['tahun_ajaran_id']);
            });
        }

        if ($filters['semester_id']) {
            $query->whereHas('penugasanMengajar', function ($q) use ($filters) {
                $q->where('semester_id', $filters['semester_id']);
            });
        }

        if ($filters['program_id']) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('program_id', $filters['program_id']);
            })->orWhereHas('penugasanMengajar.classroom', function ($q) use ($filters) {
                $q->where('program_id', $filters['program_id']);
            });
        }

        if ($filters['rombel_id']) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('rombel_id', $filters['rombel_id']);
            })->orWhereHas('penugasanMengajar', function ($q) use ($filters) {
                $q->where('classroom_id', $filters['rombel_id']);
            });
        }

        if ($filters['date_from']) {
            $query->whereDate('tanggal', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $query->whereDate('tanggal', '<=', $filters['date_to']);
        }
    }
}
