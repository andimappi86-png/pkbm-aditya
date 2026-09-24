<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Schedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        Gate::authorize('schedules.view');

        $user = $request->user();
        $query = Schedule::with([
            'penugasanMengajar.tutor',
            'penugasanMengajar.subject',
            'penugasanMengajar.classroom',
        ]);

        // Filter berdasarkan Rombel jika ada
        if ($request->filled('classroom_id')) {
            $query->whereHas('penugasanMengajar', function ($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        // Filter berdasarkan Hari jika ada
        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        // Jika user adalah Tutor, hanya tampilkan jadwal mengajar miliknya
        if ($user->hasRole('tutor') && $user->tutor) {
            $query->whereHas('penugasanMengajar', function ($q) use ($user) {
                $q->where('tutor_id', $user->tutor->id);
            });
        }

        // Jika user adalah Peserta Didik, hanya tampilkan jadwal rombelnya
        if ($user->hasRole('peserta-didik') && $user->student && $user->student->rombel_id) {
            $query->whereHas('penugasanMengajar', function ($q) use ($user) {
                $q->where('classroom_id', $user->student->rombel_id);
            });
        }

        // Jika user adalah Wali Kelas (dan bukan Admin/Operator), hanya tampilkan jadwal rombel yang dipegangnya
        if ($user->hasRole('wali-kelas') && ! $user->hasRole('kepala-pkbm') && ! $user->hasRole('operator')) {
            $classroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
            $query->whereHas('penugasanMengajar', function ($q) use ($classroomIds) {
                $q->whereIn('classroom_id', $classroomIds);
            });
        }

        $schedules = $query->orderBy('hari')
            ->orderBy('jam_mulai')
            ->paginate(15)
            ->withQueryString();

        $classrooms = Classroom::where('status_aktif', true)->get();

        return view('schedules.index', compact('schedules', 'classrooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('schedules.manage');

        $penugasanMengajars = PenugasanMengajar::with(['tutor', 'subject', 'classroom'])
            ->where('status_aktif', true)
            ->get();

        return view('schedules.create', compact('penugasanMengajars'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        Schedule::create($request->validated());

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule): View
    {
        Gate::authorize('schedules.view');

        $this->authorizeScheduleAccess($schedule);

        $schedule->load([
            'penugasanMengajar.tutor',
            'penugasanMengajar.subject',
            'penugasanMengajar.classroom',
        ]);

        return view('schedules.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Schedule $schedule): View
    {
        Gate::authorize('schedules.manage');
        $this->authorizeScheduleAccess($schedule);

        $penugasanMengajars = PenugasanMengajar::with(['tutor', 'subject', 'classroom'])
            ->where('status_aktif', true)
            ->get();

        return view('schedules.edit', compact('schedule', 'penugasanMengajars'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule): RedirectResponse
    {
        Gate::authorize('schedules.manage');
        $this->authorizeScheduleAccess($schedule);

        $schedule->update($request->validated());

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule): RedirectResponse
    {
        Gate::authorize('schedules.manage');
        $this->authorizeScheduleAccess($schedule);

        $schedule->delete();

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }

    /**
     * Authorize access to specific schedule based on user role
     */
    private function authorizeScheduleAccess(Schedule $schedule): void
    {
        $user = auth()->user();

        if ($user->hasRole('tutor') && ! $user->hasRole('kepala-pkbm') && ! $user->hasRole('operator')) {
            abort_if($schedule->penugasanMengajar->tutor_id !== $user->tutor?->id, 403);
        }

        if ($user->hasRole('peserta-didik')) {
            abort_if($schedule->penugasanMengajar->classroom_id !== $user->student?->rombel_id, 403);
        }

        if ($user->hasRole('wali-kelas') && ! $user->hasRole('kepala-pkbm') && ! $user->hasRole('operator')) {
            $classroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
            abort_if(! $classroomIds->contains($schedule->penugasanMengajar->classroom_id), 403);
        }
    }
}
