<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'durasi_menit' => ['required', 'integer', 'min:1', 'max:300'],
            'status_aktif' => ['boolean'],
            'max_attempts' => ['integer', 'min:1', 'max:10'],
            'buka_waktu' => ['nullable', 'date'],
            'tutup_waktu' => ['nullable', 'date', 'after:buka_waktu'],
            'acak_soal' => ['boolean'],
            'tampilkan_jawaban' => ['required', Rule::in(['never', 'after_submit', 'after_deadline'])],
            'tampilkan_nilai' => ['required', Rule::in(['never', 'after_submit', 'after_grading'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'durasi_menit' => $this->durasi_menit ?? 30,
            'max_attempts' => $this->max_attempts ?? 1,
            'tampilkan_jawaban' => $this->tampilkan_jawaban ?? 'never',
            'tampilkan_nilai' => $this->tampilkan_nilai ?? 'never',
            'acak_soal' => $this->acak_soal ?? false,
        ]);
    }

    public function messages(): array
    {
        return [
            'buka_waktu.date' => 'Format waktu buka tidak valid.',
            'tutup_waktu.after' => 'Waktu tutup harus setelah waktu buka.',
            'durasi_menit.max' => 'Durasi maksimal 300 menit (5 jam).',
            'max_attempts.max' => 'Maksimal percobaan adalah 10 kali.',
        ];
    }
}