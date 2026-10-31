<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi jalur "tambah siswa sekaligus buat akun".
 *
 * Satu kiriman membuat profil students dan akun users dalam satu transaksi.
 * Role akun selalu peserta-didik dan tidak pernah diambil dari input, sehingga
 * form ini tidak bisa dipakai untuk membuat akun administratif.
 */
class StoreStudentAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.manage')
            && $this->user()->can('students.create');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $studentRules = StoreStudentRequest::identityRules() + StoreStudentRequest::placementRules();

        // Nama siswa memakai field "name" akun dan email siswa memakai email
        // akun, supaya operator hanya mengisi identitas itu satu kali.
        unset($studentRules['nama_lengkap'], $studentRules['email']);

        return array_merge($studentRules, [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'student_status' => ['required', 'string', Rule::in(['aktif', 'lulus', 'keluar', 'pindah', 'nonaktif'])],
            'password' => ['required', 'string', 'min:8'],
            'password_confirmation' => ['required', 'string', 'same:password'],
        ]);
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('student_status')) {
            $this->merge(['student_status' => 'aktif']);
        }
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'email',
            'phone' => 'telepon',
            'status' => 'status akun',
            'student_status' => 'status peserta didik',
            'password' => 'password',
            'password_confirmation' => 'konfirmasi password',
            'nis' => 'NIS',
            'nisn' => 'NISN',
            'nik' => 'NIK',
            'jenis_kelamin' => 'jenis kelamin',
            'tempat_lahir' => 'tempat lahir',
            'tanggal_lahir' => 'tanggal lahir',
            'alamat' => 'alamat',
            'no_hp' => 'nomor HP',
            'program_id' => 'program',
            'tahun_ajaran_id' => 'tahun ajaran',
            'rombel_id' => 'rombel',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar pada akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'password_confirmation.same' => 'Konfirmasi password tidak sama dengan password yang diisi.',
            'status.in' => 'Status akun yang dipilih tidak valid.',
            'student_status.in' => 'Status peserta didik yang dipilih tidak valid.',
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah dipakai peserta didik lain.',
            'nisn.unique' => 'NISN sudah dipakai peserta didik lain.',
            'nik.required' => 'NIK wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'alamat.required' => 'Alamat wajib diisi.',
            'program_id.required' => 'Program wajib dipilih.',
            'program_id.exists' => 'Program yang dipilih tidak valid.',
            'tahun_ajaran_id.required' => 'Tahun ajaran wajib dipilih.',
            'tahun_ajaran_id.exists' => 'Tahun ajaran yang dipilih tidak valid.',
            'rombel_id.exists' => 'Rombel yang dipilih tidak valid.',
        ];
    }
}
