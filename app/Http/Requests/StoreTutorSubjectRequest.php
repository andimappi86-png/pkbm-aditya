<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTutorSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('classrooms.manage');
    }

    public function rules(): array
    {
        return [
            'tutor_id' => ['required', 'exists:tutors,id'],
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*' => ['required', 'exists:subjects,id'],
        ];
    }
}
