<?php

namespace App\Http\Requests;

use App\Models\PenugasanMengajar;
use App\Models\Tutor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'penugasan_mengajar_id' => ['nullable', 'exists:penugasan_mengajars,id'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'durasi_menit' => ['required', 'integer', 'min:1', 'max:300'],
            'status_aktif' => ['boolean'],
            'max_attempts' => ['integer', 'min:1', 'max:10'],
            'buka_waktu' => ['nullable', 'date', 'after_or_equal:now'],
            'tutup_waktu' => ['nullable', 'date', 'after:buka_waktu'],
            'acak_soal' => ['boolean'],
            'tampilkan_jawaban' => ['required', Rule::in(['never', 'after_submit', 'after_deadline'])],
            'tampilkan_nilai' => ['required', Rule::in(['never', 'after_submit', 'after_grading'])],
            'questions_data' => ['nullable', 'string'],
            'questions' => ['nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $penugasanId = $this->penugasan_mengajar_id;

        if (! $penugasanId) {
            $user = $this->user();
            if ($user && $user->hasRole('tutor')) {
                $tutor = Tutor::where('user_id', $user->id)->first();
                $penugasanId = PenugasanMengajar::where('tutor_id', $tutor?->id)
                    ->where('status_aktif', true)
                    ->value('id') ?? PenugasanMengajar::where('tutor_id', $tutor?->id)->value('id');
            } elseif ($user && ($user->hasRole('operator') || $user->hasRole('admin') || $user->hasRole('kepala-pkbm'))) {
                $penugasanId = PenugasanMengajar::where('status_aktif', true)->value('id')
                    ?? PenugasanMengajar::value('id');
            }
        }

        $this->merge([
            'penugasan_mengajar_id' => $penugasanId,
            'durasi_menit' => $this->durasi_menit ?? 30,
            'max_attempts' => $this->max_attempts ?? 1,
            'tampilkan_jawaban' => $this->tampilkan_jawaban ?? 'never',
            'tampilkan_nilai' => $this->tampilkan_nilai ?? 'never',
            'acak_soal' => (bool) $this->acak_soal,
        ]);
    }

    public function messages(): array
    {
        return [
            'buka_waktu.after_or_equal' => 'Waktu buka harus di masa depan atau sekarang.',
            'tutup_waktu.after' => 'Waktu tutup harus setelah waktu buka.',
            'durasi_menit.max' => 'Durasi maksimal 300 menit (5 jam).',
            'max_attempts.max' => 'Maksimal percobaan adalah 10 kali.',
        ];
    }
}