<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'penugasan_mengajar_id' => ['required', 'exists:penugasan_mengajars,id'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'durasi_menit' => ['nullable', 'integer', 'min:1'],
            'status_aktif' => ['boolean'],
        ];
    }
}