<?php

namespace App\Http\Requests;

use App\Models\PenugasanMengajar;
use App\Models\Schedule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('schedules.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'penugasan_mengajar_id' => ['required', 'exists:penugasan_mengajars,id'],
            'hari' => ['required', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'ruangan' => ['nullable', 'string', 'max:50'],
            'status_aktif' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $penugasanId = $this->input('penugasan_mengajar_id');
            $hari = $this->input('hari');
            $jamMulai = $this->input('jam_mulai');
            $jamSelesai = $this->input('jam_selesai');

            if (! $penugasanId || ! $hari || ! $jamMulai || ! $jamSelesai) {
                return;
            }

            $penugasan = PenugasanMengajar::find($penugasanId);
            if (! $penugasan) {
                return;
            }

            // Cek konflik Tutor
            $tutorConflict = Schedule::where('hari', $hari)
                ->whereHas('penugasanMengajar', function ($q) use ($penugasan) {
                    $q->where('tutor_id', $penugasan->tutor_id);
                })
                ->where(function ($q) use ($jamMulai, $jamSelesai) {
                    $q->whereBetween('jam_mulai', [$jamMulai, $jamSelesai])
                        ->orWhereBetween('jam_selesai', [$jamMulai, $jamSelesai])
                        ->orWhere(function ($q2) use ($jamMulai, $jamSelesai) {
                            $q2->where('jam_mulai', '<=', $jamMulai)
                                ->where('jam_selesai', '>=', $jamSelesai);
                        });
                })->exists();

            if ($tutorConflict) {
                $validator->errors()->add('jam_mulai', 'Jadwal bertabrakan dengan jadwal tutor lain pada waktu yang sama.');
            }

            // Cek konflik Rombel
            $rombelConflict = Schedule::where('hari', $hari)
                ->whereHas('penugasanMengajar', function ($q) use ($penugasan) {
                    $q->where('classroom_id', $penugasan->classroom_id);
                })
                ->where(function ($q) use ($jamMulai, $jamSelesai) {
                    $q->whereBetween('jam_mulai', [$jamMulai, $jamSelesai])
                        ->orWhereBetween('jam_selesai', [$jamMulai, $jamSelesai])
                        ->orWhere(function ($q2) use ($jamMulai, $jamSelesai) {
                            $q2->where('jam_mulai', '<=', $jamMulai)
                                ->where('jam_selesai', '>=', $jamSelesai);
                        });
                })->exists();

            if ($rombelConflict) {
                $validator->errors()->add('jam_mulai', 'Jadwal bertabrakan dengan pembelajaran lain di rombel yang sama.');
            }

            // Cek konflik Ruangan (opsional, jika ruang diisi)
            $ruangan = $this->input('ruangan');
            if ($ruangan) {
                $ruangConflict = Schedule::where('hari', $hari)
                    ->where('ruangan', $ruangan)
                    ->where(function ($q) use ($jamMulai, $jamSelesai) {
                        $q->whereBetween('jam_mulai', [$jamMulai, $jamSelesai])
                            ->orWhereBetween('jam_selesai', [$jamMulai, $jamSelesai])
                            ->orWhere(function ($q2) use ($jamMulai, $jamSelesai) {
                                $q2->where('jam_mulai', '<=', $jamMulai)
                                    ->where('jam_selesai', '>=', $jamSelesai);
                            });
                    })->exists();

                if ($ruangConflict) {
                    $validator->errors()->add('ruangan', 'Ruangan sudah digunakan pada waktu tersebut.');
                }
            }
        });
    }
}
