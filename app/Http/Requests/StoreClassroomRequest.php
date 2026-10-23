<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('classrooms.create');
    }

    public function rules(): array
    {
        return [
            'nama_rombel' => ['required', 'string', 'max:100'],
            'kode_rombel' => ['required', 'string', 'max:30', Rule::unique('classrooms', 'kode_rombel')],
            'program_id' => ['required', 'exists:programs,id'],
            'tahun_ajaran_id' => ['required', 'exists:academic_years,id'],
            'wali_kelas_name' => ['nullable', 'string', 'max:255'],
            'wali_kelas_id' => ['nullable', 'exists:users,id'],
            'status_aktif' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status_aktif' => $this->boolean('status_aktif'),
        ]);

        if ($this->filled('wali_kelas_id')) {
            $user = User::role('wali-kelas')->find($this->wali_kelas_id);
            if (! $user) {
                $this->merge(['wali_kelas_id' => null]);
            }
        } elseif ($this->has('wali_kelas_name')) {
            $name = trim((string) $this->wali_kelas_name);
            if ($name === '') {
                $this->merge(['wali_kelas_id' => null]);
            } else {
                $users = User::role('wali-kelas')->where('name', $name)->get();
                if ($users->count() === 1) {
                    $this->merge(['wali_kelas_id' => $users->first()->id]);
                }
            }
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('wali_kelas_name')) {
                $name = trim((string) $this->wali_kelas_name);
                $users = User::role('wali-kelas')->where('name', $name)->get();
                if ($users->count() === 0) {
                    $validator->errors()->add('wali_kelas_name', 'Wali kelas tidak ditemukan.');
                } elseif ($users->count() > 1) {
                    $validator->errors()->add('wali_kelas_name', 'Nama wali kelas tidak unik.');
                }
            } elseif ($this->filled('wali_kelas_id')) {
                $user = User::role('wali-kelas')->find($this->wali_kelas_id);
                if (! $user) {
                    $validator->errors()->add('wali_kelas_id', 'Wali kelas yang dipilih tidak valid.');
                }
            }
        });
    }
}
