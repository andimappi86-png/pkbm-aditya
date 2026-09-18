<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('classrooms.create');
    }

    public function rules(): array
    {
        return [
            'nama_rombel' => ['required', 'string', 'max:100'],
            'kode_rombel' => ['required', 'string', 'max:30', Rule::unique('classrooms', 'kode_rombel')],
            'program_id' => ['required', 'exists:programs,id'],
            'tahun_ajaran_id' => ['required', 'exists:academic_years,id'],
            'wali_kelas_id' => ['nullable', 'exists:users,id'],
            'status_aktif' => ['sometimes', 'boolean'],
        ];
    }
}
