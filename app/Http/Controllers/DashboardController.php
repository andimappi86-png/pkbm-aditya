<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Document;
use App\Models\Grade;
use App\Models\Letter;
use App\Models\Material;
use App\Models\PenugasanMengajar;
use App\Models\Ppdb;
use App\Models\Program;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Student;
use App\Models\Submission;
use App\Models\Tutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Redirect to appropriate dashboard based on user role
     */
    public function index(): RedirectResponse
    {
        $route = auth()->user()->dashboardRoute();

        return redirect()->route($route);
    }

    /**
     * Dashboard Ketua Yayasan
     */
    public function yayasan(): View
    {
        abort_unless(auth()->user()->can('dashboard.view'), 403);
        abort_unless(auth()->user()->hasRole('ketua-yayasan'), 403);

        $totalStudents = Student::count();
        $totalTutors = Tutor::count();
        $totalClassrooms = Classroom::count();
        $totalPrograms = Program::count();

        // PPDB
        $ppdbStats = [
            'total' => Ppdb::count(),
            'diterima' => Ppdb::where('status', 'diterima')->count(),
            'pending' => Ppdb::where('status', 'pending')->count(),
        ];

        // Academic performance
        $avgGrade = Grade::avg('nilai') ?? 0;

        // Attendance
        $totalSchedule = Attendance::count();
        $hadirCount = Attendance::whereIn('status', ['hadir', 'izin'])->count();
        $attendanceRate = $totalSchedule > 0 ? round(($hadirCount / $totalSchedule) * 100) : 0;

        // Program breakdown
        $programStats = Program::withCount('students')->get();

        return view('dashboards.yayasan', compact(
            'totalStudents', 'totalTutors', 'totalClassrooms', 'totalPrograms',
            'ppdbStats', 'avgGrade', 'attendanceRate', 'programStats'
        ));
    }

    /**
     * Dashboard Kepala PKBM
     */
    public function kepala(): View
    {
        abort_unless(auth()->user()->can('dashboard.view'), 403);
        abort_unless(auth()->user()->hasRole('kepala-pkbm'), 403);

        $totalStudents = Student::count();
        $totalTutors = Tutor::count();
        $totalClassrooms = Classroom::count();
        $totalPrograms = Program::count();

        // PPDB
        $ppdbStats = [
            'total' => Ppdb::count(),
            'diterima' => Ppdb::where('status', 'diterima')->count(),
            'pending' => Ppdb::where('status', 'pending')->count(),
        ];

        // Academic performance
        $avgGrade = Grade::avg('nilai') ?? 0;

        // Attendance
        $totalSchedule = Attendance::count();
        $hadirCount = Attendance::whereIn('status', ['hadir', 'izin'])->count();
        $attendanceRate = $totalSchedule > 0 ? round(($hadirCount / $totalSchedule) * 100) : 0;

        // Documents & Letters
        $documentsCount = Document::count();
        $pendingDocuments = Document::where('status_verifikasi', 'pending')->count();
        $lettersCount = Letter::count();

        // Tasks & Quizzes
        $assignmentsCount = Assignment::count();
        $quizzesCount = Quiz::count();

        // Announcements
        $activeAnnouncements = Announcement::where('status_aktif', true)->count();

        // Program breakdown
        $programStats = Program::withCount('students')->get();

        return view('dashboards.kepala', compact(
            'totalStudents', 'totalTutors', 'totalClassrooms', 'totalPrograms',
            'ppdbStats', 'avgGrade', 'attendanceRate', 'documentsCount',
            'pendingDocuments', 'lettersCount', 'assignmentsCount', 'quizzesCount',
            'activeAnnouncements', 'programStats'
        ));
    }

    /**
     * Dashboard Wali Kelas
     */
    public function walikelas(): View
    {
        abort_unless(auth()->user()->can('dashboard.view'), 403);
        abort_unless(auth()->user()->hasRole('wali-kelas'), 403);

        $myClassrooms = Classroom::where('wali_kelas_id', auth()->user()->id)
            ->withCount('students')
            ->get();

        $classroomIds = $myClassrooms->pluck('id');

        $stats = [
            'total_siswa' => $myClassrooms->sum('students_count'),
            'total_rombel' => $myClassrooms->count(),
            'total_materi' => 0,
            'total_tugas' => 0,
            'total_kuis' => 0,
            'kehadiran_rata' => 0,
            'rata_rata_nilai' => 0,
        ];

        $recentAnnouncements = collect();

        if ($classroomIds->count() > 0) {
            $penugasanIds = PenugasanMengajar::whereIn('classroom_id', $classroomIds)->pluck('id');

            $stats['total_materi'] = Material::whereIn('penugasan_mengajar_id', $penugasanIds)->count();
            $stats['total_tugas'] = Assignment::whereIn('penugasan_mengajar_id', $penugasanIds)->count();
            $stats['total_kuis'] = Quiz::whereIn('penugasan_mengajar_id', $penugasanIds)->count();

            // Average attendance percentage across all classrooms
            $studentIds = Student::whereIn('rombel_id', $classroomIds)->pluck('id');
            if ($studentIds->count() > 0) {
                $hadirCount = Attendance::whereIn('student_id', $studentIds)
                    ->whereIn('status', ['hadir', 'izin'])->count();
                $totalSchedule = Attendance::whereIn('student_id', $studentIds)->count();

                $stats['kehadiran_rata'] = $totalSchedule > 0 ? round(($hadirCount / $totalSchedule) * 100) : 0;

                $stats['rata_rata_nilai'] = Grade::whereIn('student_id', $studentIds)->avg('nilai') ?? 0;
            }

            // Get relevant announcements
            $recentAnnouncements = Announcement::where('status_aktif', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->where(function ($q) use ($classroomIds) {
                    $q->whereNull('target_role')
                        ->orWhere('target_role', 'wali-kelas')
                        ->orWhereIn('target_rombel', $classroomIds);
                })
                ->latest('published_at')
                ->take(5)
                ->get();
        }

        return view('dashboards.walikelas', [
            'myClassrooms' => $myClassrooms,
            'stats' => $stats,
            'recentAnnouncements' => $recentAnnouncements,
        ]);
    }

    /**
     * Dashboard Tutor
     */
    public function tutor(): View
    {
        abort_unless(auth()->user()->can('dashboard.view'), 403);
        abort_unless(auth()->user()->hasRole('tutor'), 403);

        $tutor = auth()->user()->tutor;
        $mySubjects = collect();
        $myClasses = collect();

        $stats = [
            'total_siswa' => 0,
            'total_materi' => 0,
            'total_tugas' => 0,
            'total_kuis' => 0,
            'submission_belum_dinilai' => 0,
        ];

        $recentAnnouncements = collect();

        if ($tutor) {
            $mySubjects = $tutor->subjects;
            $myClasses = Classroom::whereHas('penugasanMengajar', function ($q) use ($tutor) {
                $q->where('tutor_id', $tutor->id);
            })->withCount('students')->get();

            $penugasanIds = PenugasanMengajar::where('tutor_id', $tutor->id)->pluck('id');

            $stats['total_siswa'] = $myClasses->sum('students_count');
            $stats['total_materi'] = Material::whereIn('penugasan_mengajar_id', $penugasanIds)->count();

            $assignments = Assignment::whereIn('penugasan_mengajar_id', $penugasanIds)->get();
            $stats['total_tugas'] = $assignments->count();

            $stats['submission_belum_dinilai'] = Submission::whereIn('assignment_id', $assignments->pluck('id'))
                ->whereNull('nilai')
                ->count();

            $stats['total_kuis'] = Quiz::whereIn('penugasan_mengajar_id', $penugasanIds)->count();

            // Get relevant announcements
            $recentAnnouncements = Announcement::where('status_aktif', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('target_role')
                        ->orWhere('target_role', 'tutor');
                })
                ->latest('published_at')
                ->take(5)
                ->get();
        }

        return view('dashboards.tutor', [
            'mySubjects' => $mySubjects,
            'myClasses' => $myClasses,
            'stats' => $stats,
            'recentAnnouncements' => $recentAnnouncements,
        ]);
    }

    /**
     * Dashboard Peserta Didik
     */
    public function siswa(): View
    {
        abort_unless(auth()->user()->can('dashboard.view'), 403);
        abort_unless(auth()->user()->hasRole('peserta-didik'), 403);

        $student = auth()->user()->student;
        $myProgram = $student ? $student->program : null;
        $myClassroom = $student ? $student->classroom : null;

        $stats = [
            'total_tugas' => 0,
            'tugas_selesai' => 0,
            'total_materi' => 0,
            'kehadiran' => 0,
            'rata_rata_nilai' => 0,
            'total_kuis' => 0,
            'kuis_diikuti' => 0,
        ];

        $recentAnnouncements = collect();

        if ($student && $myClassroom) {
            $penugasanIds = PenugasanMengajar::where('classroom_id', $myClassroom->id)->pluck('id');

            $stats['total_tugas'] = Assignment::whereIn('penugasan_mengajar_id', $penugasanIds)->count();

            $stats['tugas_selesai'] = Submission::where('student_id', $student->id)
                ->whereHas('assignment', function ($q) use ($penugasanIds) {
                    $q->whereIn('penugasan_mengajar_id', $penugasanIds);
                })->count();

            $stats['total_materi'] = Material::whereIn('penugasan_mengajar_id', $penugasanIds)->count();

            $stats['total_kuis'] = Quiz::whereIn('penugasan_mengajar_id', $penugasanIds)->count();

            $stats['kuis_diikuti'] = QuizAttempt::where('student_id', $student->id)
                ->whereHas('quiz', function ($q) use ($penugasanIds) {
                    $q->whereIn('penugasan_mengajar_id', $penugasanIds);
                })->count();

            $hadirCount = Attendance::where('student_id', $student->id)
                ->whereIn('status', ['hadir', 'izin'])->count();
            $totalSchedule = Attendance::where('student_id', $student->id)->count();

            $stats['kehadiran'] = $totalSchedule > 0 ? round(($hadirCount / $totalSchedule) * 100) : 0;

            $stats['rata_rata_nilai'] = Grade::where('student_id', $student->id)->avg('nilai') ?? 0;

            // Get relevant announcements
            $recentAnnouncements = Announcement::where('status_aktif', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->where(function ($q) use ($student) {
                    $q->whereNull('target_role')
                        ->orWhere('target_role', 'peserta-didik')
                        ->orWhere('target_program', $student->program_id)
                        ->orWhere('target_rombel', $student->rombel_id);
                })
                ->latest('published_at')
                ->take(5)
                ->get();
        }

        return view('dashboards.siswa', [
            'myProgram' => $myProgram,
            'myClassroom' => $myClassroom,
            'stats' => $stats,
            'recentAnnouncements' => $recentAnnouncements,
        ]);
    }
}
