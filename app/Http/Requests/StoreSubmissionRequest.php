<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assignment_id' => ['required', 'exists:assignments,id'],
            'jawaban' => ['nullable', 'string'],
            'file_jawaban' => ['nullable', 'file', 'max:10240'],
        ];
    }
}
