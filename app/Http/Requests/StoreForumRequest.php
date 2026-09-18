<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreForumRequest extends FormRequest
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
            'isi' => ['required', 'string'],
            'status_aktif' => ['boolean'],
        ];
    }
}
