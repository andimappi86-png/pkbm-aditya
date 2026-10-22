<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.manage');
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'role' => ['nullable', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
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
            $user = $this->route('user');
            $currentRole = $user->roles->first()?->name;
            $newRole = $this->string('role')->toString() ?: $currentRole;

            // Role yang memerlukan profil
            if ($newRole === 'peserta-didik' && ! $this->filled('student_id') && ! $user->student) {
                $validator->errors()->add(
                    'student_id',
                    'Profil siswa wajib dipilih untuk role Peserta Didik agar akun dapat mengakses tugas, nilai, dan absensi sesuai profilnya.'
                );
            }

            if (($newRole === 'tutor' || $newRole === 'wali-kelas') && ! $this->filled('tutor_id') && ! $user->tutor) {
                $validator->errors()->add(
                    'tutor_id',
                    'Profil tutor wajib dipilih untuk role '.($newRole === 'tutor' ? 'Tutor' : 'Wali Kelas').' agar akun dapat mengakses kelas dan penugasan mengajarnya.'
                );
            }

            // Role yang tidak boleh menautkan profil
            if ($newRole !== 'peserta-didik' && $this->filled('student_id')) {
                $validator->errors()->add(
                    'student_id',
                    'Penautan profil siswa hanya diperbolehkan untuk role Peserta Didik.'
                );
            }

            if ($newRole !== 'tutor' && $newRole !== 'wali-kelas' && $this->filled('tutor_id')) {
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
            'role.exists' => 'Role yang dipilih tidak valid.',
            'student_id.exists' => 'Profil siswa yang dipilih tidak tersedia atau sudah tertaut ke akun lain.',
            'student_id.integer' => 'Profil siswa yang dipilih tidak valid.',
            'tutor_id.exists' => 'Profil tutor yang dipilih tidak tersedia atau sudah tertaut ke akun lain.',
            'tutor_id.integer' => 'Profil tutor yang dipilih tidak valid.',
        ];
    }
}
