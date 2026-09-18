<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // Require Master Data
        $program = Program::first();
        $academicYear = AcademicYear::first();

        if (! $program || ! $academicYear) {
            $this->command->warn('Program atau Tahun Ajaran belum ada. Lewati seeder Peserta Didik.');

            return;
        }

        // Ambil atau buat rombel untuk dummy data
        $classroom = Classroom::firstOrCreate(
            ['kode_rombel' => 'RBL-TEST-1'],
            [
                'nama_rombel' => 'Kelas Uji Coba',
                'program_id' => $program->id,
                'tahun_ajaran_id' => $academicYear->id,
                'status_aktif' => true,
            ]
        );

        // Buat 10 Peserta Didik secara idempotent (berdasarkan NIS)
        $studentsData = [
            ['nis' => '10001', 'nisn' => '0010000001', 'nama_lengkap' => 'Budi Santoso', 'jk' => 'Laki-laki'],
            ['nis' => '10002', 'nisn' => '0010000002', 'nama_lengkap' => 'Siti Aminah', 'jk' => 'Perempuan'],
            ['nis' => '10003', 'nisn' => '0010000003', 'nama_lengkap' => 'Agus Pratama', 'jk' => 'Laki-laki'],
            ['nis' => '10004', 'nisn' => '0010000004', 'nama_lengkap' => 'Rina Melati', 'jk' => 'Perempuan'],
            ['nis' => '10005', 'nisn' => '0010000005', 'nama_lengkap' => 'Ahmad Fauzi', 'jk' => 'Laki-laki'],
        ];

        foreach ($studentsData as $data) {
            Student::firstOrCreate(
                ['nis' => $data['nis']],
                [
                    'nisn' => $data['nisn'],
                    'nik' => '7600000000000'.rand(100, 999),
                    'nama_lengkap' => $data['nama_lengkap'],
                    'jenis_kelamin' => $data['jk'],
                    'alamat' => 'Jl. Pendidikan No. '.rand(1, 100).', Mamuju',
                    'program_id' => $program->id,
                    'tahun_ajaran_id' => $academicYear->id,
                    'rombel_id' => $classroom->id,
                    'status' => 'aktif',
                ]
            );
        }

        $this->command->info('✅ Seeded 5 dummy students for development.');
    }
}
