<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTutorSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('classrooms.manage');
    }

    public function rules(): array
    {
        return [
            'tutor_id' => ['required', 'exists:tutors,user_id'],
            'subject_kode' => ['required', 'string', 'max:30', Rule::unique('subjects', 'kode')],
            'subject_nama' => ['required', 'string', 'max:150'],
        ];
    }
}
