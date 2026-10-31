<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassroomMemberRequest;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use App\Policies\StudentPolicy;
use App\Services\StudentRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassroomMemberController extends Controller
{
    public function __construct(
        private readonly StudentRegistrationService $studentRegistration,
        private readonly StudentPolicy $studentPolicy
    ) {}

    /**
     * Cari / muat siswa aktif yang belum memiliki kelas.
     */
    public function unassignedStudents(Request $request, Classroom $classroom): JsonResponse
    {
        $this->authorizeManage($request->user(), $classroom);

        $search = trim((string) $request->query('q', ''));

        $query = Student::query()
            ->whereNull('rombel_id')
            ->where('status', 'aktif')
            ->select(['id', 'nis', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'program_id', 'tahun_ajaran_id'])
            ->with(['program:id,nama', 'academicYear:id,nama']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('nama_lengkap')->limit(50)->get();

        $studentList = [];
        foreach ($students as $s) {
            $studentList[] = [
                'id' => $s->id,
                'nis' => $s->nis ?? '-',
                'nisn' => $s->nisn ?? '-',
                'nama_lengkap' => $s->nama_lengkap,
                'jenis_kelamin' => $s->jenis_kelamin,
                'program' => $s->program instanceof Program ? $s->program->nama : '-',
                'is_program_match' => ! $s->program_id || $s->program_id == $classroom->program_id,
            ];
        }

        return response()->json([
            'students' => $studentList,
        ]);
    }

    /**
     * Metode 1: Pilih siswa terdaftar yang belum memiliki kelas.
     */
    public function assignExisting(Request $request, Classroom $classroom): RedirectResponse|JsonResponse
    {
        $this->authorizeManage($request->user(), $classroom);

        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'exists:students,id'],
        ], [
            'student_ids.required' => 'Pilih setidaknya satu siswa.',
            'student_ids.min' => 'Pilih setidaknya satu siswa.',
        ]);

        $successCount = 0;
        $errors = [];

        DB::transaction(function () use ($validated, $classroom, &$successCount, &$errors) {
            $students = Student::whereIn('id', $validated['student_ids'])->lockForUpdate()->get();

            foreach ($students as $student) {
                if ($student->status !== 'aktif') {
                    $errors[] = "Siswa {$student->nama_lengkap} tidak aktif.";

                    continue;
                }

                if ($student->rombel_id !== null && (int) $student->rombel_id !== (int) $classroom->id) {
                    $errors[] = "Siswa {$student->nama_lengkap} sudah terdaftar di kelas lain.";

                    continue;
                }

                // Tetapkan rombel dan selaraskan program & tahun ajaran bila kosong
                $student->rombel_id = $classroom->id;
                if (! $student->program_id && $classroom->program_id) {
                    $student->program_id = $classroom->program_id;
                }
                if (! $student->tahun_ajaran_id && $classroom->tahun_ajaran_id) {
                    $student->tahun_ajaran_id = $classroom->tahun_ajaran_id;
                }
                $student->save();
                $successCount++;
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $successCount > 0,
                'message' => "Berhasil menambahkan {$successCount} siswa ke kelas {$classroom->nama_rombel}.",
                'errors' => $errors,
                'assigned_count' => $successCount,
            ]);
        }

        if ($request->filled('redirect_to')) {
            $redirect = redirect($request->input('redirect_to'));
            if ($successCount > 0) {
                $redirect->with('success', "Berhasil menambahkan {$successCount} siswa ke kelas {$classroom->nama_rombel}.");
            }
            if (! empty($errors)) {
                $redirect->with('warning', implode('<br>', array_slice($errors, 0, 5)));
            }

            return $redirect;
        }

        $redirect = redirect()->route('rombel.show', $classroom);
        if ($successCount > 0) {
            $redirect->with('success', "Berhasil menambahkan {$successCount} siswa ke kelas {$classroom->nama_rombel}.");
        }
        if (! empty($errors)) {
            $redirect->with('warning', implode('<br>', array_slice($errors, 0, 5)));
        }

        return $redirect;
    }

    /**
     * Metode 2: Input cepat data siswa baru langsung ke kelas.
     */
    public function quickStore(StoreClassroomMemberRequest $request, ?Classroom $classroom = null): RedirectResponse|JsonResponse
    {
        if (! $classroom || ! $classroom->exists) {
            $classroom = Classroom::findOrFail($request->integer('rombel_id'));
        }

        $this->authorizeManage($request->user(), $classroom);

        $validated = $request->validated();

        $identitas = trim((string) ($validated['nisn'] ?? $validated['nomor_identitas'] ?? ''));
        if ($identitas === '') {
            $identitas = trim((string) ($request->input('nisn') ?? $request->input('nomor_identitas') ?? ''));
        }

        // 1. Cek duplikasi NIS di database
        if (Student::where('nis', $identitas)->orWhere('nisn', $identitas)->exists()) {
            $msg = "Nomor identitas / NIS '{$identitas}' sudah terdaftar untuk siswa lain.";
            if ($request->wantsJson()) {
                return response()->json(['message' => $msg, 'errors' => ['nomor_identitas' => [$msg], 'nisn' => [$msg]]], 422);
            }

            return back()->withErrors(['nomor_identitas' => $msg, 'nisn' => $msg])->withInput();
        }

        // 2. Cek duplikasi NIK jika diisi terpisah atau jika identitas 16 digit angka
        $nik = trim((string) ($validated['nik'] ?? $request->input('nik') ?? ''));
        if ($nik !== '') {
            $duplicateNik = Student::query()
                ->select(['id', 'nik'])
                ->get()
                ->contains(fn (Student $s) => (string) $s->nik === $nik);

            if ($duplicateNik) {
                $msg = "Nomor identitas (NIK) '{$nik}' sudah terdaftar untuk siswa lain.";
                if ($request->wantsJson()) {
                    return response()->json(['message' => $msg, 'errors' => ['nik' => [$msg]]], 422);
                }

                return back()->withErrors(['nik' => $msg])->withInput();
            }
        } elseif (strlen($identitas) === 16 && ctype_digit($identitas)) {
            $nik = $identitas;
            $duplicateNik = Student::query()
                ->select(['id', 'nik'])
                ->get()
                ->contains(fn (Student $s) => (string) $s->nik === $identitas);

            if ($duplicateNik) {
                $msg = 'Nomor identitas (NIK) ini sudah terdaftar untuk siswa lain.';
                if ($request->wantsJson()) {
                    return response()->json(['message' => $msg, 'errors' => ['nomor_identitas' => [$msg], 'nisn' => [$msg]]], 422);
                }

                return back()->withErrors(['nomor_identitas' => $msg, 'nisn' => $msg])->withInput();
            }
        }

        $rawJk = (string) ($validated['gender'] ?? $validated['jenis_kelamin'] ?? 'L');
        $jk = in_array(strtoupper($rawJk), ['L', 'LAKI-LAKI'], true) ? 'Laki-laki' : 'Perempuan';
        $statusMasuk = ! empty($validated['status_masuk']) ? $validated['status_masuk'] : 'Siswa Baru';
        $namaLengkap = trim((string) ($validated['name'] ?? $validated['nama_lengkap'] ?? ''));

        $student = DB::transaction(function () use ($validated, $classroom, $identitas, $nik, $jk, $statusMasuk, $namaLengkap) {
            $created = $this->studentRegistration->create([
                'nama_lengkap' => $namaLengkap,
                'nis' => $identitas,
                'nisn' => $identitas,
                'nik' => $nik,
                'jenis_kelamin' => $jk,
                'alamat' => '',
                'no_hp' => trim((string) ($validated['kontak'] ?? $validated['no_hp'] ?? '')),
                'program_id' => $classroom->program_id,
                'tahun_ajaran_id' => $classroom->tahun_ajaran_id,
                'rombel_id' => $classroom->id,
                'status' => 'aktif',
                'catatan' => 'Status Masuk: '.$statusMasuk,
            ]);

            return $created;
        });

        $message = "Siswa {$student->nama_lengkap} berhasil ditambahkan ke kelas {$classroom->nama_rombel}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'student' => $student,
            ]);
        }

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', $message);
        }

        return redirect()->route('rombel.show', $classroom)->with('success', $message);
    }

    /**
     * Metode 3: Preview validasi penambahan massal siswa via paste daftar.
     */
    public function bulkPreview(Request $request, Classroom $classroom): JsonResponse
    {
        $this->authorizeManage($request->user(), $classroom);

        if ($request->has('raw_text') && ! $request->has('raw_data')) {
            $request->merge(['raw_data' => $request->input('raw_text')]);
        }

        $request->validate([
            'raw_data' => ['required', 'string'],
        ], [
            'raw_data.required' => 'Daftar siswa wajib diisi.',
        ]);

        $preview = $this->parseAndValidateBulkData($request->input('raw_data'), $classroom);

        return response()->json($preview);
    }

    /**
     * Metode 3: Eksekusi simpan massal setelah konfirmasi preview.
     */
    public function bulkStore(Request $request, Classroom $classroom): RedirectResponse|JsonResponse
    {
        $this->authorizeManage($request->user(), $classroom);

        if ($request->has('raw_text') && ! $request->has('raw_data')) {
            $request->merge(['raw_data' => $request->input('raw_text')]);
        }

        $request->validate([
            'raw_data' => ['required', 'string'],
        ], [
            'raw_data.required' => 'Daftar siswa wajib diisi.',
        ]);

        // Re-validasi di backend secara ketat!
        $preview = $this->parseAndValidateBulkData($request->input('raw_data'), $classroom);
        $validRows = array_filter($preview['rows'], fn ($r) => $r['is_valid']);

        if (empty($validRows)) {
            $msg = 'Tidak ada data siswa valid yang dapat disimpan.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->route('rombel.show', $classroom)->with('error', $msg);
        }

        $savedCount = 0;
        DB::transaction(function () use ($validRows, $classroom, &$savedCount) {
            foreach ($validRows as $row) {
                $identitas = $row['nomor_identitas'];
                $created = $this->studentRegistration->create([
                    'nama_lengkap' => $row['nama_lengkap'],
                    'nis' => $identitas,
                    'nisn' => $identitas,
                    'nik' => strlen($identitas) === 16 && ctype_digit($identitas) ? $identitas : '',
                    'jenis_kelamin' => $row['jenis_kelamin'],
                    'alamat' => '',
                    'no_hp' => $row['kontak'],
                    'program_id' => $classroom->program_id,
                    'tahun_ajaran_id' => $classroom->tahun_ajaran_id,
                    'rombel_id' => $classroom->id,
                    'status' => 'aktif',
                    'catatan' => 'Status Masuk: '.($row['status_masuk'] ?: 'Siswa Baru'),
                ]);
                $savedCount++;
            }
        });

        $message = "Berhasil menambahkan {$savedCount} siswa ke kelas {$classroom->nama_rombel}.";
        if ($preview['invalid_count'] > 0) {
            $message .= " ({$preview['invalid_count']} baris tidak valid dilewati).";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'saved_count' => $savedCount,
                'skipped_count' => $preview['invalid_count'],
            ]);
        }

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', $message);
        }

        return redirect()->route('rombel.show', $classroom)->with('success', $message);
    }

    /**
     * Keluarkan siswa dari kelas (set rombel_id = null).
     */
    public function remove(Request $request, Classroom $classroom, Student $student): RedirectResponse|JsonResponse
    {
        $this->authorizeManage($request->user(), $classroom);

        if ((int) $student->rombel_id !== (int) $classroom->id) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Peserta didik bukan anggota rombel ini.'], 400);
            }

            return back()->with('error', 'Peserta didik bukan anggota rombel ini.');
        }

        $namaSiswa = $student->nama_lengkap;
        $student->rombel_id = null;
        $student->save();

        $message = "Siswa {$namaSiswa} berhasil dikeluarkan dari kelas {$classroom->nama_rombel}.";

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', $message);
        }

        return redirect()->route('rombel.show', $classroom)->with('success', $message);
    }

    /**
     * @return array{
     *     rows: array<int, array{
     *         index: int,
     *         nama_lengkap: string,
     *         nomor_identitas: string,
     *         jenis_kelamin: string,
     *         kontak: string,
     *         status_masuk: string,
     *         is_valid: bool,
     *         error: ?string
     *     }>,
     *     total_rows: int,
     *     valid_count: int,
     *     invalid_count: int
     * }
     */
    private function parseAndValidateBulkData(string $rawData, Classroom $classroom): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($rawData));
        if ($lines === false) {
            $lines = [];
        }

        $rows = [];
        $seenIdentitas = [];

        // Muat semua NIS yang sudah ada untuk cek duplikasi cepat
        /** @var array<string, bool> $existingNisLookup */
        $existingNisLookup = array_fill_keys(Student::query()->pluck('nis')->filter()->all(), true);

        // Muat NIK terenkripsi untuk cek duplikasi NIK 16 digit
        $existingNiks = Student::query()->select(['id', 'nik'])->get()->map(fn ($s) => (string) $s->nik)->filter()->all();
        /** @var array<string, bool> $existingNikLookup */
        $existingNikLookup = array_fill_keys($existingNiks, true);

        $rowIdx = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Abaikan baris header bila pengguna menyertakan judul kolom
            if (preg_match('/^(nama|nama_lengkap|nis|nik|no\b|nomor)/i', $line)) {
                continue;
            }

            $rowIdx++;

            // Deteksi pemisah: tab (\t), pipa (|), koma (,), atau titik koma (;)
            if (str_contains($line, "\t")) {
                $cols = explode("\t", $line);
            } elseif (str_contains($line, '|')) {
                $cols = explode('|', $line);
            } elseif (str_contains($line, ';')) {
                $cols = str_getcsv($line, ';');
            } else {
                $cols = str_getcsv($line, ',');
            }

            $cols = array_map(fn ($c) => trim((string) $c), $cols);

            // Deteksi fleksibel format input:
            // 1. "NISN, Nama Lengkap, L/P" (kolom pertama digit identitas)
            // 2. "Nama Lengkap" saja (1 kolom)
            // 3. "Nama Lengkap, NISN/Identitas, L/P, Kontak, Status"
            if (count($cols) >= 2 && ctype_digit($cols[0]) && ! ctype_digit($cols[1])) {
                $identitas = $cols[0];
                $nama = $cols[1];
                $jkRaw = $cols[2] ?? 'L';
                $kontak = $cols[3] ?? '';
                $statusMasuk = $cols[4] ?? 'Siswa Baru';
            } elseif (count($cols) === 1) {
                $nama = $cols[0];
                $identitas = '';
                $jkRaw = 'L';
                $kontak = '';
                $statusMasuk = 'Siswa Baru';
            } else {
                $nama = $cols[0];
                $identitas = $cols[1] ?? '';
                $jkRaw = $cols[2] ?? 'L';
                $kontak = $cols[3] ?? '';
                $statusMasuk = $cols[4] ?? 'Siswa Baru';
            }

            // Jika identitas tidak diisi (misal hanya paste Nama Lengkap), buat nomor identitas sementara
            if ($identitas === '') {
                do {
                    $identitas = '99'.str_pad((string) mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
                } while (isset($existingNisLookup[$identitas]) || isset($seenIdentitas[$identitas]));
            }

            $isValid = true;
            $error = null;

            // 1. Validasi nama lengkap
            if ($nama === '') {
                $isValid = false;
                $error = 'Nama lengkap wajib diisi.';
            } elseif (strlen($nama) > 150) {
                $isValid = false;
                $error = 'Nama lengkap maksimal 150 karakter.';
            }

            // 2. Validasi nomor identitas
            if ($isValid && strlen($identitas) > 50) {
                $isValid = false;
                $error = 'Nomor identitas maksimal 50 karakter.';
            }

            // 3. Validasi jenis kelamin
            $jk = 'Laki-laki';
            if ($isValid) {
                $jkUpper = strtoupper($jkRaw);
                if (in_array($jkUpper, ['L', 'LAKI-LAKI', 'LAKI - LAKI', 'PRIA', 'MALE'], true)) {
                    $jk = 'Laki-laki';
                } elseif (in_array($jkUpper, ['P', 'PEREMPUAN', 'WANITA', 'FEMALE'], true)) {
                    $jk = 'Perempuan';
                } else {
                    $isValid = false;
                    $error = 'Jenis kelamin tidak valid (gunakan L/P atau Laki-laki/Perempuan).';
                }
            }

            // 4. Deteksi duplikasi internal dalam teks paste
            if ($isValid) {
                if (isset($seenIdentitas[$identitas])) {
                    $isValid = false;
                    $error = "Duplikat dalam daftar: Nomor identitas '{$identitas}' muncul lebih dari satu kali.";
                } else {
                    $seenIdentitas[$identitas] = true;
                }
            }

            // 5. Deteksi duplikasi terhadap database (NIS & NIK)
            if ($isValid) {
                if (isset($existingNisLookup[$identitas])) {
                    $isValid = false;
                    $error = "Nomor identitas '{$identitas}' sudah terdaftar di database.";
                } elseif (strlen($identitas) === 16 && ctype_digit($identitas) && isset($existingNikLookup[$identitas])) {
                    $isValid = false;
                    $error = "NIK '{$identitas}' sudah terdaftar di database.";
                }
            }

            $rows[] = [
                'index' => $rowIdx,
                'nama_lengkap' => $nama,
                'nomor_identitas' => $identitas,
                'jenis_kelamin' => $jk,
                'kontak' => $kontak,
                'status_masuk' => $statusMasuk,
                'is_valid' => $isValid,
                'error' => $error,
            ];
        }

        $validCount = count(array_filter($rows, fn ($r) => $r['is_valid']));
        $invalidCount = count($rows) - $validCount;

        return [
            'rows' => $rows,
            'total_rows' => count($rows),
            'valid_count' => $validCount,
            'invalid_count' => $invalidCount,
        ];
    }

    private function authorizeManage(?User $user, Classroom $classroom): void
    {
        if ($user === null) {
            abort(403);
        }

        if ($user->hasAnyRole(['admin', 'operator', 'kepala-pkbm'])) {
            return;
        }

        if ($user->hasAnyRole(['wali-kelas', 'wali_kelas', 'wali kelas'])) {
            if ((int) $classroom->wali_kelas_id === (int) $user->id) {
                return;
            }
            abort(403, 'Anda tidak memiliki hak untuk mengelola anggota kelas ini.');
        }

        if (! $this->studentPolicy->manageClassroom($user, $classroom)) {
            abort(403, 'Anda tidak memiliki hak untuk mengelola anggota kelas ini.');
        }
    }
}
