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
use App\Models\User;
use App\Policies\StudentPolicy;
use App\Services\StudentRegistrationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function __construct(private readonly StudentRegistrationService $studentRegistration) {}

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

        $callback = function () use ($headers, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, $sampleData);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=template_import_peserta_didik.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
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

        $query = Student::query();
        $user = auth()->user();

        // Scope access based on roles
        if ($user->hasRole('peserta-didik')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('wali-kelas')) {
            $query->whereHas('classroom', function ($q) use ($user) {
                $q->where('wali_kelas_id', $user->id);
            });
        } elseif ($user->hasRole('tutor')) {
            $tutorId = $user->tutor?->id;

            $query->whereHas('classroom.penugasanMengajar', function ($q) use ($tutorId) {
                $q->where('tutor_id', $tutorId ?? 0);
            });
        }

        $statsQuery = clone $query;

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'aktif' => (clone $statsQuery)->where('status', 'aktif')->count(),
            'nonaktif' => (clone $statsQuery)->whereIn('status', ['nonaktif', 'lulus', 'keluar', 'pindah'])->count(),
        ];

        $query->with(['program', 'academicYear', 'classroom']);

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
        $classrooms = $this->manageableClassrooms($user);

        return view('students.index', compact('students', 'programs', 'academicYears', 'stats', 'classrooms'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('students.create');

        $programs = Program::where('status_aktif', true)->get();
        $academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->get();
        $classrooms = $this->manageableClassrooms($request->user());

        // Rombel awal saat pengguna memulai dari detail rombel. Nilai ini hanya
        // prefill; kepemilikan rombel tetap divalidasi ulang di server saat store.
        $presetRombelId = (int) $request->integer('rombel_id');
        if ($presetRombelId === 0 || ! $classrooms->contains('id', $presetRombelId)) {
            $presetRombelId = null;
        }

        return view('students.create', compact('programs', 'academicYears', 'classrooms', 'presetRombelId'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Pertahanan berlapis: FormRequest sudah memvalidasi, tetapi kepemilikan
        // rombel tujuan diperiksa ulang di sini sebelum data ditulis.
        $this->ensureCanManageClassroom($request->user(), $validated['rombel_id'] ?? null);

        $student = DB::transaction(fn (): Student => $this->studentRegistration->create(
            $validated,
            $request->file('foto'),
        ));

        // Operator yang masuk dari alur pembuatan akun dikembalikan ke form akun
        // dengan profil siswa baru sudah terpilih.
        if ($request->string('return_to')->toString() === 'akun' && $request->user()->can('users.manage')) {
            return redirect()
                ->route('users.create', ['student' => $student->id])
                ->with('success', "Data siswa {$student->nama_lengkap} tersimpan. Lanjutkan membuat akun login untuk siswa tersebut.");
        }

        return redirect()->route('peserta-didik.index')
            ->with('success', 'Peserta didik berhasil ditambahkan.');
    }

    public function show(Student $peserta_didik): View
    {
        $this->authorizeAccess($peserta_didik, 'view');

        $peserta_didik->load(['program', 'academicYear', 'classroom']);

        return view('students.show', compact('peserta_didik'));
    }

    public function edit(Request $request, Student $peserta_didik): View
    {
        $this->authorizeAccess($peserta_didik, 'update');

        $programs = Program::where('status_aktif', true)->get();
        $academicYears = AcademicYear::where('status_aktif', true)->latest('tanggal_mulai')->get();
        $classrooms = $this->manageableClassrooms($request->user());

        return view('students.edit', compact('peserta_didik', 'programs', 'academicYears', 'classrooms'));
    }

    public function update(UpdateStudentRequest $request, Student $peserta_didik): RedirectResponse
    {
        $this->authorizeAccess($peserta_didik, 'update');

        $validated = $request->validated();

        // Pertahanan berlapis: verifikasi rombel tujuan (jika diubah) sebelum menyimpan.
        $this->ensureCanManageClassroom($request->user(), $validated['rombel_id'] ?? null);

        DB::transaction(function () use (&$validated, $request, $peserta_didik) {
            if ($request->hasFile('foto')) {
                if ($peserta_didik->foto) {
                    Storage::disk('public')->delete($peserta_didik->foto);
                }
                $validated['foto'] = $request->file('foto')->store('students', 'public');
            }

            // Extract parent details only if provided
            if ($request->has('parent_details')) {
                $parentDetails = $validated['parent_details'] ?? [];
                unset($validated['parent_details']);

                // Sync legacy name fields from parent_details if empty
                if (empty($peserta_didik->nama_ayah) && ($ayah = collect($parentDetails)->firstWhere('relationship_type', 'ayah'))) {
                    $validated['nama_ayah'] = $ayah['name'] ?? null;
                }
                if (empty($peserta_didik->nama_ibu) && ($ibu = collect($parentDetails)->firstWhere('relationship_type', 'ibu'))) {
                    $validated['nama_ibu'] = $ibu['name'] ?? null;
                }

                foreach ($parentDetails as $detail) {
                    if (empty($detail['relationship_type'])) {
                        continue;
                    }
                    $peserta_didik->parentDetails()->updateOrCreate(
                        ['relationship_type' => $detail['relationship_type']],
                        $detail
                    );
                }
            } else {
                unset($validated['parent_details']);
            }

            $peserta_didik->update($validated);
        });

        return redirect()->route('peserta-didik.index')
            ->with('success', 'Data peserta didik berhasil diperbarui.');
    }

    public function destroy(Student $peserta_didik): RedirectResponse
    {
        $this->authorizeAccess($peserta_didik, 'delete');

        if ($peserta_didik->foto) {
            Storage::disk('public')->delete($peserta_didik->foto);
        }

        $peserta_didik->delete();

        return redirect()->route('peserta-didik.index')
            ->with('success', 'Data peserta didik berhasil dihapus.');
    }

    public function bulkAssignForm(Request $request): View
    {
        Gate::authorize('students.update');

        $query = Student::with(['program', 'academicYear', 'classroom'])
            ->where('status', 'aktif');

        // By default show unassigned students
        if (! $request->has('show_all') || $request->show_all != '1') {
            $query->whereNull('rombel_id');
        }

        // Filters
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }
        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('nama_lengkap')->paginate(50)->withQueryString();

        $programs = Program::where('status_aktif', true)->get();
        $academicYears = AcademicYear::where('status_aktif', true)->get();
        $classrooms = $this->manageableClassrooms($request->user());

        $unassignedCount = Student::where('status', 'aktif')->whereNull('rombel_id')->count();

        return view('students.bulk_assign', compact('students', 'programs', 'academicYears', 'classrooms', 'unassignedCount'));
    }

    public function bulkAssignProcess(Request $request): RedirectResponse
    {
        Gate::authorize('students.update');

        $request->validate([
            'student_ids' => ['required', 'array', 'min:1', 'max:200'],
            'student_ids.*' => ['required', 'exists:students,id'],
            'rombel_id' => ['required', 'exists:classrooms,id'],
        ], [
            'student_ids.required' => 'Pilih setidaknya satu peserta didik untuk ditempatkan.',
            'rombel_id.required' => 'Pilih rombel tujuan.',
            'rombel_id.exists' => 'Rombel tujuan tidak valid.',
        ]);

        $classroom = Classroom::with(['program', 'academicYear'])->findOrFail($request->rombel_id);

        // Verifikasi kepemilikan rombel tujuan secara server-side.
        $this->ensureCanManageClassroom($request->user(), $classroom->id);

        $successCount = 0;
        $skippedCount = 0;
        $warnings = [];

        DB::transaction(function () use ($request, $classroom, &$successCount, &$skippedCount, &$warnings) {
            $students = Student::whereIn('id', $request->student_ids)->get();

            foreach ($students as $student) {
                // Rule 1: Only active students
                if ($student->status !== 'aktif') {
                    $skippedCount++;
                    $warnings[] = "Siswa {$student->nama_lengkap} dilewati karena status tidak aktif.";

                    continue;
                }

                // Rule 2: Only unassigned students
                if ($student->rombel_id !== null) {
                    $skippedCount++;
                    $warnings[] = "Siswa {$student->nama_lengkap} dilewati karena sudah terdaftar di rombel lain.";

                    continue;
                }

                // Rule 3: Program match validation
                if ($student->program_id && $classroom->program_id && $student->program_id != $classroom->program_id) {
                    $skippedCount++;
                    $warnings[] = "Siswa {$student->nama_lengkap} dilewati karena program berbeda dengan rombel tujuan.";

                    continue;
                }

                // Rule 4: Academic Year match validation
                if ($student->tahun_ajaran_id && $classroom->tahun_ajaran_id && $student->tahun_ajaran_id != $classroom->tahun_ajaran_id) {
                    $skippedCount++;
                    $warnings[] = "Siswa {$student->nama_lengkap} dilewati karena tahun ajaran berbeda dengan rombel tujuan.";

                    continue;
                }

                $student->rombel_id = $classroom->id;
                $student->save();
                $successCount++;
            }
        });

        if ($successCount > 0) {
            $message = "Berhasil menempatkan {$successCount} siswa ke rombel {$classroom->nama_rombel}.";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} siswa dilewati.";
            }
            if (! empty($warnings)) {
                return redirect()->route('peserta-didik.bulk-assign')
                    ->with('success', $message)
                    ->with('warning', implode('<br>', array_slice($warnings, 0, 5)).(count($warnings) > 5 ? '<br>...dan '.(count($warnings) - 5).' lainnya.' : ''));
            }

            return redirect()->route('peserta-didik.bulk-assign')->with('success', $message);
        }

        return redirect()->route('peserta-didik.bulk-assign')
            ->with('error', "Gagal menempatkan siswa. {$skippedCount} siswa dilewati karena tidak memenuhi kriteria.")
            ->with('warning', implode('<br>', array_slice($warnings, 0, 5)));
    }

    private function authorizeAccess(Student $student, string $ability): void
    {
        Gate::authorize($ability, $student);
    }

    /**
     * Daftar rombel yang boleh dikelola user pada formulir siswa/bulk assign.
     *
     * Role administratif (operator, kepala-pkbm, dll.) melihat semua rombel aktif;
     * wali kelas hanya rombel miliknya sendiri sehingga students.create dan
     * students.update tidak membuka seluruh data rombel.
     *
     * @return Collection<int, Classroom>
     */
    private function manageableClassrooms(User $user)
    {
        $query = Classroom::with(['program', 'academicYear'])
            ->where('status_aktif', true)
            ->orderBy('nama_rombel');

        if (app(StudentPolicy::class)->isScopedToOwnClassrooms($user)) {
            $query->where('wali_kelas_id', $user->id);
        }

        return $query->get();
    }

    /**
     * Validasi server kepemilikan rombel tujuan sebelum penulisan data.
     *
     * Nilai kosong divalidasi sesuai kewenangan: wali kelas wajib menempatkan
     * siswa pada rombel miliknya, role administratif boleh tanpa rombel.
     */
    private function ensureCanManageClassroom(?User $user, mixed $classroomId): void
    {
        if ($user === null) {
            abort(403);
        }

        $policy = app(StudentPolicy::class);

        if (blank($classroomId)) {
            if ($policy->isScopedToOwnClassrooms($user)) {
                abort(403, 'Pilih rombel yang menjadi tanggung jawab Anda.');
            }

            return;
        }

        $classroom = Classroom::query()->find($classroomId);

        if ($classroom === null || ! $policy->manageClassroom($user, $classroom)) {
            abort(403, 'Anda tidak memiliki akses ke rombel tersebut.');
        }
    }
}
