<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jawaban' => ['nullable', 'string'],
            'file_jawaban' => ['nullable', 'file', 'max:10240'],
            'feedback' => ['nullable', 'string'],
            'nilai' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
