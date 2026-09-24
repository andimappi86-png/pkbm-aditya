<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Document;
use App\Models\Forum;
use App\Models\Grade;
use App\Models\Letter;
use App\Models\Material;
use App\Models\PenugasanMengajar;
use App\Models\Ppdb;
use App\Models\Program;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Submission;
use App\Models\Tutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Fokus: monitoring institusi - tidak perlu detail operasional harian
     */
    public function yayasan(): View
    {
        abort_unless(auth()->user()->can('dashboard.view'), 403);
        abort_unless(auth()->user()->hasRole('ketua-yayasan'), 403);

        $totalStudents = Student::count();
        $activeStudents = Student::where('status', 'aktif')->count();
        $totalTutors = Tutor::count();
        $activeTutors = Tutor::where('status_tutor', 'aktif')->count();
        $totalClassrooms = Classroom::count();
        $totalPrograms = Program::count();

        // Attendance overview
        $totalAttendance = Attendance::count();
        $hadirCount = Attendance::whereIn('status', ['hadir', 'izin'])->count();
        $attendanceRate = $totalAttendance > 0 ? round(($hadirCount / $totalAttendance) * 100) : 0;

        // PPDB Ringkasan
        $ppdbStats = [
            'total' => Ppdb::count(),
            'diterima' => Ppdb::where('status', 'diterima')->count(),
            'pending' => Ppdb::where('status', 'pending')->count(),
        ];

        // Program breakdown
        $programStats = Program::withCount('students')->get();

        return view('dashboards.yayasan', compact(
            'totalStudents', 'activeStudents', 'totalTutors', 'activeTutors',
            'totalClassrooms', 'totalPrograms', 'attendanceRate',
            'ppdbStats', 'programStats'
        ));
    }

    /**
     * Dashboard Kepala PKBM
     * Fokus: monitoring dan pengelolaan operasional PKBM
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

        // Attendance overview
        $totalSchedule = Attendance::count();
        $hadirCount = Attendance::whereIn('status', ['hadir', 'izin'])->count();
        $attendanceRate = $totalSchedule > 0 ? round(($hadirCount / $totalSchedule) * 100) : 0;

        // Documents & Letters (administrasi)
        $documentsCount = Document::count();
        $pendingDocuments = Document::where('status_verifikasi', 'pending')->count();
        $lettersCount = Letter::count();

        // Announcements
        $activeAnnouncements = Announcement::where('status_aktif', true)->count();

        // Program breakdown
        $programStats = Program::withCount('students')->get();

        return view('dashboards.kepala', compact(
            'totalStudents', 'totalTutors', 'totalClassrooms', 'totalPrograms',
            'ppdbStats', 'attendanceRate', 'documentsCount',
            'pendingDocuments', 'lettersCount', 'activeAnnouncements', 'programStats'
        ));
    }

    /**
     * Dashboard Wali Kelas
     * Fokus: rombel yang menjadi tanggung jawabnya
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
     * Fokus: pembelajaran pada penugasannya
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
     * Fokus: aktivitas belajar pribadi
     */
    public function kelasSaya(Request $request): View
    {
        abort_unless(auth()->user()->can('dashboard.view'), 403);
        $user = auth()->user();

        $penugasan = collect();
        $myClassroom = null;

        if ($user->hasRole('peserta-didik')) {
            $student = $user->student;
            $myClassroom = $student ? $student->classroom : null;
            if ($myClassroom) {
                $penugasan = PenugasanMengajar::with(['subject', 'tutor.user', 'materials' => function ($q) {
                    $q->latest();
                }, 'assignments' => function ($q) {
                    $q->where('deadline', '>=', now())->orWhereNull('deadline')->orderBy('deadline', 'asc');
                }, 'quizzes'])
                    ->where('classroom_id', $myClassroom->id)
                    ->where('status_aktif', true)
                    ->get();
            }
        } elseif ($user->hasRole('tutor')) {
            $tutor = $user->tutor;
            if ($tutor) {
                $penugasan = PenugasanMengajar::with(['subject', 'classroom', 'materials' => function ($q) {
                    $q->latest();
                }, 'assignments' => function ($q) {
                    $q->where('deadline', '>=', now())->orWhereNull('deadline')->orderBy('deadline', 'asc');
                }, 'quizzes'])
                    ->where('tutor_id', $tutor->id)
                    ->where('status_aktif', true)
                    ->get();
            }
        } elseif ($user->hasRole('wali-kelas')) {
            $classrooms = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
            if ($classrooms->isNotEmpty()) {
                $penugasan = PenugasanMengajar::with(['subject', 'tutor.user', 'classroom', 'materials' => function ($q) {
                    $q->latest();
                }, 'assignments' => function ($q) {
                    $q->where('deadline', '>=', now())->orWhereNull('deadline')->orderBy('deadline', 'asc');
                }, 'quizzes'])
                    ->whereIn('classroom_id', $classrooms)
                    ->where('status_aktif', true)
                    ->get();
            }
        } else {
            abort(403);
        }

        return view('dashboards.siswa.kelas-saya', compact('penugasan', 'myClassroom'));
    }

    public function kelasSayaShow(Request $request, PenugasanMengajar $penugasanMengajar): View
    {
        abort_unless(auth()->user()->can('dashboard.view'), 403);
        $user = auth()->user();

        // Check Access
        $hasAccess = false;
        if ($user->hasRole('peserta-didik')) {
            $student = $user->student;
            $hasAccess = $student && $student->classroom && $penugasanMengajar->classroom_id === $student->classroom->id;
        } elseif ($user->hasRole('tutor')) {
            $tutor = $user->tutor;
            $hasAccess = $tutor && $penugasanMengajar->tutor_id === $tutor->id;
        } elseif ($user->hasRole('wali-kelas')) {
            $classroom = Classroom::find($penugasanMengajar->classroom_id);
            $hasAccess = $classroom && $classroom->wali_kelas_id === $user->id;
        }

        abort_unless($hasAccess, 403);

        $penugasanMengajar->load([
            'subject',
            'tutor.user',
            'classroom.program',
            'classroom.academicYear',
            'materials' => fn ($q) => $q->latest()->take(3),
            'assignments' => fn ($q) => $q->latest()->take(3),
            'quizzes' => fn ($q) => $q->latest()->take(3),
            'forums' => fn ($q) => $q->latest()->take(3),
        ]);

        return view('dashboards.siswa.detail-kelas', compact('penugasanMengajar'));
    }

    public function siswa(): View
    {
        abort_unless(auth()->user()->can('dashboard.view'), 403);
        abort_unless(auth()->user()->hasRole('peserta-didik'), 403);

        $student = auth()->user()->student;
        $myClassroom = $student ? $student->classroom : null;

        $stats = [
            'total_tugas' => 0,
            'tugas_selesai' => 0,
            'total_materi' => 0,
            'kehadiran' => 0,
            'total_kuis' => 0,
            'kuis_diikuti' => 0,
        ];

        $recentAnnouncements = collect();
        $upcomingAssignments = collect();
        $upcomingSchedules = collect();
        $recentMaterials = collect();
        $upcomingQuizzes = collect();
        $penugasans = collect();
        $recentGrades = collect();
        $recentForums = collect();
        $hadirCount = 0;
        $izinCount = 0;
        $sakitCount = 0;
        $alpaCount = 0;

        if ($student && $myClassroom) {
            $penugasanIds = PenugasanMengajar::where('classroom_id', $myClassroom->id)->where('status_aktif', true)->pluck('id');

            // Lanjutkan Belajar data
            $penugasans = PenugasanMengajar::with(['subject', 'materials' => function ($q) {
                $q->latest();
            }, 'assignments' => function ($q) {
                $q->where('deadline', '>=', now())->orWhereNull('deadline')->orderBy('deadline', 'asc');
            }])
                ->whereIn('id', $penugasanIds)
                ->get();

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

            $hadirCount = Attendance::where('student_id', $student->id)->where('status', 'hadir')->count();
            $izinCount = Attendance::where('student_id', $student->id)->where('status', 'izin')->count();
            $sakitCount = Attendance::where('student_id', $student->id)->where('status', 'sakit')->count();
            $alpaCount = Attendance::where('student_id', $student->id)->where('status', 'alpa')->count();
            $totalSchedule = Attendance::where('student_id', $student->id)->count();

            $stats['kehadiran'] = $totalSchedule > 0 ? round((($hadirCount + $izinCount) / $totalSchedule) * 100) : 0;

            // Tugas Terdekat (Pending)
            $submittedAssignmentIds = Submission::where('student_id', $student->id)->pluck('assignment_id');
            $upcomingAssignments = Assignment::with('penugasanMengajar.subject')
                ->whereIn('penugasan_mengajar_id', $penugasanIds)
                ->whereNotIn('id', $submittedAssignmentIds)
                ->where(function ($q) {
                    $q->whereNull('deadline')->orWhere('deadline', '>=', now());
                })
                ->orderBy('deadline', 'asc')
                ->take(4)
                ->get();

            // Jadwal Terdekat - ordered by day and time
            $upcomingSchedules = Schedule::with(['penugasanMengajar.subject', 'penugasanMengajar.tutor.user'])
                ->whereIn('penugasan_mengajar_id', $penugasanIds)
                ->where('status_aktif', true)
                ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
                ->orderBy('jam_mulai', 'asc')
                ->take(3)
                ->get();

            // Materi Terbaru
            $recentMaterials = Material::with(['penugasanMengajar.subject', 'penugasanMengajar.tutor.user'])
                ->whereIn('penugasan_mengajar_id', $penugasanIds)
                ->latest()
                ->take(3)
                ->get();

            // Kuis yang tersedia
            $attemptedQuizIds = QuizAttempt::where('student_id', $student->id)->pluck('quiz_id');
            $upcomingQuizzes = Quiz::with('penugasanMengajar.subject')
                ->whereIn('penugasan_mengajar_id', $penugasanIds)
                ->whereNotIn('id', $attemptedQuizIds)
                ->where('status_aktif', true)
                ->latest()
                ->take(3)
                ->get();

            // Nilai Terbaru
            $recentGrades = Grade::with(['penugasanMengajar.subject'])
                ->where('student_id', $student->id)
                ->latest()
                ->take(3)
                ->get();

            // Forum Diskusi
            $recentForums = Forum::with(['penugasanMengajar.subject', 'user'])
                ->withCount('comments')
                ->whereIn('penugasan_mengajar_id', $penugasanIds)
                ->where('status_aktif', true)
                ->latest()
                ->take(3)
                ->get();

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
                ->take(3)
                ->get();
        }

        return view('dashboards.siswa', [
            'myClassroom' => $myClassroom,
            'stats' => $stats,
            'recentAnnouncements' => $recentAnnouncements,
            'upcomingAssignments' => $upcomingAssignments,
            'upcomingSchedules' => $upcomingSchedules,
            'recentMaterials' => $recentMaterials,
            'upcomingQuizzes' => $upcomingQuizzes,
            'penugasans' => $penugasans,
            'recentGrades' => $recentGrades,
            'recentForums' => $recentForums,
            'hadirCount' => $hadirCount,
            'izinCount' => $izinCount,
            'sakitCount' => $sakitCount,
            'alpaCount' => $alpaCount,
        ]);
    }
}
