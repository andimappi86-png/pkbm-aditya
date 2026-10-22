<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTutorClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('classrooms.manage');
    }

    public function rules(): array
    {
        $modeTutor = $this->input('mode_tutor', 'select');
        $modeRombel = $this->input('mode_rombel', 'select');

        $rules = [
            'mode_tutor' => ['required', 'in:select,manual'],
            'mode_rombel' => ['required', 'in:select,manual'],
            'program_id' => ['required', 'exists:programs,id'],
            'tahun_ajaran_id' => ['required', 'exists:academic_years,id'],
        ];

        if ($modeTutor === 'select') {
            $rules['tutor_id'] = ['required', 'exists:tutors,id'];
        } else {
            $rules['tutor_nama'] = ['required', 'string', 'max:150'];
        }

        if ($modeRombel === 'select') {
            $rules['classroom_id'] = ['required', 'exists:classrooms,id'];
        } else {
            $rules['classroom_nama'] = ['required', 'string', 'max:100'];
            $rules['classroom_kode'] = ['required', 'string', 'max:30'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tutor_id.required' => 'Pilih tutor dari daftar yang tersedia.',
            'tutor_id.exists' => 'Tutor yang dipilih tidak valid.',
            'tutor_nama.required' => 'Masukkan nama tutor.',
            'classroom_id.required' => 'Pilih rombel dari daftar yang tersedia.',
            'classroom_id.exists' => 'Rombel yang dipilih tidak valid.',
            'classroom_nama.required' => 'Masukkan nama rombel.',
            'classroom_kode.required' => 'Masukkan kode rombel.',
            'program_id.required' => 'Pilih program.',
            'program_id.exists' => 'Program yang dipilih tidak valid.',
            'tahun_ajaran_id.required' => 'Pilih tahun ajaran.',
            'tahun_ajaran_id.exists' => 'Tahun ajaran yang dipilih tidak valid.',
        ];
    }
}
