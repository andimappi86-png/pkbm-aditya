<?php

namespace App\Imports;

use App\Models\AcademicYear;
use App\Models\Program;
use App\Models\Student;
use App\Models\Tutor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BulkImport implements ToCollection, WithHeadingRow
{
    use Importable;

    protected bool $previewMode;

    protected array $importResults = [
        'total' => 0,
        'siswa' => 0,
        'tutor' => 0,
        'valid' => 0,
        'invalid' => 0,
        'success' => 0,
        'failed' => 0,
        'duplicates' => 0,
        'rows' => [],
        'errors' => [],
    ];

    protected array $seenNik = [];

    public function __construct(bool $previewMode = false)
    {
        $this->previewMode = $previewMode;
    }

    public function collection(Collection $rows): void
    {
        $defaultProgram = Program::where('status_aktif', true)->first();
        $defaultAcademicYear = AcademicYear::where('status_aktif', true)->first();

        $validRowsToInsert = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 for header and 1-based index

            // Skip completely empty rows
            if ($row->filter(fn ($v) => ! is_null($v) && trim((string) $v) !== '')->isEmpty()) {
                continue;
            }

            $this->importResults['total']++;

            $tipeRaw = strtolower(trim((string) $row->get('tipe', '')));
            if ($tipeRaw === 'siswa') {
                $this->importResults['siswa']++;
            } elseif ($tipeRaw === 'tutor') {
                $this->importResults['tutor']++;
            }

            // Validation
            $validation = $this->validateRow($row, $rowNumber);
            $isValid = $validation['valid'];
            $errors = $validation['errors'];

            // Duplicate detection within CSV
            $nikRaw = trim((string) $row->get('nik_dummy', ''));
            if ($nikRaw !== '') {
                if (isset($this->seenNik[$nikRaw])) {
                    $isValid = false;
                    $errors[] = "NIK duplikat di dalam file CSV (pertama muncul di baris {$this->seenNik[$nikRaw]})";
                } else {
                    $this->seenNik[$nikRaw] = $rowNumber;
                }
            }

            if ($isValid) {
                $this->importResults['valid']++;
                $data = $this->prepareData($row, $tipeRaw, $defaultProgram?->id ?? 1, $defaultAcademicYear?->id ?? 1);
                $validRowsToInsert[] = [
                    'row' => $rowNumber,
                    'type' => $tipeRaw,
                    'model_data' => $data,
                ];
            } else {
                $this->importResults['invalid']++;
                $this->importResults['failed']++;
                foreach ($errors as $err) {
                    $this->importResults['errors'][] = [
                        'row' => $rowNumber,
                        'message' => $err,
                        'nama' => (string) $row->get('nama', '-'),
                        'tipe' => $tipeRaw ?: 'tidak diketahui',
                    ];
                }
            }

            // Record row for preview table
            $this->importResults['rows'][] = [
                'row_number' => $rowNumber,
                'tipe' => $tipeRaw,
                'nama' => (string) $row->get('nama', '-'),
                'jenis_kelamin' => (string) $row->get('jenis_kelamin', '-'),
                'tanggal_lahir' => (string) $row->get('tanggal_lahir', '-'),
                'nik' => $nikRaw,
                'email' => (string) $row->get('email_dummy', '-'),
                'status' => (string) $row->get('status', 'aktif'),
                'valid' => $isValid,
                'errors' => $errors,
            ];
        }

        // If preview mode, do not write to database
        if ($this->previewMode) {
            return;
        }

        // Database Transaction for actual import
        DB::beginTransaction();
        try {
            foreach ($validRowsToInsert as $item) {
                if ($item['type'] === 'siswa') {
                    Student::create($item['model_data']);
                } elseif ($item['type'] === 'tutor') {
                    Tutor::create($item['model_data']);
                }
                $this->importResults['success']++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->importResults['success'] = 0;
            $this->importResults['failed'] += count($validRowsToInsert);
            $this->importResults['errors'][] = [
                'row' => 'Global',
                'message' => 'Transaksi dibatalkan karena kegagalan fatal database: '.$e->getMessage(),
                'nama' => 'FATAL',
                'tipe' => 'SYSTEM',
            ];
            throw $e;
        }
    }

    protected function validateRow($row, int $rowNumber): array
    {
        $errors = [];

        // 1. Tipe
        $tipe = strtolower(trim((string) $row->get('tipe', '')));
        if ($tipe === '') {
            $errors[] = 'Tipe wajib diisi (harus siswa atau tutor)';
        } elseif (! in_array($tipe, ['siswa', 'tutor'])) {
            $errors[] = "Tipe tidak valid: '{$tipe}'. Hanya diperbolehkan: siswa, tutor";
        }

        // 2. Nama
        $nama = trim((string) $row->get('nama', ''));
        if ($nama === '') {
            $errors[] = 'Nama wajib diisi';
        }

        // 3. Jenis Kelamin
        $jk = strtolower(trim((string) $row->get('jenis_kelamin', '')));
        if ($jk === '') {
            $errors[] = 'Jenis kelamin wajib diisi';
        } elseif (! in_array($jk, ['laki-laki', 'perempuan', 'l', 'p'])) {
            $errors[] = "Jenis kelamin tidak valid: '{$jk}'. Gunakan: laki-laki / perempuan";
        }

        // 4. Tanggal Lahir
        $tglLahir = trim((string) $row->get('tanggal_lahir', ''));
        if ($tglLahir === '') {
            $errors[] = 'Tanggal lahir wajib diisi';
        } else {
            try {
                Carbon::parse($tglLahir);
            } catch (\Throwable $e) {
                $errors[] = "Tanggal lahir tidak valid: '{$tglLahir}' (gunakan format YYYY-MM-DD)";
            }
        }

        // 5. NIK
        $nik = trim((string) $row->get('nik_dummy', ''));
        if ($nik === '') {
            $errors[] = 'NIK wajib diisi';
        } elseif (! preg_match('/^\d{16}$/', $nik)) {
            $errors[] = 'NIK harus 16 digit angka';
        }

        // 6. Alamat
        $alamat = trim((string) $row->get('alamat_dummy', ''));
        if ($alamat === '') {
            $errors[] = 'Alamat wajib diisi';
        }

        // 7. No HP
        $noHp = trim((string) $row->get('no_hp_dummy', ''));
        if ($noHp === '') {
            $errors[] = 'No HP wajib diisi';
        } elseif (! preg_match('/^[\d\+\-\s\(\)]+$/', $noHp)) {
            $errors[] = 'Format No HP tidak valid';
        }

        // 8. Email
        $email = trim((string) $row->get('email_dummy', ''));
        if ($email === '') {
            $errors[] = 'Email wajib diisi';
        } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }

        // 9. Status
        $status = strtolower(trim((string) $row->get('status', '')));
        if ($status !== '' && ! in_array($status, ['aktif', 'lulus', 'keluar', 'pindah', 'nonaktif', 'tidak_aktif'])) {
            $errors[] = "Status tidak valid: '{$status}'";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    protected function prepareData($row, string $type, int $programId, int $academicYearId): array
    {
        $jkRaw = strtolower(trim((string) $row->get('jenis_kelamin', '')));
        $jenisKelamin = ($jkRaw === 'p' || $jkRaw === 'perempuan') ? 'Perempuan' : 'Laki-laki';

        $statusRaw = strtolower(trim((string) $row->get('status', 'aktif')));
        if ($statusRaw === '') {
            $statusRaw = 'aktif';
        }

        $tanggalLahir = null;
        $tglLahirStr = trim((string) $row->get('tanggal_lahir', ''));
        if ($tglLahirStr !== '') {
            try {
                $tanggalLahir = Carbon::parse($tglLahirStr)->format('Y-m-d');
            } catch (\Throwable $e) {
                $tanggalLahir = null;
            }
        }

        $tempatLahir = trim((string) $row->get('tempat_lahir_dummy', ''));

        $base = [
            'nik' => trim((string) $row->get('nik_dummy', '')),
            'nama_lengkap' => trim((string) $row->get('nama', '')),
            'nama_panggilan' => null,
            'jenis_kelamin' => $jenisKelamin,
            'tempat_lahir' => $tempatLahir ?: null,
            'tanggal_lahir' => $tanggalLahir,
            'alamat' => trim((string) $row->get('alamat_dummy', '')),
            'no_hp' => trim((string) $row->get('no_hp_dummy', '')),
            'email' => trim((string) $row->get('email_dummy', '')),
        ];

        if ($type === 'siswa') {
            return array_merge($base, [
                'nis' => 'NIS-'.strtoupper(uniqid()),
                'nisn' => null,
                'agama' => 'Islam',
                'status_perkawinan' => 'Belum Kawin',
                'program_id' => $programId,
                'tahun_ajaran_id' => $academicYearId,
                'rombel_id' => null,
                'status' => $statusRaw === 'tidak_aktif' ? 'nonaktif' : $statusRaw,
                'nama_ayah' => '-',
                'nama_ibu' => '-',
                'nama_wali' => null,
                'no_hp_wali' => null,
                'alamat_wali' => null,
                'foto' => null,
                'catatan' => 'Impor CSV Operator',
            ]);
        }

        // type == 'tutor'
        $pendidikan = trim((string) $row->get('pendidikan_terakhir', ''));
        $keahlian = trim((string) $row->get('pekerjaan_jabatan', ''));

        return array_merge($base, [
            'nomor_induk' => 'TUTOR-'.strtoupper(uniqid()),
            'agama' => 'Islam',
            'pendidikan_terakhir' => $pendidikan ?: null,
            'bidang_keahlian' => $keahlian ?: null,
            'status_tutor' => $statusRaw === 'nonaktif' ? 'tidak_aktif' : $statusRaw,
            'tanggal_mulai_mengajar' => now()->format('Y-m-d'),
            'foto' => null,
            'catatan' => 'Impor CSV Operator',
        ]);
    }

    public function getResults(): array
    {
        return $this->importResults;
    }
}
