<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('tutors.create');
    }

    public function rules(): array
    {
        return [
            'nomor_induk' => ['required', 'string', 'max:50', 'unique:tutors,nomor_induk'],
            'nik' => ['required', 'digits:16'],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'nama_panggilan' => ['nullable', 'string', 'max:50'],
            'jenis_kelamin' => ['required', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:100'],
            'bidang_keahlian' => ['required', 'string', 'max:150'],
            'status_tutor' => ['required', 'string', 'max:30'],
            'tanggal_mulai_mengajar' => ['nullable', 'date'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'catatan' => ['nullable', 'string'],
        ];
    }
}
