<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePenugasanMengajarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tutor_id' => ['sometimes', 'required', 'exists:tutors,id'],
            'subject_id' => ['sometimes', 'required', 'exists:subjects,id'],
            'classroom_id' => ['sometimes', 'required', 'exists:classrooms,id'],
            'academic_year_id' => ['sometimes', 'required', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'sometimes', 'exists:semesters,id'],
            'status_aktif' => ['sometimes', 'boolean'],
        ];
    }

    public function validationMessages(): array
    {
        return [
            'tutor_id.exists' => 'Tutor yang dipilih tidak valid.',
            'subject_id.exists' => 'Mata pelajaran yang dipilih tidak valid.',
            'classroom_id.exists' => 'Rombel yang dipilih tidak valid.',
            'academic_year_id.exists' => 'Tahun ajaran yang dipilih tidak valid.',
            'semester_id.exists' => 'Semester yang dipilih tidak valid.',
            'status_aktif.boolean' => 'Status aktif tidak valid.',
        ];
    }
}
