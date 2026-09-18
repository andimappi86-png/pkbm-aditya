<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('announcements.manage');
    }

    public function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'kategori' => 'required|string|max:100',
            'target_role' => 'nullable|string|exists:roles,name',
            'target_program' => 'nullable|exists:programs,id',
            'target_rombel' => 'nullable|exists:classrooms,id',
            'published_at' => 'nullable|date',
            'status_aktif' => 'boolean',
        ];
    }
}
