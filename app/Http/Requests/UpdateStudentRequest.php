<?php

namespace App\Http\Requests;

use App\Models\Classroom;
use App\Models\Student;
use App\Policies\StudentPolicy;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $student = $this->route('student') ?? $this->route('peserta_didik');

        return $student instanceof Student
            ? Gate::allows('update', $student)
            : $this->user()->can('students.update');
    }

    public function rules(): array
    {
        $student = $this->route('student') ?? $this->route('peserta_didik');
        $studentId = is_object($student) ? $student->id : (int) $student;

        $isScoped = app(StudentPolicy::class)->isScopedToOwnClassrooms($this->user());

        $rombelRules = [
            'integer',
            'exists:classrooms,id',
            function (string $attribute, mixed $value, Closure $fail): void {
                if (blank($value)) {
                    return;
                }

                $classroom = Classroom::query()->find($value);

                if ($classroom === null
                    || ! app(StudentPolicy::class)->manageClassroom($this->user(), $classroom)) {
                    $fail('Anda tidak memiliki akses ke rombel tersebut.');
                }
            },
        ];
        array_unshift($rombelRules, $isScoped ? 'required' : 'nullable');

        return [
            // Identitas Dasar
            'nis' => ['required', 'string', 'max:50', Rule::unique('students')->ignore($studentId)],
            'nisn' => ['nullable', 'string', 'max:50', Rule::unique('students')->ignore($studentId)],
            // NIK terenkripsi: hindari duplikasi namun jangan menolak data siswa itu sendiri.
            'nik' => array_merge(
                ['required', 'string', 'max:50'],
                StoreStudentRequest::uniqueNikRule($studentId > 0 ? (int) $studentId : null)
            ),
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'nama_panggilan' => ['nullable', 'string', 'max:50'],
            'jenis_kelamin' => ['required', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', 'string', 'max:50'],
            'status_perkawinan' => ['nullable', 'string', 'max:50'],

            // Bagian A
            'nomor_akta_lahir' => ['nullable', 'string', 'max:100'],
            'nomor_kk' => ['nullable', 'string', 'max:50'],
            'urutan_anak' => ['nullable', 'integer', 'min:1'],
            'foto' => ['nullable', 'image', 'max:2048'],

            // Bagian B
            'alamat' => ['required', 'string'],
            'rt' => ['nullable', 'string', 'max:10'],
            'rw' => ['nullable', 'string', 'max:10'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kabupaten' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'titik_koordinat' => ['nullable', 'string', 'max:100'],
            'jenis_tempat_tinggal' => ['nullable', 'string', 'max:100'],
            'alat_transportasi' => ['nullable', 'string', 'max:100'],
            'jarak_rumah' => ['nullable', 'string', 'max:50'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],

            // Bagian C
            'kebutuhan_khusus' => ['nullable', 'string', 'max:100'],
            'berat_badan' => ['nullable', 'numeric'],
            'tinggi_badan' => ['nullable', 'numeric'],
            'lingkar_kepala' => ['nullable', 'numeric'],

            // Bagian E
            'kps_status' => ['nullable', 'boolean'],
            'nomor_kps' => ['nullable', 'string', 'max:100'],
            'pkh_status' => ['nullable', 'boolean'],
            'kip_status' => ['nullable', 'boolean'],
            'nama_kip' => ['nullable', 'string', 'max:150'],
            'kks_nomor' => ['nullable', 'string', 'max:100'],
            'pip_kelayakan' => ['nullable', 'boolean'],
            'pip_alasan' => ['nullable', 'string', 'max:255'],
            'bank_nama' => ['nullable', 'string', 'max:100'],
            'rekening_nomor' => ['nullable', 'string', 'max:100'],
            'rekening_nama' => ['nullable', 'string', 'max:150'],

            // Bagian F
            'sekolah_asal' => ['nullable', 'string', 'max:200'],
            'nomor_peserta_un' => ['nullable', 'string', 'max:100'],
            'ijazah_nomor' => ['nullable', 'string', 'max:100'],
            'skhun_nomor' => ['nullable', 'string', 'max:100'],

            // Organisasi
            'program_id' => ['required', 'exists:programs,id'],
            'tahun_ajaran_id' => ['required', 'exists:academic_years,id'],
            'rombel_id' => $rombelRules,
            'status' => $isScoped
                ? ['required', 'string', Rule::in(['aktif', 'nonaktif'])]
                : ['required', 'string', 'max:30'],

            // Legacy keluarga
            'nama_ayah' => ['nullable', 'string', 'max:150'],
            'nama_ibu' => ['nullable', 'string', 'max:150'],
            'nama_wali' => ['nullable', 'string', 'max:150'],
            'no_hp_wali' => ['nullable', 'string', 'max:30'],
            'alamat_wali' => ['nullable', 'string'],

            'catatan' => ['nullable', 'string'],

            // Bagian D — parent_details (nested)
            'parent_details' => ['nullable', 'array'],
            'parent_details.*.relationship_type' => ['nullable', 'string', 'in:ayah,ibu,wali'],
            'parent_details.*.name' => ['nullable', 'string', 'max:150'],
            'parent_details.*.nik' => ['nullable', 'string', 'max:50'],
            'parent_details.*.birth_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'parent_details.*.education' => ['nullable', 'string', 'max:100'],
            'parent_details.*.occupation' => ['nullable', 'string', 'max:150'],
            'parent_details.*.monthly_income' => ['nullable', 'numeric', 'min:0'],
            'parent_details.*.phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kps_status' => $this->boolean('kps_status'),
            'pkh_status' => $this->boolean('pkh_status'),
            'kip_status' => $this->boolean('kip_status'),
            'pip_kelayakan' => $this->boolean('pip_kelayakan'),
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rombel_id.required' => 'Pilih rombel tempat siswa akan ditempatkan.',
            'status.in' => 'Status siswa hanya dapat diubah ke "aktif" atau "nonaktif" oleh wali kelas.',
        ];
    }
}
