<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        Gate::authorize('attendance.view');

        $user = $request->user();
        $query = Attendance::with([
            'schedule.penugasanMengajar.tutor',
            'schedule.penugasanMengajar.subject',
            'schedule.penugasanMengajar.classroom',
            'student',
        ]);

        // Scoping berdasarkan role
        if ($user->hasRole('peserta-didik') && ! $user->hasRole('operator') && ! $user->hasRole('kepala-pkbm')) {
            // Wajib dibatasi ke siswa milik akun ini. Tanpa profil siswa, hasil harus kosong.
            $query->whereIn('student_id', Student::where('user_id', $user->id)->pluck('id'));
        } elseif ($user->hasRole('tutor') && $user->tutor) {
            $query->whereHas('schedule.penugasanMengajar', function ($q) use ($user) {
                $q->where('tutor_id', $user->tutor->id);
            });
        } elseif ($user->hasRole('wali-kelas')) {
            $classroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
            $query->whereHas('schedule.penugasanMengajar', function ($q) use ($classroomIds) {
                $q->whereIn('classroom_id', $classroomIds);
            });
        }

        // Filters
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('classroom_id')) {
            $query->whereHas('schedule.penugasanMengajar', function ($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        if ($request->filled('tutor_id')) {
            $query->whereHas('schedule.penugasanMengajar', function ($q) use ($request) {
                $q->where('tutor_id', $request->tutor_id);
            });
        }

        if ($request->filled('subject_id')) {
            $query->whereHas('schedule.penugasanMengajar', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->latest('tanggal')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $classrooms = Classroom::where('status_aktif', true)->get();
        $tutors = Tutor::all();
        $subjects = Subject::where('status_aktif', true)->get();

        return view('attendances.index', compact('attendances', 'classrooms', 'tutors', 'subjects'));
    }

    /**
     * Show the form for creating a new resource (input absensi per jadwal).
     */
    public function create(Request $request): View
    {
        Gate::authorize('attendance.manage');

        $user = $request->user();
        $schedulesQuery = Schedule::with(['penugasanMengajar.tutor', 'penugasanMengajar.subject', 'penugasanMengajar.classroom'])
            ->where('status_aktif', true);

        if ($user->hasRole('tutor') && $user->tutor) {
            $schedulesQuery->whereHas('penugasanMengajar', function ($q) use ($user) {
                $q->where('tutor_id', $user->tutor->id);
            });
        }

        $schedules = $schedulesQuery->get();

        $selectedSchedule = null;
        $students = collect();

        if ($request->filled('schedule_id')) {
            $selectedSchedule = Schedule::with('penugasanMengajar.classroom')->find($request->schedule_id);

            // Validasi kepemilikan jika tutor
            if ($user->hasRole('tutor') && $user->tutor && $selectedSchedule) {
                if ($selectedSchedule->penugasanMengajar->tutor_id !== $user->tutor->id) {
                    abort(403, 'Anda tidak berhak mengelola absensi jadwal ini.');
                }
            }

            if ($selectedSchedule && $selectedSchedule->penugasanMengajar->classroom_id) {
                $students = Student::where('rombel_id', $selectedSchedule->penugasanMengajar->classroom_id)
                    ->where('status', 'aktif')
                    ->orderBy('nama_lengkap')
                    ->get();
            }
        }

        return view('attendances.create', compact('schedules', 'selectedSchedule', 'students'));
    }

    /**
     * Store a newly created resource in storage (supports single or bulk submission).
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('attendance.manage');

        $user = $request->user();

        // Check if batch attendance submission
        if ($request->has('attendances') && is_array($request->attendances)) {
            $request->validate([
                'schedule_id' => ['required', 'exists:schedules,id'],
                'tanggal' => ['required', 'date'],
                'attendances' => ['required', 'array'],
                'attendances.*.student_id' => ['required', 'exists:students,id'],
                'attendances.*.status' => ['required', 'in:hadir,izin,sakit,alpa'],
                'attendances.*.keterangan' => ['nullable', 'string', 'max:255'],
            ]);

            $schedule = Schedule::with('penugasanMengajar')->findOrFail($request->schedule_id);

            // Cek otorisasi tutor
            if ($user->hasRole('tutor') && $user->tutor) {
                if ($schedule->penugasanMengajar->tutor_id !== $user->tutor->id) {
                    abort(403, 'Anda tidak berwenang mengelola absensi jadwal ini.');
                }
            }

            foreach ($request->attendances as $item) {
                // Pastikan siswa berasal dari rombel jadwal tersebut
                $student = Student::find($item['student_id']);
                if ($student && $student->rombel_id === $schedule->penugasanMengajar->classroom_id) {
                    Attendance::updateOrCreate(
                        [
                            'schedule_id' => $schedule->id,
                            'student_id' => $student->id,
                            'tanggal' => $request->tanggal,
                        ],
                        [
                            'status' => $item['status'],
                            'keterangan' => $item['keterangan'] ?? null,
                        ]
                    );
                }
            }

            return redirect()
                ->route('absensi.index')
                ->with('success', 'Absensi berhasil disimpan.');
        }

        // Single attendance creation
        $validated = $request->validate([
            'schedule_id' => ['required', 'exists:schedules,id'],
            'student_id' => ['required', 'exists:students,id'],
            'tanggal' => ['required', 'date'],
            'status' => ['required', 'in:hadir,izin,sakit,alpa'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $schedule = Schedule::with('penugasanMengajar')->findOrFail($validated['schedule_id']);
        $student = Student::findOrFail($validated['student_id']);

        if ($student->rombel_id !== $schedule->penugasanMengajar->classroom_id) {
            return back()->withErrors(['student_id' => 'Peserta didik tidak terdaftar di rombel jadwal ini.'])->withInput();
        }

        if ($user->hasRole('tutor') && $user->tutor) {
            if ($schedule->penugasanMengajar->tutor_id !== $user->tutor->id) {
                abort(403, 'Anda tidak berwenang mengelola absensi jadwal ini.');
            }
        }

        // Check duplicate
        $exists = Attendance::where('schedule_id', $schedule->id)
            ->where('student_id', $student->id)
            ->whereDate('tanggal', $validated['tanggal'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['tanggal' => 'Data absensi untuk siswa dan jadwal pada tanggal ini sudah ada.'])->withInput();
        }

        Attendance::create($validated);

        return redirect()
            ->route('absensi.index')
            ->with('success', 'Absensi berhasil dicatat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance): View
    {
        Gate::authorize('attendance.view');

        $user = auth()->user();

        // Scope access
        if ($user->hasRole('peserta-didik') && $user->student && $attendance->student_id !== $user->student->id) {
            abort(403, 'Anda tidak memiliki akses.');
        } elseif ($user->hasRole('wali-kelas')) {
            $classroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
            $attendanceClassroomId = $attendance->schedule->penugasanMengajar->classroom_id;
            if (! $classroomIds->contains($attendanceClassroomId)) {
                abort(403, 'Anda tidak memiliki akses ke data absensi ini.');
            }
        } elseif ($user->hasRole('tutor') && $user->tutor) {
            if ($attendance->schedule->penugasanMengajar->tutor_id !== $user->tutor->id) {
                abort(403, 'Anda tidak berhak melihat absensi ini.');
            }
        }

        $attendance->load([
            'schedule.penugasanMengajar.tutor',
            'schedule.penugasanMengajar.subject',
            'schedule.penugasanMengajar.classroom',
            'student',
        ]);

        return view('attendances.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance): View
    {
        Gate::authorize('attendance.manage');

        $user = auth()->user();

        if ($user->hasRole('tutor') && $user->tutor) {
            if ($attendance->schedule->penugasanMengajar->tutor_id !== $user->tutor->id) {
                abort(403, 'Anda tidak berhak mengedit absensi ini.');
            }
        }

        $attendance->load([
            'schedule.penugasanMengajar.tutor',
            'schedule.penugasanMengajar.subject',
            'schedule.penugasanMengajar.classroom',
            'student',
        ]);

        return view('attendances.edit', compact('attendance'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        Gate::authorize('attendance.manage');

        $user = $request->user();

        if ($user->hasRole('tutor') && $user->tutor) {
            if ($attendance->schedule->penugasanMengajar->tutor_id !== $user->tutor->id) {
                abort(403, 'Anda tidak berhak mengedit absensi ini.');
            }
        }

        $validated = $request->validated();

        $attendance->update($validated);

        return redirect()
            ->route('absensi.index')
            ->with('success', 'Absensi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance): RedirectResponse
    {
        Gate::authorize('attendance.manage');

        $user = auth()->user();

        if ($user->hasRole('tutor') && $user->tutor) {
            if ($attendance->schedule->penugasanMengajar->tutor_id !== $user->tutor->id) {
                abort(403, 'Anda tidak berhak menghapus absensi ini.');
            }
        }

        $attendance->delete();

        return redirect()
            ->route('absensi.index')
            ->with('success', 'Data absensi berhasil dihapus.');
    }
}
