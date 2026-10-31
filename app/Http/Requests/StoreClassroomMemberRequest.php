<?php

namespace App\Http\Requests;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreClassroomMemberRequest extends FormRequest
{
    /**
     * Pastikan hanya pengguna dengan peran Wali Kelas (pada rombel yang diampunya)
     * atau Admin / Operator yang dapat menambah siswa ke rombel.
     */
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $rombelId = $this->input('rombel_id')
            ?? ($this->route('classroom') instanceof Classroom ? $this->route('classroom')->id : $this->route('classroom'));

        if (! $rombelId) {
            return false;
        }

        $classroom = Classroom::find($rombelId);
        if (! $classroom) {
            return false;
        }

        if ($user->hasAnyRole(['admin', 'operator', 'kepala-pkbm'])) {
            return true;
        }

        if ($user->hasAnyRole(['wali-kelas', 'wali_kelas', 'wali kelas'])) {
            return (int) $classroom->wali_kelas_id === (int) $user->id;
        }

        return (int) $classroom->wali_kelas_id === (int) $user->id;
    }

    /**
     * Normalisasi input sebelum validasi agar mendukung format nama/field ganda:
     * - name / nama_lengkap
     * - nisn / nomor_identitas
     * - gender (L/P) / jenis_kelamin (Laki-laki/Perempuan)
     * - rombel_id dari route atau body
     */
    protected function prepareForValidation(): void
    {
        $routeClassroom = $this->route('classroom');
        $rombelId = $this->input('rombel_id')
            ?? ($routeClassroom instanceof Classroom ? $routeClassroom->id : $routeClassroom);

        $name = $this->input('name') ?? $this->input('nama_lengkap');
        $nisn = $this->input('nisn') ?? $this->input('nomor_identitas') ?? $this->input('nis');
        $nik = $this->input('nik');
        $kontak = $this->input('kontak') ?? $this->input('no_hp');

        $rawGender = $this->input('gender') ?? $this->input('jenis_kelamin');
        $gender = null;
        if ($rawGender !== null) {
            $upper = strtoupper(trim((string) $rawGender));
            if (in_array($upper, ['L', 'LAKI-LAKI', 'LAKI - LAKI', 'PRIA', 'MALE'], true)) {
                $gender = 'L';
            } elseif (in_array($upper, ['P', 'PEREMPUAN', 'WANITA', 'FEMALE'], true)) {
                $gender = 'P';
            } else {
                $gender = $upper;
            }
        }

        $this->merge([
            'rombel_id' => $rombelId ? (int) $rombelId : null,
            'name' => $name !== null ? trim((string) $name) : null,
            'nama_lengkap' => $name !== null ? trim((string) $name) : null,
            'nisn' => $nisn !== null ? trim((string) $nisn) : null,
            'nomor_identitas' => $nisn !== null ? trim((string) $nisn) : null,
            'nik' => $nik !== null ? trim((string) $nik) : null,
            'kontak' => $kontak !== null ? trim((string) $kontak) : null,
            'no_hp' => $kontak !== null ? trim((string) $kontak) : null,
            'gender' => $gender,
            'jenis_kelamin' => $gender === 'P' ? 'Perempuan' : ($gender === 'L' ? 'Laki-laki' : $rawGender),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rombelId = $this->input('rombel_id');

        return [
            'rombel_id' => ['required', 'integer', 'exists:classrooms,id'],
            'name' => ['required', 'string', 'max:150'],
            'nisn' => [
                'required',
                'string',
                'max:30',
                function (string $attribute, mixed $value, Closure $fail) use ($rombelId): void {
                    $identitas = trim((string) $value);
                    if ($rombelId) {
                        // Cegah duplikasi siswa yang sama di dalam satu rombel yang sama
                        $duplicateInRombel = Student::query()
                            ->where('rombel_id', $rombelId)
                            ->where(function ($q) use ($identitas) {
                                $q->where('nis', $identitas)
                                    ->orWhere('nisn', $identitas);
                            })
                            ->exists();

                        if ($duplicateInRombel) {
                            $fail("Siswa dengan nomor identitas/NISN '{$identitas}' sudah terdaftar di kelas ini.");
                        }
                    }
                },
            ],
            'gender' => ['required', 'in:L,P'],
            'nik' => ['nullable', 'string', 'max:50'],
            'status_masuk' => ['nullable', 'string', 'max:50'],
            'kontak' => ['nullable', 'string', 'max:50'],
            'no_hp' => ['nullable', 'string', 'max:50'],
            'schedule_id' => ['nullable', 'integer'],
            'tanggal' => ['nullable', 'date'],
            'redirect_to' => ['nullable', 'string'],
            'subject_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 150 karakter.',
            'nisn.required' => 'NISN / Nomor Induk warga belajar wajib diisi.',
            'nisn.max' => 'NISN / Nomor Induk maksimal 30 karakter.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).',
            'rombel_id.required' => 'Kelas (rombel) wajib dipilih.',
            'rombel_id.exists' => 'Kelas (rombel) tidak ditemukan.',
        ];
    }
}
