<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('classrooms.manage');
    }

    public function rules(): array
    {
        return [
            'subject_kode' => ['required', 'string', 'max:30', Rule::unique('subjects', 'kode')],
            'subject_nama' => ['required', 'string', 'max:150'],
            'classroom_kode' => ['required', 'string', 'max:30', Rule::unique('classrooms', 'kode_rombel')],
            'classroom_nama' => ['required', 'string', 'max:100'],
            'program_id' => ['required', 'exists:programs,id'],
            'tahun_ajaran_id' => ['required', 'exists:academic_years,id'],
        ];
    }
}
