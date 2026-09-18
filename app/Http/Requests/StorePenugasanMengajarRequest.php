<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenugasanMengajarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tutor_id' => ['required', 'exists:tutors,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'status_aktif' => ['sometimes', 'boolean'],
        ];
    }

    public function validationMessages(): array
    {
        return [
            'tutor_id.required' => 'Tutor wajib dipilih.',
            'tutor_id.exists' => 'Tutor yang dipilih tidak valid.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran yang dipilih tidak valid.',
            'classroom_id.required' => 'Rombel wajib dipilih.',
            'classroom_id.exists' => 'Rombel yang dipilih tidak valid.',
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran yang dipilih tidak valid.',
            'semester_id.exists' => 'Semester yang dipilih tidak valid.',
            'status_aktif.boolean' => 'Status aktif tidak valid.',
        ];
    }
}
