<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('subjects.create');
    }

    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:30', 'unique:subjects,kode'],
            'nama' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
            'status_aktif' => ['sometimes', 'boolean'],
        ];
    }
}
