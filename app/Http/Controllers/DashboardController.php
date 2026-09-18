<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\PenugasanMengajar;
use App\Models\Program;
use App\Models\Quiz;
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

        return view('dashboards.yayasan', [
            'totalStudents' => Student::count(),
            'totalTutors' => Tutor::count(),
            'totalClassrooms' => Classroom::count(),
            'totalPrograms' => Program::count(),
        ]);
    }

    /**
     * Dashboard Kepala PKBM
     */
    public function kepala(): View
    {
        abort_unless(auth()->user()->can('dashboard.view'), 403);
        abort_unless(auth()->user()->hasRole('kepala-pkbm'), 403);

        return view('dashboards.kepala', [
            'totalStudents' => Student::count(),
            'totalTutors' => Tutor::count(),
            'totalClassrooms' => Classroom::count(),
            'totalPrograms' => Program::count(),
        ]);
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
            'kehadiran_rata' => 0,
        ];

        if ($classroomIds->count() > 0) {
            $penugasanIds = PenugasanMengajar::whereIn('classroom_id', $classroomIds)->pluck('id');

            $stats['total_materi'] = Material::whereIn('penugasan_mengajar_id', $penugasanIds)->count();
            $stats['total_tugas'] = Assignment::whereIn('penugasan_mengajar_id', $penugasanIds)->count();

            // Average attendance percentage across all classrooms
            $studentIds = Student::whereIn('rombel_id', $classroomIds)->pluck('id');
            if ($studentIds->count() > 0) {
                $hadirCount = Attendance::whereIn('student_id', $studentIds)
                    ->whereIn('status', ['hadir', 'izin'])->count();
                $totalSchedule = Attendance::whereIn('student_id', $studentIds)->count();

                $stats['kehadiran_rata'] = $totalSchedule > 0 ? round(($hadirCount / $totalSchedule) * 100) : 0;
            }
        }

        return view('dashboards.walikelas', [
            'myClassrooms' => $myClassrooms,
            'stats' => $stats,
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
        ];

        if ($tutor) {
            $mySubjects = $tutor->subjects;
            $myClasses = Classroom::whereHas('penugasanMengajar', function ($q) use ($tutor) {
                $q->where('tutor_id', $tutor->id);
            })->withCount('students')->get();

            $penugasanIds = PenugasanMengajar::where('tutor_id', $tutor->id)->pluck('id');

            $stats['total_siswa'] = $myClasses->sum('students_count');
            $stats['total_materi'] = Material::whereIn('penugasan_mengajar_id', $penugasanIds)->count();
            $stats['total_tugas'] = Assignment::whereIn('penugasan_mengajar_id', $penugasanIds)->count();
            $stats['total_kuis'] = Quiz::whereIn('penugasan_mengajar_id', $penugasanIds)->count();
        }

        return view('dashboards.tutor', [
            'mySubjects' => $mySubjects,
            'myClasses' => $myClasses,
            'stats' => $stats,
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
        ];

        if ($student && $myClassroom) {
            $penugasanIds = PenugasanMengajar::where('classroom_id', $myClassroom->id)->pluck('id');

            $stats['total_tugas'] = Assignment::whereIn('penugasan_mengajar_id', $penugasanIds)->count();

            $stats['tugas_selesai'] = Submission::where('student_id', $student->id)
                ->whereHas('assignment', function ($q) use ($penugasanIds) {
                    $q->whereIn('penugasan_mengajar_id', $penugasanIds);
                })->count();

            $stats['total_materi'] = Material::whereIn('penugasan_mengajar_id', $penugasanIds)->count();

            $hadirCount = Attendance::where('student_id', $student->id)
                ->whereIn('status', ['hadir', 'izin'])->count();
            $totalSchedule = Attendance::where('student_id', $student->id)->count();

            $stats['kehadiran'] = $totalSchedule > 0 ? round(($hadirCount / $totalSchedule) * 100) : 0;
        }

        return view('dashboards.siswa', [
            'myProgram' => $myProgram,
            'myClassroom' => $myClassroom,
            'stats' => $stats,
        ]);
    }
}
