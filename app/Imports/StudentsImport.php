<?php

namespace App\Imports;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithBatchInserts, WithChunkReading, WithHeadingRow
{
    use Importable;

    protected $previewMode;

    protected $importResults = [
        'success' => 0,
        'failed' => 0,
        'duplicates' => 0,
        'errors' => [],
        'preview_data' => [],
    ];

    protected $seenNis = [];

    protected $seenNisn = [];

    protected $seenNik = [];

    public function __construct(bool $previewMode = false)
    {
        $this->previewMode = $previewMode;
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection as $index => $row) {
            $rowNumber = $index + 2; // +2 because heading row is 1, and array is 0-indexed

            // Skip completely empty rows
            if ($row->filter()->isEmpty()) {
                continue;
            }

            $validationResult = $this->validateRow($row, $rowNumber);

            if (! $validationResult['valid']) {
                $this->importResults['failed']++;
                foreach ($validationResult['errors'] as $error) {
                    $this->importResults['errors'][] = [
                        'row' => $rowNumber,
                        'message' => $error,
                        'data' => $row->toArray(),
                    ];
                }

                // Even if invalid, store in preview for detection
                if ($this->previewMode && count($this->importResults['preview_data']) < 300) {
                    $previewItem = $row->toArray();
                    $this->importResults['preview_data'][] = $previewItem;
                }

                continue;
            }

            $data = $this->prepareData($row);

            // Check duplicates within the import file
            $internalDuplicate = false;
            if (isset($this->seenNis[$data['nis']])) {
                $internalDuplicate = true;
            }
            if (isset($data['nisn']) && $data['nisn'] && isset($this->seenNisn[$data['nisn']])) {
                $internalDuplicate = true;
            }
            if (isset($this->seenNik[$data['nik']])) {
                $internalDuplicate = true;
            }

            if ($internalDuplicate) {
                $this->importResults['duplicates']++;
                $this->importResults['errors'][] = [
                    'row' => $rowNumber,
                    'message' => 'Data duplikat dalam file Excel (NIS/NISN/NIK sudah ada di baris lain)',
                    'data' => $row->toArray(),
                ];

                continue;
            }

            // Mark as seen for internal duplicate detection
            $this->seenNis[$data['nis']] = true;
            if (isset($data['nisn']) && $data['nisn']) {
                $this->seenNisn[$data['nisn']] = true;
            }
            $this->seenNik[$data['nik']] = true;

            // Check database duplicates
            $existingNis = Student::where('nis', $data['nis'])->exists();
            $existingNisn = isset($data['nisn']) && $data['nisn'] ? Student::where('nisn', $data['nisn'])->exists() : false;

            // NIK is encrypted, so we must fetch all NIKs or query if we used deterministic encryption.
            // Assuming AES-256-GCM is deterministic or we have a hash column. Wait, standard Laravel encrypted casts are NON-deterministic by default!
            // If it's non-deterministic, we can't simply do Student::where('nik', $data['nik'])->exists().
            // Wait! The prompt says: "Gunakan identifier yang benar berdasarkan schema Student. Minimal cegah: duplikat internal dalam Excel, duplikat dengan database... Field sensitif seperti NIK... JANGAN menggunakan SQL insert mentah... Gunakan Student Model/Eloquent".
            // If NIK is encrypted, checking for DB duplicate might require loading all students or relying on unique constraints if available.
            // Let's check if the database has a unique constraint on NIK, or if NIS/NISN are the primary unique identifiers.
            // The FormRequest for Student only checks `unique:students,nis` and `unique:students,nisn`. It does NOT check `unique:students,nik`!
            // So we will just check `nis` and `nisn` for database duplicates to be safe and accurate.

            if ($existingNis || $existingNisn) {
                $this->importResults['duplicates']++;
                $msg = [];
                if ($existingNis) {
                    $msg[] = 'NIS sudah terdaftar';
                }
                if ($existingNisn) {
                    $msg[] = 'NISN sudah terdaftar';
                }

                $this->importResults['errors'][] = [
                    'row' => $rowNumber,
                    'message' => implode(', ', $msg),
                    'data' => $row->toArray(),
                ];

                continue;
            }

            if ($this->previewMode) {
                $this->importResults['success']++;
                if (count($this->importResults['preview_data']) < 10) {
                    $this->importResults['preview_data'][] = $data;
                }

                continue;
            }

            try {
                // Create student using Eloquent to trigger AES encryption
                Student::create($data);
                $this->importResults['success']++;
            } catch (\Exception $e) {
                $this->importResults['failed']++;
                $this->importResults['errors'][] = [
                    'row' => $rowNumber,
                    'message' => 'Gagal menyimpan: '.$e->getMessage(),
                    'data' => $row->toArray(),
                ];
            }
        }
    }

    protected function validateRow($row, $rowNumber): array
    {
        $errors = [];

        // Required fields check with fallback
        $nama = $row->get('nama') ?? $row->get('nama_lengkap');
        if (empty($nama) || trim($nama) === '') {
            $errors[] = "Nama Lengkap wajib diisi";
        }

        $nik = $row->get('nik_dummy') ?? $row->get('nik');
        if (empty($nik) || trim($nik) === '') {
            $errors[] = "NIK wajib diisi";
        } elseif (! preg_match('/^\d{16}$/', trim($nik))) {
            $errors[] = 'NIK harus 16 digit angka';
        }

        $jk = $row->get('jenis_kelamin');
        if (empty($jk) || trim($jk) === '') {
            $errors[] = "Jenis Kelamin wajib diisi";
        } else {
            $jkVal = strtolower(trim($jk));
            if (! in_array($jkVal, ['laki-laki', 'perempuan', 'l', 'p'])) {
                $errors[] = 'Jenis Kelamin harus Laki-laki atau Perempuan';
            }
        }

        $alamat = $row->get('alamat_dummy') ?? $row->get('alamat');
        if (empty($alamat) || trim($alamat) === '') {
            $errors[] = "Alamat wajib diisi";
        }

        // Validate Tanggal Lahir
        $tglLahir = $row->get('tanggal_lahir');
        if ($tglLahir) {
            try {
                Carbon::parse($tglLahir);
            } catch (\Exception $e) {
                $errors[] = 'Tanggal Lahir tidak valid';
            }
        }

        // Validate Email
        $email = $row->get('email_dummy') ?? $row->get('email');
        if ($email) {
            if (! filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Format Email tidak valid';
            }
        }

        // Validate Tipe
        $tipe = strtolower(trim($row->get('tipe', 'siswa')));
        if ($tipe === 'tutor') {
            $errors[] = 'Halaman ini khusus Peserta Didik. Baris ini terdeteksi sebagai Tutor.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    protected function prepareData($row): array
    {
        $jenisKelamin = strtolower(trim($row->get('jenis_kelamin', '')));
        if ($jenisKelamin === 'l' || $jenisKelamin === 'laki-laki') {
            $jenisKelamin = 'Laki-laki';
        } elseif ($jenisKelamin === 'p' || $jenisKelamin === 'perempuan') {
            $jenisKelamin = 'Perempuan';
        }

        $status = strtolower(trim($row->get('status', 'aktif')));

        $data = [
            'nis' => trim($row->get('id')),
            'nik' => trim($row->get('nik_dummy')),
            'nama_lengkap' => trim($row->get('nama')),
            'jenis_kelamin' => $jenisKelamin,
            'tempat_lahir' => trim($row->get('tempat_lahir_dummy')),
            'tanggal_lahir' => trim($row->get('tanggal_lahir')),
            'alamat' => trim($row->get('alamat_dummy')),
            'no_hp' => trim($row->get('no_hp_dummy')),
            'email' => trim($row->get('email_dummy')),
            'pendidikan_terakhir' => trim($row->get('pendidikan_terakhir')),
            'status' => $status,
            'nama_ayah' => trim($row->get('nama_ayah')),
            'nama_ibu' => trim($row->get('nama_ibu')),
            'nama_wali' => trim($row->get('nama_wali')),
            'no_hp_wali' => trim($row->get('no_hp_wali')),
            'alamat_wali' => trim($row->get('alamat_wali')),
            // Defaults that might be required
            'program_id' => 1,
            'tahun_ajaran_id' => 1,
        ];

        // Process tanggal_lahir
        if (!empty($data['tanggal_lahir'])) {
            try {
                $data['tanggal_lahir'] = Carbon::parse($data['tanggal_lahir'])->format('Y-m-d');
            } catch (\Exception $e) {
                $data['tanggal_lahir'] = null;
            }
        }

        return $data;
    }

    public function rules(): array
    {
        return [];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getResults(): array
    {
        return $this->importResults;
    }
}
