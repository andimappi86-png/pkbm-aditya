<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use Carbon\Carbon;
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

        // Scoping Rombel berdasarkan role
        if ($user->hasRole('peserta-didik') && ! $user->hasRole('operator') && ! $user->hasRole('kepala-pkbm')) {
            $student = Student::where('user_id', $user->id)->first();
            $classroomId = $student?->rombel_id;
            $classrooms = Classroom::where('id', $classroomId)->where('status_aktif', true)->orderBy('nama_rombel')->get();
        } elseif ($user->hasRole('tutor') && $user->tutor && ! $user->hasAnyRole(['operator', 'kepala-pkbm', 'admin', 'wali-kelas', 'wali_kelas'])) {
            $tutorId = $user->tutor->id;
            $classrooms = Classroom::whereHas('penugasanMengajar', function ($q) use ($tutorId) {
                $q->where('tutor_id', $tutorId);
            })->where('status_aktif', true)->orderBy('nama_rombel')->get();
            if ($classrooms->isEmpty()) {
                $classrooms = Classroom::where('status_aktif', true)->orderBy('nama_rombel')->get();
            }
        } elseif ($user->hasAnyRole(['wali-kelas', 'wali_kelas', 'wali kelas'])) {
            $classrooms = Classroom::where('wali_kelas_id', $user->id)
                ->where('status_aktif', true)
                ->orderBy('nama_rombel')
                ->get();
            if ($classrooms->isEmpty()) {
                $classrooms = Classroom::where('status_aktif', true)->orderBy('nama_rombel')->get();
            }
            if ($classrooms->isEmpty()) {
                $classrooms = Classroom::orderBy('id')->get();
            }
        } else {
            $classrooms = Classroom::where('status_aktif', true)
                ->orderBy('nama_rombel')
                ->get();
            if ($classrooms->isEmpty()) {
                $classrooms = Classroom::orderBy('id')->get();
            }
        }

        // Resolusi Rombel Terpilih (Auto-Resolve sesuai hierarki)
        // 1. Cek parameter classroom_id atau rombel_id
        $resolvedClassroomId = $request->filled('classroom_id')
            ? (int) $request->classroom_id
            : ($request->filled('rombel_id') ? (int) $request->rombel_id : null);

        // 2. Jika kosong dan ada schedule_id, ambil dari jadwal tersebut
        if (! $resolvedClassroomId && $request->filled('schedule_id')) {
            $sched = Schedule::with('penugasanMengajar')->find($request->schedule_id);
            $resolvedClassroomId = $sched?->penugasanMengajar?->classroom_id;
        }

        // 3. Jika masih kosong, cari rombel yang diampu user saat ini
        if (! $resolvedClassroomId) {
            $ownClassroomId = Classroom::where('wali_kelas_id', $user->id)->where('status_aktif', true)->value('id')
                ?? Classroom::where('wali_kelas_id', $user->id)->value('id');
            if ($ownClassroomId) {
                $resolvedClassroomId = (int) $ownClassroomId;
            } elseif ($user->hasRole('tutor') && $user->tutor) {
                $tutorRombelId = Classroom::whereHas('penugasanMengajar', fn ($q) => $q->where('tutor_id', $user->tutor->id))->value('id');
                if ($tutorRombelId) {
                    $resolvedClassroomId = (int) $tutorRombelId;
                }
            }
        }

        // Cek session jika belum ada
        if (! $resolvedClassroomId && session('selected_classroom_id')) {
            $resolvedClassroomId = (int) session('selected_classroom_id');
        }

        // 4. Fallback Wajib untuk Wali Kelas / Operator / Admin: jika masih null, pilih rombel pertama yang ada
        if (! $resolvedClassroomId) {
            $firstFromList = $classrooms->first();
            if ($firstFromList) {
                $resolvedClassroomId = (int) $firstFromList->id;
            } elseif ($user->hasAnyRole(['admin', 'operator', 'kepala-pkbm', 'wali-kelas', 'wali_kelas', 'wali kelas'])) {
                $fallbackId = Classroom::where('status_aktif', true)->orderBy('id')->value('id')
                    ?? Classroom::orderBy('id')->value('id');
                if ($fallbackId) {
                    $resolvedClassroomId = (int) $fallbackId;
                }
            }
        }

        $selectedClassroomId = $resolvedClassroomId;
        /** @var Classroom|null $selectedClassroom */
        $selectedClassroom = $selectedClassroomId ? Classroom::find($selectedClassroomId) : null;
        if ($selectedClassroom && ! $classrooms->contains('id', $selectedClassroom->id)) {
            $classrooms->push($selectedClassroom);
        }

        // Scoping Mata Pelajaran
        $subjectsQuery = Subject::where('status_aktif', true);
        if ($user->hasRole('tutor') && $user->tutor) {
            $tutorId = $user->tutor->id;
            $assignedSubjectIds = PenugasanMengajar::where('tutor_id', $tutorId)
                ->where('status_aktif', true)
                ->when($selectedClassroomId, fn ($q) => $q->where('classroom_id', $selectedClassroomId))
                ->pluck('subject_id');
            if ($assignedSubjectIds->isNotEmpty()) {
                $subjectsQuery->whereIn('id', $assignedSubjectIds);
            }
        } elseif ($selectedClassroomId) {
            $assignedSubjectIds = PenugasanMengajar::where('classroom_id', $selectedClassroomId)
                ->where('status_aktif', true)
                ->pluck('subject_id');
            if ($assignedSubjectIds->isNotEmpty()) {
                $subjectsQuery->whereIn('id', $assignedSubjectIds);
            }
        }
        $subjects = $subjectsQuery->orderBy('nama')->get();
        if ($subjects->isEmpty()) {
            $subjects = Subject::where('status_aktif', true)->orderBy('nama')->get();
        }

        // Selected Subject
        $firstSubject = $subjects->first();
        $selectedSubjectId = $request->filled('subject_id')
            ? (int) $request->subject_id
            : (session('selected_subject_id') ? (int) session('selected_subject_id') : ($firstSubject ? $firstSubject->id : null));

        // Selected Tanggal & Bulan
        $selectedTanggal = $request->input('tanggal', session('selected_tanggal', now()->format('Y-m-d')));
        $selectedBulan = $request->input('bulan', Carbon::parse($selectedTanggal)->format('Y-m'));

        // Students in the selected classroom
        $students = collect();
        if ($selectedClassroomId) {
            $studentsQuery = Student::where('rombel_id', $selectedClassroomId)
                ->where('status', 'aktif')
                ->orderBy('nama_lengkap');

            if ($user->hasRole('peserta-didik') && ! $user->hasRole('operator') && ! $user->hasRole('kepala-pkbm')) {
                $studentsQuery->where('user_id', $user->id);
            }

            $students = $studentsQuery->get();
        }

        // Resolve Schedule
        $selectedSchedule = null;
        if ($selectedClassroomId && $selectedSubjectId) {
            $selectedSchedule = Schedule::whereHas('penugasanMengajar', function ($q) use ($selectedClassroomId, $selectedSubjectId, $user) {
                $q->where('classroom_id', $selectedClassroomId)
                    ->where('subject_id', $selectedSubjectId);
                if ($user->hasRole('tutor') && $user->tutor) {
                    $q->where('tutor_id', $user->tutor->id);
                }
            })->where('status_aktif', true)->first();

            // Auto-resolve or create schedule if user can manage attendance
            if (! $selectedSchedule && $user->can('attendance.manage')) {
                $penugasan = PenugasanMengajar::where('classroom_id', $selectedClassroomId)
                    ->where('subject_id', $selectedSubjectId)
                    ->first();

                if (! $penugasan) {
                    $tutorId = ($user->hasRole('tutor') && $user->tutor) ? $user->tutor->id : Tutor::first()?->id;
                    if ($tutorId) {
                        $penugasan = PenugasanMengajar::create([
                            'classroom_id' => $selectedClassroomId,
                            'subject_id' => $selectedSubjectId,
                            'tutor_id' => $tutorId,
                            'status_aktif' => true,
                        ]);
                    }
                }

                if ($penugasan) {
                    $dayName = Carbon::parse($selectedTanggal)->locale('id')->isoFormat('dddd');
                    $selectedSchedule = Schedule::create([
                        'penugasan_mengajar_id' => $penugasan->id,
                        'hari' => $dayName ?: 'Senin',
                        'jam_mulai' => '08:00',
                        'jam_selesai' => '10:00',
                        'ruangan' => 'Kelas',
                        'status_aktif' => true,
                    ]);
                }
            }
        }

        // Existing attendances for selected date
        $existingAttendances = collect();
        if ($selectedSchedule) {
            $existingAttendances = Attendance::where('schedule_id', $selectedSchedule->id)
                ->whereDate('tanggal', $selectedTanggal)
                ->get()
                ->keyBy('student_id');
        }

        // Initial summary counts
        $summary = [
            'total' => $students->count(),
            'hadir' => 0,
            'sakit' => 0,
            'izin' => 0,
            'alpa' => 0,
        ];

        foreach ($students as $student) {
            $existing = $existingAttendances->get($student->id);
            $st = ($existing instanceof Attendance) ? $existing->status : 'hadir';
            if ($st === 'sakit') {
                $summary['sakit']++;
            } elseif ($st === 'izin') {
                $summary['izin']++;
            } elseif ($st === 'alpa') {
                $summary['alpa']++;
            } else {
                $summary['hadir']++;
            }
        }

        // Matrix Data for Monthly Recap
        $matrixData = [
            'dates' => [],
            'students' => [],
        ];

        if ($selectedSchedule && $students->isNotEmpty()) {
            $carbonBulan = Carbon::parse($selectedBulan.'-01');
            $startMonth = $carbonBulan->copy()->startOfMonth()->toDateString();
            $endMonth = $carbonBulan->copy()->endOfMonth()->toDateString();

            $monthlyAttendances = Attendance::where('schedule_id', $selectedSchedule->id)
                ->whereBetween('tanggal', [$startMonth, $endMonth])
                ->orderBy('tanggal')
                ->get();

            $uniqueDates = $monthlyAttendances->pluck('tanggal')
                ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
                ->unique()
                ->values()
                ->all();

            $matrixData['dates'] = $uniqueDates;

            $attMap = [];
            foreach ($monthlyAttendances as $att) {
                $d = Carbon::parse($att->tanggal)->format('Y-m-d');
                $attMap[$att->student_id][$d] = $att->status;
            }

            foreach ($students as $student) {
                $records = [];
                $h = 0;
                $s = 0;
                $i = 0;
                $a = 0;

                foreach ($uniqueDates as $d) {
                    $st = $attMap[$student->id][$d] ?? '-';
                    $records[$d] = $st;
                    if ($st === 'hadir') {
                        $h++;
                    } elseif ($st === 'sakit') {
                        $s++;
                    } elseif ($st === 'izin') {
                        $i++;
                    } elseif ($st === 'alpa') {
                        $a++;
                    }
                }

                $totalCount = count($uniqueDates);
                $pct = $totalCount > 0 ? round(($h / $totalCount) * 100, 1) : 0;

                $matrixData['students'][] = [
                    'student' => $student,
                    'records' => $records,
                    'hadir' => $h,
                    'sakit' => $s,
                    'izin' => $i,
                    'alpa' => $a,
                    'total' => $totalCount,
                    'persentase' => $pct,
                ];
            }
        }

        /** @var Classroom|null $selectedClassroom */
        $selectedClassroom = $selectedClassroomId ? $classrooms->firstWhere('id', $selectedClassroomId) : null;
        if (! $selectedClassroom && $selectedClassroomId) {
            $selectedClassroom = Classroom::find($selectedClassroomId);
        }

        $isWaliKelasRole = $user->hasAnyRole(['wali-kelas', 'wali_kelas', 'wali kelas']);
        $isAdminOrOperator = $user->hasAnyRole(['admin', 'operator', 'kepala-pkbm']);
        $isAssignedWali = $selectedClassroom && (int) $selectedClassroom->wali_kelas_id === (int) $user->id;

        $canManageClassroom = $selectedClassroom !== null && (
            $isAdminOrOperator ||
            ($isWaliKelasRole && $isAssignedWali) ||
            $isAssignedWali
        );

        $attendances = Attendance::where('id', 0)->paginate(15);

        return view('attendances.index', compact(
            'classrooms',
            'subjects',
            'selectedClassroomId',
            'selectedClassroom',
            'canManageClassroom',
            'selectedSubjectId',
            'selectedTanggal',
            'selectedBulan',
            'selectedSchedule',
            'students',
            'existingAttendances',
            'summary',
            'matrixData',
            'attendances'
        ));
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
                'schedule_id' => ['nullable', 'exists:schedules,id'],
                'classroom_id' => ['nullable', 'exists:classrooms,id'],
                'subject_id' => ['nullable', 'exists:subjects,id'],
                'tanggal' => ['required', 'date'],
                'attendances' => ['required', 'array'],
                'attendances.*.student_id' => ['required', 'exists:students,id'],
                'attendances.*.status' => ['required', 'in:hadir,izin,sakit,alpa,alfa'],
                'attendances.*.keterangan' => ['nullable', 'string', 'max:255'],
            ]);

            $schedule = null;
            if ($request->filled('schedule_id')) {
                $schedule = Schedule::with('penugasanMengajar')->findOrFail($request->schedule_id);
            } elseif ($request->filled('classroom_id') && $request->filled('subject_id')) {
                $schedule = Schedule::whereHas('penugasanMengajar', function ($q) use ($request) {
                    $q->where('classroom_id', $request->classroom_id)
                        ->where('subject_id', $request->subject_id);
                })->where('status_aktif', true)->first();
            }

            if (! $schedule) {
                return back()->withErrors(['schedule_id' => 'Jadwal presensi kelas tidak ditemukan.'])->withInput();
            }

            // Cek otorisasi tutor
            if ($user->hasRole('tutor') && $user->tutor) {
                if ($schedule->penugasanMengajar && $schedule->penugasanMengajar->tutor_id !== $user->tutor->id) {
                    abort(403, 'Anda tidak berwenang mengelola absensi jadwal ini.');
                }
            }

            foreach ($request->attendances as $item) {
                // Pastikan siswa berasal dari rombel jadwal tersebut
                $student = Student::find($item['student_id']);
                if ($student && (! $schedule->penugasanMengajar || $student->rombel_id === $schedule->penugasanMengajar->classroom_id)) {
                    $status = ($item['status'] === 'alfa') ? 'alpa' : $item['status'];
                    Attendance::updateOrCreate(
                        [
                            'schedule_id' => $schedule->id,
                            'student_id' => $student->id,
                            'tanggal' => $request->tanggal,
                        ],
                        [
                            'status' => $status,
                            'keterangan' => $item['keterangan'] ?? null,
                        ]
                    );
                }
            }

            if ($schedule->penugasanMengajar) {
                session()->flash('selected_classroom_id', $schedule->penugasanMengajar->classroom_id);
                session()->flash('selected_subject_id', $schedule->penugasanMengajar->subject_id);
            }
            session()->flash('selected_tanggal', $request->tanggal);

            return redirect()
                ->route('absensi.index')
                ->with('success', 'Buku presensi kelas berhasil disimpan.');
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
