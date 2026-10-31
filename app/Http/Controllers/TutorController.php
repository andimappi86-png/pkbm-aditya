<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTutorRequest;
use App\Http\Requests\UpdateTutorRequest;
use App\Models\PenugasanMengajar;
use App\Models\Schedule;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TutorController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('tutors.view');

        $query = Tutor::query();

        $user = auth()->user();
        if ($user->hasRole('peserta-didik') || $user->hasRole('wali-kelas')) {
            // They shouldn't see tutors list generally, but keep permission check
            // If they have the permission, they can see all
        }

        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nomor_induk', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $status = $request->get('status_tutor');
        if ($status) {
            $query->where('status_tutor', $status);
        }

        $tutors = $query->latest()->paginate(10)
            ->withQueryString();

        $totalTutors = Tutor::count();
        $activeTutors = Tutor::where('status_tutor', 'aktif')->count();
        $inactiveTutors = Tutor::where('status_tutor', 'tidak_aktif')->count();

        return view('dashboards.tutor.index', [
            'tutors' => $tutors,
            'search' => $search,
            'status' => $status,
            'statusOptions' => ['aktif', 'tidak_aktif'],
            'totalTutors' => $totalTutors,
            'activeTutors' => $activeTutors,
            'inactiveTutors' => $inactiveTutors,
        ]);
    }

    public function create()
    {
        Gate::authorize('tutors.create');

        return view('dashboards.tutor.create');
    }

    public function store(StoreTutorRequest $request)
    {
        Gate::authorize('tutors.create');
        $validated = $request->validated();
        $tutor = Tutor::create($validated);

        return redirect()
            ->route('tutors.index')
            ->with('success', 'Tutor berhasil ditambahkan.');
    }

    public function show(Tutor $tutor)
    {
        Gate::authorize('tutors.view');

        $tutor->load([
            'subjects',
            'classrooms.program',
            'classrooms.academicYear',
        ]);

        $penugasan = PenugasanMengajar::with(['subject', 'classroom', 'academicYear', 'semester'])
            ->where('tutor_id', $tutor->id)
            ->where('status_aktif', true)
            ->get();

        $penugasanIds = $penugasan->pluck('id');
        $schedules = Schedule::with(['penugasanMengajar.subject', 'penugasanMengajar.classroom'])
            ->whereIn('penugasan_mengajar_id', $penugasanIds)
            ->where('status_aktif', true)
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 WHEN 'Minggu' THEN 7 ELSE 8 END")
            ->orderBy('jam_mulai', 'asc')
            ->get();

        return view('dashboards.tutor.show', [
            'tutor' => $tutor,
            'penugasan' => $penugasan,
            'schedules' => $schedules,
            'stats' => [
                'total_rombel' => $tutor->classrooms->count(),
                'total_mapel' => $tutor->subjects->count(),
                'total_penugasan' => $penugasan->count(),
            ],
        ]);
    }

    public function edit(Tutor $tutor)
    {
        Gate::authorize('tutors.update');

        return view('dashboards.tutor.edit', [
            'tutor' => $tutor,
        ]);
    }

    public function update(UpdateTutorRequest $request, Tutor $tutor)
    {
        Gate::authorize('tutors.update');
        $validated = $request->validated();
        $tutor->update($validated);

        return redirect()
            ->route('tutors.index')
            ->with('success', 'Tutor berhasil diperbarui.');
    }

    public function destroy(Tutor $tutor)
    {
        Gate::authorize('tutors.delete');
        $tutor->delete();

        return redirect()
            ->route('tutors.index')
            ->with('success', 'Tutor berhasil dihapus.');
    }
}
