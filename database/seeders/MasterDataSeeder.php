<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Program;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'kode' => 'PA',
                'nama' => 'Paket A',
                'deskripsi' => 'Program pendidikan kesetaraan setara SD/MI.',
            ],
            [
                'kode' => 'PB',
                'nama' => 'Paket B',
                'deskripsi' => 'Program pendidikan kesetaraan setara SMP/MTs.',
            ],
            [
                'kode' => 'PC',
                'nama' => 'Paket C',
                'deskripsi' => 'Program pendidikan kesetaraan setara SMA/MA.',
            ],
        ];

        foreach ($programs as $program) {
            Program::updateOrCreate(
                ['kode' => $program['kode']],
                [
                    'nama' => $program['nama'],
                    'deskripsi' => $program['deskripsi'],
                    'status_aktif' => true,
                ],
            );
        }

        $academicYear = AcademicYear::updateOrCreate(
            ['nama' => '2026/2027'],
            [
                'tanggal_mulai' => '2026-07-01',
                'tanggal_selesai' => '2027-06-30',
                'status_aktif' => true,
            ],
        );

        $semesters = [
            ['kode' => 'GJL', 'nama_semester' => 'Ganjil'],
            ['kode' => 'GNP', 'nama_semester' => 'Genap'],
        ];

        foreach ($semesters as $semester) {
            Semester::updateOrCreate(
                [
                    'tahun_ajaran_id' => $academicYear->id,
                    'kode' => $semester['kode'],
                ],
                [
                    'nama_semester' => $semester['nama_semester'],
                    'status_aktif' => true,
                ],
            );
        }
    }
}
