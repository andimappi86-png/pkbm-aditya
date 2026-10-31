<?php

namespace App\Http\Requests;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class AssignWaliKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('classrooms.manage') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'wali_kelas_id' => ['required', 'exists:users,id'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator): void {
            $classroomId = $this->input('classroom_id');
            $academicYearId = $this->input('academic_year_id');
            $waliKelasId = $this->input('wali_kelas_id');

            if ($classroomId && $academicYearId) {
                $classroom = Classroom::find($classroomId);
                if ($classroom && (int) $classroom->tahun_ajaran_id !== (int) $academicYearId) {
                    $validator->errors()->add('classroom_id', 'Rombel tidak terdaftar pada tahun ajaran yang dipilih.');
                }
            }

            if ($waliKelasId) {
                $user = User::find($waliKelasId);
                if ($user && ! $user->hasAnyRole(['wali-kelas', 'wali_kelas', 'wali kelas'])) {
                    $validator->errors()->add('wali_kelas_id', 'Pengguna yang dipilih harus memiliki peran Wali Kelas.');
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran yang dipilih tidak valid.',
            'classroom_id.required' => 'Rombel wajib dipilih.',
            'classroom_id.exists' => 'Rombel yang dipilih tidak valid.',
            'wali_kelas_id.required' => 'Wali kelas wajib dipilih.',
            'wali_kelas_id.exists' => 'Wali kelas yang dipilih tidak valid.',
        ];
    }
}
