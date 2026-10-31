<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('classrooms.manage');
    }

    public function rules(): array
    {
        return [
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*' => ['exists:subjects,id'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'tahun_ajaran_id' => ['nullable', 'exists:academic_years,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'subjects.required' => 'Pilih minimal satu mata pelajaran.',
            'subjects.min' => 'Pilih minimal satu mata pelajaran.',
            'classroom_id.required' => 'Silakan pilih rombel terlebih dahulu.',
            'classroom_id.exists' => 'Rombel yang dipilih tidak valid.',
        ];
    }
}
