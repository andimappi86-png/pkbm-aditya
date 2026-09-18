<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('subjects.update');
    }

    public function rules(): array
    {
        $subjectId = $this->route('subject')->id ?? null;

        return [
            'kode' => ['required', 'string', 'max:30', Rule::unique('subjects', 'kode')->ignore($subjectId)],
            'nama' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
            'status_aktif' => ['sometimes', 'boolean'],
        ];
    }
}
