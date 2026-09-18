<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('attendance.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'schedule_id' => ['required', 'exists:schedules,id'],
            'student_id' => ['required', 'exists:students,id'],
            'tanggal' => ['required', 'date'],
            'status' => ['required', 'in:hadir,izin,sakit,alpa'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }
}
