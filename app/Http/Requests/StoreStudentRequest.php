<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('students.create');
    }

    public function rules(): array
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
            'alamat' => ['required', 'string'],
            'rt' => ['nullable', 'string', 'max:10'],
            'rw' => ['nullable', 'string', 'max:10'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'program_id' => ['required', 'exists:programs,id'],
            'tahun_ajaran_id' => ['required', 'exists:academic_years,id'],
            'rombel_id' => ['nullable', 'exists:classrooms,id'],
            'status' => ['required', 'string', 'max:30'],
            'nama_ayah' => ['nullable', 'string', 'max:150'],
            'nama_ibu' => ['nullable', 'string', 'max:150'],
            'nama_wali' => ['nullable', 'string', 'max:150'],
            'no_hp_wali' => ['nullable', 'string', 'max:30'],
            'alamat_wali' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'catatan' => ['nullable', 'string'],
        ];
    }
}
