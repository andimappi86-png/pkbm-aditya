<?php

namespace App\Http\Requests;

use App\Models\Classroom;
use App\Models\Student;
use App\Policies\StudentPolicy;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', Student::class);
    }

    /**
     * Aturan pemeriksaan duplikasi NIK untuk atribut terenkripsi.
     *
     * NIK disimpan terenkripsi (AES-256-GCM, non-deterministik) sehingga unique
     * index database tidak dapat dipakai. Perbandingan dilakukan terhadap nilai
     * hasil dekripsi di level aplikasi, bukan terhadap ciphertext, dan nilai NIK
     * tidak pernah dicantumkan pada pesan error maupun log.
     *
     * @param  int|null  $ignoreStudentId  ID siswa yang diabaikan (pembaruan profil sendiri)
     * @return array<int, Closure>
     */
    public static function uniqueNikRule(?int $ignoreStudentId = null): array
    {
        return [
            function (string $attribute, mixed $value, Closure $fail) use ($ignoreStudentId): void {
                $nik = trim((string) $value);

                if ($nik === '') {
                    return;
                }

                $duplicate = Student::query()
                    ->when(
                        $ignoreStudentId !== null,
                        fn ($query) => $query->whereKeyNot($ignoreStudentId)
                    )
                    ->select(['id', 'nik'])
                    ->get()
                    ->contains(fn (Student $student): bool => (string) $student->nik === $nik);

                if ($duplicate) {
                    $fail('NIK ini sudah terdaftar untuk siswa lain.');
                }
            },
        ];
    }

    /**
     * Aturan identitas pokok peserta didik.
     *
     * Dipakai ulang oleh alur "tambah siswa sekaligus buat akun" agar kedua
     * formulir memakai kontrak validasi yang sama.
     *
     * @return array<string, array<int, string>>
     */
    public static function identityRules(): array
    {
        return [
            'nis' => ['required', 'string', 'max:50', 'unique:students,nis'],
            'nisn' => ['nullable', 'string', 'max:50', 'unique:students,nisn'],
            'nik' => ['required', 'string', 'max:50'],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'nama_panggilan' => ['nullable', 'string', 'max:50'],
            'jenis_kelamin' => ['required', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', 'string', 'max:50'],
            'status_perkawinan' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Aturan domisili, kontak, dan penempatan akademik.
     *
     * @return array<string, array<int, string>>
     */
    public static function placementRules(): array
    {
        return [
            'alamat' => ['required', 'string'],
            'rt' => ['nullable', 'string', 'max:10'],
            'rw' => ['nullable', 'string', 'max:10'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'titik_koordinat' => ['nullable', 'string', 'max:100'],
            'jenis_tempat_tinggal' => ['nullable', 'string', 'max:100'],
            'alat_transportasi' => ['nullable', 'string', 'max:100'],
            'jarak_rumah' => ['nullable', 'string', 'max:50'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'program_id' => ['required', 'exists:programs,id'],
            'tahun_ajaran_id' => ['required', 'exists:academic_years,id'],
            'rombel_id' => ['nullable', 'exists:classrooms,id'],
        ];
    }

    public function rules(): array
    {
        $isScoped = app(StudentPolicy::class)->isScopedToOwnClassrooms($this->user());

        $rombelRules = [
            'integer',
            'exists:classrooms,id',
            function (string $attribute, mixed $value, Closure $fail): void {
                if (blank($value)) {
                    return;
                }

                $classroom = Classroom::query()->find($value);

                if ($classroom === null
                    || ! app(StudentPolicy::class)->manageClassroom($this->user(), $classroom)) {
                    $fail('Anda tidak memiliki akses ke rombel tersebut.');
                }
            },
        ];
        array_unshift($rombelRules, $isScoped ? 'required' : 'nullable');

        return array_merge(
            self::identityRules(),
            self::placementRules(),
            [
                'status' => $isScoped
                    ? ['required', 'string', Rule::in(['aktif', 'nonaktif'])]
                    : ['required', 'string', 'max:30'],

                // Scope rombel sesuai kewenangan: wali kelas hanya menempatkan
                // pada rombel miliknya sendiri (diverifikasi di manageClassroom).
                'rombel_id' => $rombelRules,

                // Pencegahan data ganda: NIK diperiksa duplikasinya di level aplikasi.
                'nik' => array_merge(['required', 'string', 'max:50'], self::uniqueNikRule()),

                // Bagian A — Buku Induk Identitas
                'nomor_akta_lahir' => ['nullable', 'string', 'max:100'],
                'nomor_kk' => ['nullable', 'string', 'max:50'],
                'urutan_anak' => ['nullable', 'integer', 'min:1'],
                'foto' => ['nullable', 'image', 'max:2048'],

                // Bagian C — Kesehatan & Fisik
                'kebutuhan_khusus' => ['nullable', 'string', 'max:100'],
                'berat_badan' => ['nullable', 'numeric'],
                'tinggi_badan' => ['nullable', 'numeric'],
                'lingkar_kepala' => ['nullable', 'numeric'],

                // Bagian E — Kesejahteraan & Perbankan
                'kps_status' => ['nullable', 'boolean'],
                'nomor_kps' => ['nullable', 'string', 'max:100'],
                'pkh_status' => ['nullable', 'boolean'],
                'kip_status' => ['nullable', 'boolean'],
                'nama_kip' => ['nullable', 'string', 'max:150'],
                'kks_nomor' => ['nullable', 'string', 'max:100'],
                'pip_kelayakan' => ['nullable', 'boolean'],
                'pip_alasan' => ['nullable', 'string', 'max:255'],
                'bank_nama' => ['nullable', 'string', 'max:100'],
                'rekening_nomor' => ['nullable', 'string', 'max:100'],
                'rekening_nama' => ['nullable', 'string', 'max:150'],

                // Bagian F — Akademik
                'sekolah_asal' => ['nullable', 'string', 'max:200'],
                'nomor_peserta_un' => ['nullable', 'string', 'max:100'],
                'ijazah_nomor' => ['nullable', 'string', 'max:100'],
                'skhun_nomor' => ['nullable', 'string', 'max:100'],

                // Legacy keluarga (opsional, backward-compat)
                'nama_ayah' => ['nullable', 'string', 'max:150'],
                'nama_ibu' => ['nullable', 'string', 'max:150'],
                'nama_wali' => ['nullable', 'string', 'max:150'],
                'no_hp_wali' => ['nullable', 'string', 'max:30'],
                'alamat_wali' => ['nullable', 'string'],

                'catatan' => ['nullable', 'string'],

                // Bagian D — Data Orang Tua/Wali (nested)
                'parent_details' => ['nullable', 'array'],
                'parent_details.*.relationship_type' => ['nullable', 'string', 'in:ayah,ibu,wali'],
                'parent_details.*.name' => ['nullable', 'string', 'max:150'],
                'parent_details.*.nik' => ['nullable', 'string', 'max:50'],
                'parent_details.*.birth_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
                'parent_details.*.education' => ['nullable', 'string', 'max:100'],
                'parent_details.*.occupation' => ['nullable', 'string', 'max:150'],
                'parent_details.*.monthly_income' => ['nullable', 'numeric', 'min:0'],
                'parent_details.*.phone' => ['nullable', 'string', 'max:30'],
            ],
        );
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kps_status' => $this->boolean('kps_status'),
            'pkh_status' => $this->boolean('pkh_status'),
            'kip_status' => $this->boolean('kip_status'),
            'pip_kelayakan' => $this->boolean('pip_kelayakan'),
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rombel_id.required' => 'Pilih rombel tempat siswa akan ditempatkan.',
            'status.in' => 'Status siswa hanya dapat diubah ke "aktif" atau "nonaktif" oleh wali kelas.',
        ];
    }
}
