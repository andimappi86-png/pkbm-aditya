<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\UploadedFile;

class StudentRegistrationService
{
    /**
     * Simpan profil peserta didik beserta detail orang tua/wali.
     *
     * Dipakai oleh form Buku Induk dan alur "tambah siswa sekaligus buat akun"
     * supaya aturan penyimpanan profil hanya hidup di satu tempat.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $photo = null): Student
    {
        $parentDetails = $data['parent_details'] ?? [];
        unset($data['parent_details'], $data['foto']);

        if ($photo !== null) {
            $data['foto'] = $photo->store('students', 'public');
        }

        // Kolom nik/alamat/no_hp/nama_ayah/nama_ibu bersifat NOT NULL pada skema
        // (lihat migrasi alter_students_and_tutors_for_encryption), jadi nilai
        // kosong harus dikirim sebagai string kosong, bukan null.
        foreach (['nik', 'alamat', 'no_hp', 'nama_ayah', 'nama_ibu'] as $requiredColumn) {
            $data[$requiredColumn] = $data[$requiredColumn] ?? '';
        }

        $student = Student::create($data);

        // Sync nama_ayah/nama_ibu from parent_details when empty
        if (empty($student->nama_ayah) && ($ayah = collect($parentDetails)->firstWhere('relationship_type', 'ayah'))) {
            $student->update(['nama_ayah' => $ayah['name'] ?? '']);
        }

        if (empty($student->nama_ibu) && ($ibu = collect($parentDetails)->firstWhere('relationship_type', 'ibu'))) {
            $student->update(['nama_ibu' => $ibu['name'] ?? '']);
        }

        foreach ($parentDetails as $detail) {
            if (empty($detail['relationship_type'])) {
                continue;
            }

            $student->parentDetails()->updateOrCreate(
                ['relationship_type' => $detail['relationship_type']],
                $detail
            );
        }

        return $student;
    }
}
