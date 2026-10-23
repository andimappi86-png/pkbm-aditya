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

        $isStudent = $user->hasRole('peserta-didik')
            && ! $user->hasRole('operator')
            && ! $user->hasRole('kepala-pkbm');

        $studentNotice = null;
        $classrooms = collect();

        if ($isStudent) {
            /*
             * Peserta didik selalu dibatasi ke rombelnya sendiri. Bila profil atau penempatan
             * belum ada, query harus gagal-tertutup (tanpa hasil) supaya jadwal rombel lain
             * tidak pernah ikut tampil.
             */
            $student = $user->student;

            $studentNotice = match (true) {
                ! $student => 'Akun Anda belum tertaut ke profil peserta didik. Hubungi operator agar akun ini dihubungkan dengan data siswa yang benar.',
                ! $student->rombel_id => 'Anda belum ditempatkan pada rombel atau kelas aktif. Hubungi operator untuk melakukan penempatan kelas.',
                default => null,
            };

            $penugasanIds = $student?->rombel_id
                ? PenugasanMengajar::where('classroom_id', $student->rombel_id)
                    ->where('status_aktif', true)
                    ->pluck('id')
                : collect();

            $query->whereIn('penugasan_mengajar_id', $penugasanIds)
                ->where('status_aktif', true);
        } else {
            // Filter berdasarkan Rombel hanya untuk pengelola jadwal
            if ($request->filled('classroom_id')) {
                $query->whereHas('penugasanMengajar', function ($q) use ($request) {
                    $q->where('classroom_id', $request->classroom_id);
                });
            }

            $classrooms = Classroom::where('status_aktif', true)->get();
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

        // Jika user adalah Peserta Didik, hanya tampilkan jadwal rombelnya (lihat blok di atas)

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

        return view('schedules.index', compact('schedules', 'classrooms', 'isStudent', 'studentNotice'));
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
    public function show(Schedule $schedule): View|RedirectResponse
    {
        Gate::authorize('schedules.view');

        if ($redirect = $this->redirectWhenStudentProfileIncomplete()) {
            return $redirect;
        }

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
     * Arahkan peserta didik yang profil/penempatannya belum lengkap ke daftar jadwal
     * dengan pemberitahuan yang jelas, bukan pesan penolakan akses yang menyesatkan.
     */
    private function redirectWhenStudentProfileIncomplete(): ?RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasRole('peserta-didik') || $user->hasRole('operator') || $user->hasRole('kepala-pkbm')) {
            return null;
        }

        $student = $user->student;

        if (! $student) {
            return redirect()
                ->route('jadwal.index')
                ->with('error', 'Akun Anda belum tertaut ke profil peserta didik. Hubungi operator agar akun ini dihubungkan dengan data siswa yang benar.');
        }

        if (! $student->rombel_id) {
            return redirect()
                ->route('jadwal.index')
                ->with('error', 'Anda belum ditempatkan pada rombel atau kelas aktif. Hubungi operator untuk melakukan penempatan kelas.');
        }

        return null;
    }

    /**
     * Authorize access to specific schedule based on user role
     */
    private function authorizeScheduleAccess(Schedule $schedule): void
    {
        $user = auth()->user();
        $classroomId = $schedule->penugasanMengajar?->classroom_id;

        if ($user->hasRole('tutor') && ! $user->hasRole('kepala-pkbm') && ! $user->hasRole('operator')) {
            abort_if($schedule->penugasanMengajar?->tutor_id !== $user->tutor?->id, 403);
        }

        if ($user->hasRole('peserta-didik')) {
            abort_if($classroomId === null || $classroomId !== $user->student?->rombel_id, 403);
        }

        if ($user->hasRole('wali-kelas') && ! $user->hasRole('kepala-pkbm') && ! $user->hasRole('operator')) {
            $classroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
            abort_if($classroomId === null || ! $classroomIds->contains($classroomId), 403);
        }
    }
}
