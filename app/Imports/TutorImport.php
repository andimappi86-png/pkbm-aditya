<?php

namespace App\Imports;

use App\Models\Tutor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TutorImport implements ToCollection, WithHeadingRow
{
    use Importable;

    protected bool $previewMode;

    protected array $importResults = [
        'total' => 0,
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
        $validRowsToInsert = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            if ($row->filter(fn ($v) => ! is_null($v) && trim((string) $v) !== '')->isEmpty()) {
                continue;
            }

            $this->importResults['total']++;

            // Validation
            $validation = $this->validateRow($row, $rowNumber);
            $isValid = $validation['valid'];
            $errors = $validation['errors'];

            $nikRaw = trim((string) $row->get('nik_dummy', ''));
            if ($nikRaw !== '') {
                if (isset($this->seenNik[$nikRaw])) {
                    $isValid = false;
                    $errors[] = "NIK duplikat di dalam file CSV (baris {$this->seenNik[$nikRaw]})";
                } else {
                    $this->seenNik[$nikRaw] = $rowNumber;
                }
            }

            if ($isValid) {
                $this->importResults['valid']++;
                $data = $this->prepareData($row);
                $validRowsToInsert[] = [
                    'row' => $rowNumber,
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
                    ];
                }
            }

            $this->importResults['rows'][] = [
                'row_number' => $rowNumber,
                'nama' => (string) $row->get('nama', '-'),
                'jenis_kelamin' => (string) $row->get('jenis_kelamin', '-'),
                'tanggal_lahir' => (string) $row->get('tanggal_lahir', '-'),
                'nik' => $nikRaw,
                'email' => (string) $row->get('email_dummy', '-'),
                'bidang_keahlian' => (string) $row->get('pekerjaan_jabatan', '-'),
                'status' => (string) $row->get('status', 'aktif'),
                'valid' => $isValid,
                'errors' => $errors,
            ];
        }

        if ($this->previewMode) {
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($validRowsToInsert as $item) {
                Tutor::create($item['model_data']);
                $this->importResults['success']++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->importResults['success'] = 0;
            $this->importResults['failed'] += count($validRowsToInsert);
            $this->importResults['errors'][] = [
                'row' => 'Global',
                'message' => 'Gagal simpan ke database: '.$e->getMessage(),
                'nama' => 'FATAL',
            ];
            throw $e;
        }
    }

    protected function validateRow($row, int $rowNumber): array
    {
        $errors = [];

        if (trim((string) $row->get('nama', '')) === '') {
            $errors[] = 'Nama wajib diisi';
        }

        $jk = strtolower(trim((string) $row->get('jenis_kelamin', '')));
        if ($jk === '') {
            $errors[] = 'Jenis kelamin wajib diisi';
        } elseif (! in_array($jk, ['laki-laki', 'perempuan', 'l', 'p'])) {
            $errors[] = "Jenis kelamin tidak valid: '{$jk}'";
        }

        $tglLahir = trim((string) $row->get('tanggal_lahir', ''));
        if ($tglLahir !== '') {
            try {
                Carbon::parse($tglLahir);
            } catch (\Throwable $e) {
                $errors[] = "Tanggal lahir tidak valid: '{$tglLahir}'";
            }
        }

        $nik = trim((string) $row->get('nik_dummy', ''));
        if ($nik === '') {
            $errors[] = 'NIK wajib diisi';
        } elseif (! preg_match('/^\d{16}$/', $nik)) {
            $errors[] = 'NIK harus 16 digit';
        }

        $email = trim((string) $row->get('email_dummy', ''));
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }

        $status = strtolower(trim((string) $row->get('status', '')));
        if ($status !== '' && ! in_array($status, ['aktif', 'tidak_aktif', 'nonaktif'])) {
            $errors[] = "Status tidak valid: '{$status}'";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    protected function prepareData($row): array
    {
        $jkRaw = strtolower(trim((string) $row->get('jenis_kelamin', '')));
        $jenisKelamin = ($jkRaw === 'p' || $jkRaw === 'perempuan') ? 'Perempuan' : 'Laki-laki';

        $statusRaw = strtolower(trim((string) $row->get('status', 'aktif')));
        if ($statusRaw === 'nonaktif') {
            $statusRaw = 'tidak_aktif';
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

        return [
            'nomor_induk' => 'TUTOR-'.strtoupper(uniqid()),
            'nik' => trim((string) $row->get('nik_dummy', '')),
            'nama_lengkap' => trim((string) $row->get('nama', '')),
            'jenis_kelamin' => $jenisKelamin,
            'tempat_lahir' => trim((string) $row->get('tempat_lahir_dummy', '')) ?: null,
            'tanggal_lahir' => $tanggalLahir,
            'alamat' => trim((string) $row->get('alamat_dummy', '')) ?: null,
            'no_hp' => trim((string) $row->get('no_hp_dummy', '')) ?: null,
            'email' => trim((string) $row->get('email_dummy', '')) ?: null,
            'pendidikan_terakhir' => trim((string) $row->get('pendidikan_terakhir', '')) ?: null,
            'bidang_keahlian' => trim((string) $row->get('pekerjaan_jabatan', '')) ?: null,
            'status_tutor' => $statusRaw ?: 'aktif',
            'tanggal_mulai_mengajar' => now()->format('Y-m-d'),
            'catatan' => 'Impor Khusus Tutor',
        ];
    }

    public function getResults(): array
    {
        return $this->importResults;
    }
}
