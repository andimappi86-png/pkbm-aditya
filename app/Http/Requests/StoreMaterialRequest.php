<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
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
            'tipe' => ['required', 'in:pdf,video,teks,link'],
            'file_path' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,zip', 'max:20480'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'isi_materi' => ['nullable', 'string'],
            'urutan' => ['nullable', 'integer', 'min:1'],
            'status_aktif' => ['boolean'],
        ];
    }
}
