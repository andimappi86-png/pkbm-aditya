<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('students.view');

        $query = Student::with(['program', 'academicYear', 'classroom']);
        $user = auth()->user();

        // Scope access based on roles
        if ($user->hasRole('peserta-didik')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('wali-kelas')) {
            $query->whereHas('classroom', function ($q) use ($user) {
                $q->where('wali_kelas_id', $user->id);
            });
        }
        // Assuming Tutor logic is complex or not fully implemented, we allow them to see all for now or scope if classroom subjects are added later.

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        $students = $query->latest()->paginate(10)->withQueryString();

        $programs = Program::where('status_aktif', true)->get();
        $academicYears = AcademicYear::where('status_aktif', true)->get();

        return view('students.index', compact('students', 'programs', 'academicYears'));
    }

    public function create(): View
    {
        Gate::authorize('students.create');

        $programs = Program::where('status_aktif', true)->get();
        $academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->get();
        $classrooms = Classroom::where('status_aktif', true)->get();

        return view('students.create', compact('programs', 'academicYears', 'classrooms'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('students', 'public');
        }

        Student::create($data);

        return redirect()->route('peserta-didik.index')
            ->with('success', 'Peserta didik berhasil ditambahkan.');
    }

    public function show(Student $peserta_didik): View
    {
        $this->authorizeAccess($peserta_didik, 'students.view');

        $peserta_didik->load(['program', 'academicYear', 'classroom']);

        return view('students.show', compact('peserta_didik'));
    }

    public function edit(Student $peserta_didik): View
    {
        $this->authorizeAccess($peserta_didik, 'students.update');

        $programs = Program::where('status_aktif', true)->get();
        $academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->get();
        $classrooms = Classroom::where('status_aktif', true)->get();

        return view('students.edit', compact('peserta_didik', 'programs', 'academicYears', 'classrooms'));
    }

    public function update(UpdateStudentRequest $request, Student $peserta_didik): RedirectResponse
    {
        $this->authorizeAccess($peserta_didik, 'students.update');

        $data = $request->validated();

        if ($request->hasFile('foto')) {
            if ($peserta_didik->foto) {
                Storage::disk('public')->delete($peserta_didik->foto);
            }
            $data['foto'] = $request->file('foto')->store('students', 'public');
        }

        $peserta_didik->update($data);

        return redirect()->route('peserta-didik.index')
            ->with('success', 'Data peserta didik berhasil diperbarui.');
    }

    public function destroy(Student $peserta_didik): RedirectResponse
    {
        $this->authorizeAccess($peserta_didik, 'students.delete');

        if ($peserta_didik->foto) {
            Storage::disk('public')->delete($peserta_didik->foto);
        }

        $peserta_didik->delete();

        return redirect()->route('peserta-didik.index')
            ->with('success', 'Data peserta didik berhasil dihapus.');
    }

    private function authorizeAccess(Student $student, string $permission): void
    {
        // Must have the base permission
        Gate::authorize($permission);

        $user = auth()->user();

        if ($user->hasRole('peserta-didik')) {
            if ($student->user_id !== $user->id) {
                abort(403);
            }
        } elseif ($user->hasRole('wali-kelas')) {
            $studentClassroom = $student->classroom;
            if (! $studentClassroom || $studentClassroom->wali_kelas_id !== $user->id) {
                abort(403);
            }
        }
    }
}
