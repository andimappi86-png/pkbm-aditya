<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'password' => ['required', 'string', 'min:8'],
            'password_confirmation' => ['required', 'string', 'same:password'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],

            // Hanya profil yang belum bertaut yang boleh dipilih. Pemeriksaan ulang
            // tetap dilakukan di dalam transaksi dengan lockForUpdate() untuk
            // mencegah dua permintaan bersamaan menautkan profil yang sama.
            'student_id' => [
                'nullable',
                'integer',
                Rule::exists('students', 'id')->whereNull('user_id'),
            ],
            'tutor_id' => [
                'nullable',
                'integer',
                Rule::exists('tutors', 'id')->whereNull('user_id'),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $role = $this->string('role')->toString();

            if ($role === 'peserta-didik' && ! $this->filled('student_id')) {
                $validator->errors()->add(
                    'student_id',
                    'Profil siswa wajib dipilih untuk role Peserta Didik agar akun dapat mengakses tugas, nilai, dan absensi sesuai profilnya.'
                );
            }

            if ($role === 'tutor' && ! $this->filled('tutor_id')) {
                $validator->errors()->add(
                    'tutor_id',
                    'Profil tutor wajib dipilih untuk role Tutor agar akun dapat mengakses kelas dan penugasan mengajarnya.'
                );
            }

            if ($role === 'wali-kelas' && ! $this->filled('tutor_id')) {
                $validator->errors()->add(
                    'tutor_id',
                    'Profil tutor wajib dipilih untuk role Wali Kelas agar akun dapat mengakses rombel dan penugasan mengajarnya.'
                );
            }

            if ($role !== 'peserta-didik' && $this->filled('student_id')) {
                $validator->errors()->add(
                    'student_id',
                    'Penautan profil siswa hanya diperbolehkan untuk role Peserta Didik.'
                );
            }

            if ($role !== 'tutor' && $role !== 'wali-kelas' && $this->filled('tutor_id')) {
                $validator->errors()->add(
                    'tutor_id',
                    'Penautan profil tutor hanya diperbolehkan untuk role Tutor dan Wali Kelas.'
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'email',
            'phone' => 'telepon',
            'status' => 'status',
            'password' => 'password',
            'password_confirmation' => 'konfirmasi password',
            'role' => 'role',
            'student_id' => 'profil siswa',
            'tutor_id' => 'profil tutor',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'password_confirmation.same' => 'Konfirmasi password tidak sama dengan password yang diisi.',
            'role.required' => 'Role wajib dipilih.',
            'role.exists' => 'Role yang dipilih tidak valid.',
            'student_id.exists' => 'Profil siswa yang dipilih tidak tersedia atau sudah tertaut ke akun lain.',
            'student_id.integer' => 'Profil siswa yang dipilih tidak valid.',
            'tutor_id.exists' => 'Profil tutor yang dipilih tidak tersedia atau sudah tertaut ke akun lain.',
            'tutor_id.integer' => 'Profil tutor yang dipilih tidak valid.',
        ];
    }
}
