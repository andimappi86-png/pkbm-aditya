<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'penugasan_mengajar_id' => ['required', 'exists:penugasan_mengajars,id'],
            'student_id' => ['required', 'exists:students,id'],
            'nilai_tugas' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_uts' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_uas' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_akhir' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
