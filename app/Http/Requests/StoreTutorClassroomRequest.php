<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTutorClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('classrooms.manage');
    }

    public function rules(): array
    {
        return [
            'tutor_id' => ['required', 'exists:tutors,user_id'],
            'classroom_kode' => ['required', 'string', 'max:30', Rule::unique('classrooms', 'kode_rombel')],
            'classroom_nama' => ['required', 'string', 'max:100'],
            'program_id' => ['required', 'exists:programs,id'],
            'tahun_ajaran_id' => ['required', 'exists:academic_years,id'],
        ];
    }
}
