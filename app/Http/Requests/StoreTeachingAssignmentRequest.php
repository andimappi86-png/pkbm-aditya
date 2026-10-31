<?php

namespace App\Http\Requests;

use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeachingAssignmentRequest extends FormRequest
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
            'tutor_id' => ['required', 'exists:tutors,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'status_aktif' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator): void {
            $yearId = $this->input('academic_year_id');
            $tutorId = $this->input('tutor_id');
            $subjectId = $this->input('subject_id');
            $classroomId = $this->input('classroom_id');

            if ($yearId && $classroomId) {
                $classroom = Classroom::find($classroomId);
                if ($classroom && (int) $classroom->tahun_ajaran_id !== (int) $yearId) {
                    $validator->errors()->add('classroom_id', 'Rombel tidak terdaftar pada tahun ajaran yang dipilih.');
                }
            }

            if ($yearId && $tutorId && $subjectId && $classroomId) {
                $exists = PenugasanMengajar::where('academic_year_id', $yearId)
                    ->where('tutor_id', $tutorId)
                    ->where('subject_id', $subjectId)
                    ->where('classroom_id', $classroomId)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('tutor_id', 'Penugasan mengajar untuk tutor, mata pelajaran, dan rombel tersebut pada tahun ajaran ini sudah terdaftar.');
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
            'tutor_id.required' => 'Tutor wajib dipilih.',
            'tutor_id.exists' => 'Tutor yang dipilih tidak valid.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran yang dipilih tidak valid.',
            'classroom_id.required' => 'Rombel wajib dipilih.',
            'classroom_id.exists' => 'Rombel yang dipilih tidak valid.',
        ];
    }
}
