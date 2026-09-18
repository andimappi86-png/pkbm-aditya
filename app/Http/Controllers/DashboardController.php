<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
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
            'totalStudents' => 0, // Phase 3: real data
            'totalTutors' => 0,
            'totalClassrooms' => 0,
            'totalPrograms' => 3, // Paket A/B/C
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
            'totalStudents' => 0, // Phase 3: real data
            'totalTutors' => 0,
            'totalClassrooms' => 0,
            'totalPrograms' => 3,
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

        return view('dashboards.walikelas', [
            'myClassrooms' => $myClassrooms,
        ]);
    }

    /**
     * Dashboard Tutor
     */
    public function tutor(): View
    {
        abort_unless(auth()->user()->can('dashboard.view'), 403);
        abort_unless(auth()->user()->hasRole('tutor'), 403);

        return view('dashboards.tutor', [
            // Phase 3: assigned subjects and classes
            'mySubjects' => [],
            'myClasses' => [],
        ]);
    }

    /**
     * Dashboard Peserta Didik
     */
    public function siswa(): View
    {
        abort_unless(auth()->user()->can('dashboard.view'), 403);
        abort_unless(auth()->user()->hasRole('peserta-didik'), 403);

        return view('dashboards.siswa', [
            // Phase 3: student's own data
            'myProgram' => null,
            'myClassroom' => null,
        ]);
    }
}
