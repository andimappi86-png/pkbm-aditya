<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\PenugasanMengajar;
use App\Models\Student;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('grades.view');

        $user = $request->user();
        $roles = $user->getRoleNames();

        $query = Grade::with(['penugasanMengajar.tutor.user', 'penugasanMengajar.subject', 'penugasanMengajar.classroom', 'penugasanMengajar.academicYear', 'penugasanMengajar.semester', 'student']);

        $filters = [
            'classroom_id' => $request->input('classroom_id'),
            'subject_id' => $request->input('subject_id'),
            'tutor_id' => $request->input('tutor_id'),
            'student_id' => $request->input('student_id'),
            'keyword' => $request->input('keyword'),
        ];

        if ($filters['classroom_id']) {
            $query->whereHas('penugasanMengajar', fn ($q) => $q->where('classroom_id', $filters['classroom_id']));
        }

        if ($filters['subject_id']) {
            $query->whereHas('penugasanMengajar', fn ($q) => $q->where('subject_id', $filters['subject_id']));
        }

        if ($filters['tutor_id']) {
            $query->whereHas('penugasanMengajar', fn ($q) => $q->where('tutor_id', $filters['tutor_id']));
        }

        if ($filters['student_id']) {
            $query->where('student_id', $filters['student_id']);
        }

        if ($filters['keyword']) {
            $keyword = $filters['keyword'];
            $query->whereHas('student', fn ($q) => $q->where('nama_lengkap', 'like', "%{$keyword}%"));
        }

        // Role-based filtering
        if ($roles->contains('peserta-didik')) {
            $student = $user->student;
            abort_if(! $student, 403);
            $query->where('student_id', $student->id);
        } elseif ($roles->contains('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            abort_if(! $tutor, 403);
            $query->whereHas('penugasanMengajar', fn ($q) => $q->where('tutor_id', $tutor->id));
        } elseif ($roles->contains('wali-kelas')) {
            $classroomIds = Classroom::where('wali_kelas_id', $user->id)->pluck('id');
            $query->whereHas('penugasanMengajar', fn ($q) => $q->whereIn('classroom_id', $classroomIds));
        }

        $grades = $query->latest()->paginate(15)->withQueryString();

        return view('grades.index', compact('grades', 'filters'));
    }

    public function create(Request $request)
    {
        Gate::authorize('grades.manage');

        $user = $request->user();
        $roles = $user->getRoleNames();

        $penugasanQuery = PenugasanMengajar::with(['tutor', 'subject', 'classroom'])
            ->where('status_aktif', true);

        if ($roles->contains('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            abort_if(! $tutor, 403);
            $penugasanQuery->where('tutor_id', $tutor->id);
        }

        $penugasans = $penugasanQuery->get();

        $selectedPenugasan = null;
        $students = collect();

        if ($request->filled('penugasan_mengajar_id')) {
            $selectedPenugasan = PenugasanMengajar::with(['classroom.students' => fn ($q) => $q->where('status', 'aktif')])
                ->findOrFail($request->input('penugasan_mengajar_id'));

            $this->authorizePenugasan($selectedPenugasan, $user);

            $students = $selectedPenugasan->classroom?->students ?? collect();
        }

        return view('grades.create', compact('penugasans', 'selectedPenugasan', 'students'));
    }

    public function store(StoreGradeRequest $request)
    {
        Gate::authorize('grades.manage');

        $validated = $request->validated();
        $user = $request->user();

        $penugasan = PenugasanMengajar::findOrFail($validated['penugasan_mengajar_id']);
        $this->authorizePenugasan($penugasan, $user);

        $this->validateStudentInClassroom($validated['student_id'], $penugasan->classroom_id);

        $existing = Grade::where('penugasan_mengajar_id', $penugasan->id)
            ->where('student_id', $validated['student_id'])
            ->first();

        if ($existing) {
            return back()->withErrors(['student_id' => 'Nilai untuk siswa ini pada mata pelajaran ini sudah ada.'])->withInput();
        }

        Grade::create([
            ...$validated,
            'nilai_akhir' => $validated['nilai_akhir'] ?? $this->hitungNilaiAkhir($validated),
        ]);

        return redirect()->route('nilai.index')->with('success', 'Nilai berhasil disimpan.');
    }

    public function show(Grade $grade)
    {
        Gate::authorize('grades.view');
        $this->authorizeGrade($grade, auth()->user());

        return view('grades.show', compact('grade'));
    }

    public function edit(Grade $grade)
    {
        Gate::authorize('grades.manage');
        $this->authorizeGrade($grade, auth()->user());

        return view('grades.edit', compact('grade'));
    }

    public function update(UpdateGradeRequest $request, Grade $grade)
    {
        Gate::authorize('grades.manage');
        $this->authorizeGrade($grade, $request->user());

        $validated = $request->validated();

        $grade->update([
            ...$validated,
            'nilai_akhir' => $validated['nilai_akhir'] ?? $this->hitungNilaiAkhir([...$grade->toArray(), ...$validated]),
        ]);

        return redirect()->route('nilai.index')->with('success', 'Nilai berhasil diperbarui.');
    }

    public function destroy(Grade $grade)
    {
        Gate::authorize('grades.manage');
        $this->authorizeGrade($grade, auth()->user());

        $grade->delete();

        return redirect()->route('nilai.index')->with('success', 'Nilai berhasil dihapus.');
    }

    private function authorizePenugasan(PenugasanMengajar $penugasan, $user): void
    {
        if ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            abort_if(! $tutor || $penugasan->tutor_id !== $tutor->id, 403);
        }
    }

    private function authorizeGrade(Grade $grade, $user): void
    {
        if ($user->hasRole('peserta-didik')) {
            $student = $user->student;
            abort_if(! $student || $grade->student_id !== $student->id, 403);
        } elseif ($user->hasRole('tutor')) {
            $tutor = Tutor::where('user_id', $user->id)->first();
            abort_if(! $tutor || $grade->penugasanMengajar->tutor_id !== $tutor->id, 403);
        } elseif ($user->hasRole('wali-kelas')) {
            $wali = Tutor::where('user_id', $user->id)->first();
            $classroomIds = Classroom::where('wali_kelas_id', $wali?->id)->pluck('id');
            abort_if(! $classroomIds->contains($grade->penugasanMengajar->classroom_id), 403);
        }
    }

    private function validateStudentInClassroom(int $studentId, ?int $classroomId): void
    {
        $student = Student::where('id', $studentId)->where('rombel_id', $classroomId)->first();
        abort_if(! $student, 422, 'Siswa tidak terdaftar dalam rombel mata pelajaran ini.');
    }

    private function hitungNilaiAkhir(array $data): ?float
    {
        $tugas = $data['nilai_tugas'] ?? null;
        $uts = $data['nilai_uts'] ?? null;
        $uas = $data['nilai_uas'] ?? null;

        if ($tugas === null && $uts === null && $uas === null) {
            return null;
        }

        $tugas = $tugas ?? 0;
        $uts = $uts ?? 0;
        $uas = $uas ?? 0;

        return round(($tugas * 0.3) + ($uts * 0.3) + ($uas * 0.4), 2);
    }
}
