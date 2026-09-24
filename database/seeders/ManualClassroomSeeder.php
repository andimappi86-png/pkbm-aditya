<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;

class ManualClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::firstOrCreate(
            ['nama' => '2026/2027'],
            [
                'tanggal_mulai' => '2026-07-01',
                'tanggal_selesai' => '2027-06-30',
                'status_aktif' => true,
            ]
        );

        $programA = Program::firstOrCreate(['nama' => 'Paket A']);
        $programB = Program::firstOrCreate(['nama' => 'Paket B']);
        $programC = Program::firstOrCreate(['nama' => 'Paket C']);

        $rombelData = [
            [
                'nama_rombel' => 'Paket A Tingkat 4',
                'kode_rombel' => 'PA-4-2627',
                'program_id' => $programA->id,
                'wali_kelas' => 'Ahmad Fadli',
            ],
            [
                'nama_rombel' => 'Paket A Tingkat 5',
                'kode_rombel' => 'PA-5-2627',
                'program_id' => $programA->id,
                'wali_kelas' => 'Citra Lestari',
            ],
            [
                'nama_rombel' => 'Paket A Tingkat 6',
                'kode_rombel' => 'PA-6-2627',
                'program_id' => $programA->id,
                'wali_kelas' => 'Eko Prasetyo',
            ],
            [
                'nama_rombel' => 'Paket B Tingkat 7',
                'kode_rombel' => 'PB-7-2627',
                'program_id' => $programB->id,
                'wali_kelas' => 'Gita Maharani',
            ],
            [
                'nama_rombel' => 'Paket B Tingkat 8',
                'kode_rombel' => 'PB-8-2627',
                'program_id' => $programB->id,
                'wali_kelas' => 'Hendra Wijaya',
            ],
            [
                'nama_rombel' => 'Paket B Tingkat 9',
                'kode_rombel' => 'PB-9-2627',
                'program_id' => $programB->id,
                'wali_kelas' => 'Indah Permata',
            ],
            [
                'nama_rombel' => 'Paket C Tingkat 10',
                'kode_rombel' => 'PC-10-2627',
                'program_id' => $programC->id,
                'wali_kelas' => 'Joko Saputra',
            ],
            [
                'nama_rombel' => 'Paket C Tingkat 11',
                'kode_rombel' => 'PC-11-2627',
                'program_id' => $programC->id,
                'wali_kelas' => 'Putri Amelia',
            ],
            [
                'nama_rombel' => 'Paket C Tingkat 12',
                'kode_rombel' => 'PC-12-2627',
                'program_id' => $programC->id,
                'wali_kelas' => 'Rafi Maulana',
            ],
        ];

        foreach ($rombelData as $item) {
            $wali = User::firstOrCreate(
                ['email' => strtolower(str_replace(' ', '.', $item['wali_kelas'])).'@pkbm-aditya.test'],
                [
                    'name' => $item['wali_kelas'],
                    'password' => bcrypt('password123'),
                ]
            );

            Classroom::updateOrCreate(
                ['kode_rombel' => $item['kode_rombel']],
                [
                    'nama_rombel' => $item['nama_rombel'],
                    'program_id' => $item['program_id'],
                    'tahun_ajaran_id' => $academicYear->id,
                    'wali_kelas_id' => $wali->id,
                    'status_aktif' => true,
                ]
            );
        }
    }
}
