<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnswerQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by policy in controller
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'exists:questions,id'],
            'answers.*.answer_text' => ['nullable', 'string'],
            'answers.*.option_id' => ['nullable', 'exists:options,id'],
            'answers.*.media_path' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required' => 'Jawaban wajib diisi.',
            'answers.array' => 'Format jawaban tidak valid.',
            'answers.*.question_id.required' => 'ID soal wajib diisi.',
            'answers.*.question_id.exists' => 'Soal tidak ditemukan.',
            'answers.*.option_id.exists' => 'Pilihan jawaban tidak valid.',
        ];
    }
}