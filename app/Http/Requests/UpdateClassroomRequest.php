<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('classrooms.update');
    }

    public function rules(): array
    {
        $rombelId = $this->route('rombel')->id ?? null;

        return [
            'nama_rombel' => ['required', 'string', 'max:100'],
            'kode_rombel' => ['required', 'string', 'max:30', Rule::unique('classrooms', 'kode_rombel')->ignore($rombelId)],
            'program_id' => ['required', 'exists:programs,id'],
            'tahun_ajaran_id' => ['required', 'exists:academic_years,id'],
            'wali_kelas_id' => ['nullable', 'exists:users,id'],
            'status_aktif' => ['sometimes', 'boolean'],
        ];
    }
}
