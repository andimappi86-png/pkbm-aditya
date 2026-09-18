<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
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
            'instruksi' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
            'file_materi' => ['nullable', 'file', 'max:10240'],
            'is_active' => ['boolean'],
            'type' => ['required', 'in:tugas,quiz'],
        ];
    }
}
