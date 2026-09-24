<?php

namespace App\Http\Controllers;

use App\Exports\StudentTemplateExport;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Imports\StudentsImport;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function template()
    {
        Gate::authorize('students.create');

        return Excel::download(new StudentTemplateExport, 'template_import_peserta_didik.xlsx');
    }

    public function templateCsv()
    {
        Gate::authorize('students.create');

        $headers = [
            'nis',
            'nik',
            'nama_lengkap',
            'jenis_kelamin',
            'alamat',
            'program_id',
            'tahun_ajaran_id',
            'status',
            'nisn',
            'nama_panggilan',
            'tempat_lahir',
            'tanggal_lahir',
            'agama',
            'status_perkawinan',
            'rt',
            'rw',
            'kelurahan',
            'kecamatan',
            'kabupaten',
            'provinsi',
            'no_hp',
            'email',
            'rombel_id',
            'nama_ayah',
            'nama_ibu',
            'nama_wali',
            'no_hp_wali',
            'alamat_wali',
            'catatan',
        ];

        $sampleData = [
            'S001',
            '1234567890123456',
            'Budi Santoso',
            'Laki-laki',
            'Jl. Raya No. 1, Mamuju',
            '1',
            '1',
            'aktif',
            '1234567890',
            'Budi',
            'Mamuju',
            '2010-01-15',
            'Islam',
            'Belum Menikah',
            '001',
            '002',
            'Kelurahan Baru',
            'Kecamatan Tengah',
            'Kabupaten Mamuju',
            'Sulawesi Barat',
            '081234567890',
            'budi@example.com',
            '1',
            'Ahmad',
            'Siti',
            '',
            '',
            '',
            'Peserta didik baru',
        ];

        $callback = function() use ($headers, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, $sampleData);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=template_import_peserta_didik.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
    }

    public function importForm(): View
    {
        Gate::authorize('students.create');

        return view('students.import');
    }

    public function importPreview(Request $request)
    {
        Gate::authorize('students.create');

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:5120'], // 5MB max
        ]);

        $file = $request->file('file');
        $path = $file->storeAs('temp', 'import_students_'.auth()->id().'.'.$file->getClientOriginalExtension());

        $import = new StudentsImport(true); // previewMode = true
        Excel::import($import, $path);

        $results = $import->getResults();

        // Check for dataset mix
        $hasTutor = false;
        if (isset($results['preview_data'])) {
            foreach ($results['preview_data'] as $data) {
                if (isset($data['tipe']) && strtolower($data['tipe']) === 'tutor') {
                    $hasTutor = true;
                    break;
                }
            }
        }

        if ($hasTutor) {
             return redirect()->route('peserta-didik.import')
                 ->with('error', 'File mengandung data Tutor. Halaman ini hanya menerima Peserta Didik.');
        }

        return view('students.import_preview', compact('results', 'path'));
    }

    public function importProcess(Request $request)
    {
        Gate::authorize('students.create');

        $request->validate([
            'path' => ['required', 'string'],
        ]);

        $path = $request->path;
        if (! Storage::exists($path)) {
            return redirect()->route('peserta-didik.import')->with('error', 'File import tidak ditemukan atau sudah kadaluarsa.');
        }

        $import = new StudentsImport(false); // previewMode = false

        DB::beginTransaction();
        try {
            Excel::import($import, $path);

            $results = $import->getResults();
            if ($results['failed'] > 0 || $results['duplicates'] > 0) {
                // If we want strict all-or-nothing: DB::rollBack();
                // But the instruction says: "jangan sampai satu baris invalid menyebabkan seluruh proses menjadi sulit dilacak... Berikan hasil Berhasil: 295 Gagal: 3"
                // This means partial success is allowed! We commit the successful ones.
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::delete($path);

            return redirect()->route('peserta-didik.import')->with('error', 'Terjadi kesalahan sistem saat import: '.$e->getMessage());
        }

        Storage::delete($path);

        return view('students.import_result', compact('results'));
    }

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
        } elseif ($user->hasRole('tutor')) {
            $query->whereHas('classroom.penugasanMengajar', function ($q) use ($user) {
                if ($user->tutor) {
                    $q->where('tutor_id', $user->tutor->id);
                }
            });
        }

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
        } elseif ($user->hasRole('tutor')) {
            $studentClassroom = $student->classroom;
            $hasAccess = $studentClassroom && $studentClassroom->penugasanMengajar()->where('tutor_id', $user->tutor?->id)->exists();
            if (! $hasAccess) {
                abort(403);
            }
        }
    }
}
