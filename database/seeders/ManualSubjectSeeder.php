<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class ManualSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'MAT-001', 'nama' => 'Pendidikan Agama dan Budi Pekerti', 'deskripsi' => 'Kelompok: Umum, Program: Paket A/B/C', 'status_aktif' => true],
            ['kode' => 'MAT-002', 'nama' => 'Pendidikan Pancasila', 'deskripsi' => 'Kelompok: Umum, Program: Paket A/B/C', 'status_aktif' => true],
            ['kode' => 'MAT-003', 'nama' => 'Bahasa Indonesia', 'deskripsi' => 'Kelompok: Umum, Program: Paket A/B/C', 'status_aktif' => true],
            ['kode' => 'MAT-004', 'nama' => 'Matematika', 'deskripsi' => 'Kelompok: Umum, Program: Paket A/B/C', 'status_aktif' => true],
            ['kode' => 'MAT-005', 'nama' => 'Ilmu Pengetahuan Alam', 'deskripsi' => 'Kelompok: Umum, Program: Paket A/B', 'status_aktif' => true],
            ['kode' => 'MAT-006', 'nama' => 'Ilmu Pengetahuan Sosial', 'deskripsi' => 'Kelompok: Umum, Program: Paket A/B', 'status_aktif' => true],
            ['kode' => 'MAT-007', 'nama' => 'Bahasa Inggris', 'deskripsi' => 'Kelompok: Umum, Program: Paket B/C', 'status_aktif' => true],
            ['kode' => 'MAT-008', 'nama' => 'Informatika', 'deskripsi' => 'Kelompok: Keterampilan, Program: Paket B/C', 'status_aktif' => true],
            ['kode' => 'MAT-009', 'nama' => 'Sejarah Indonesia', 'deskripsi' => 'Kelompok: Umum, Program: Paket C', 'status_aktif' => true],
            ['kode' => 'MAT-010', 'nama' => 'Ekonomi', 'deskripsi' => 'Kelompok: Umum, Program: Paket C', 'status_aktif' => true],
            ['kode' => 'MAT-011', 'nama' => 'Geografi', 'deskripsi' => 'Kelompok: Umum, Program: Paket C', 'status_aktif' => true],
            ['kode' => 'MAT-012', 'nama' => 'Sosiologi', 'deskripsi' => 'Kelompok: Umum, Program: Paket C', 'status_aktif' => true],
            ['kode' => 'MAT-013', 'nama' => 'Seni dan Budaya', 'deskripsi' => 'Kelompok: Keterampilan, Program: Paket A/B/C', 'status_aktif' => true],
            ['kode' => 'MAT-014', 'nama' => 'Pendidikan Jasmani', 'deskripsi' => 'Kelompok: Keterampilan, Program: Paket A/B/C', 'status_aktif' => true],
            ['kode' => 'MAT-015', 'nama' => 'Prakarya dan Kewirausahaan', 'deskripsi' => 'Kelompok: Keterampilan, Program: Paket B/C', 'status_aktif' => true],
            ['kode' => 'MAT-016', 'nama' => 'Muatan Lokal', 'deskripsi' => 'Kelompok: Muatan Lokal, Program: Paket A/B/C', 'status_aktif' => true],
            ['kode' => 'MAT-017', 'nama' => 'Keterampilan Komputer', 'deskripsi' => 'Kelompok: Keterampilan, Program: Paket C', 'status_aktif' => true],
            ['kode' => 'MAT-018', 'nama' => 'Kewirausahaan', 'deskripsi' => 'Kelompok: Keterampilan, Program: Paket C', 'status_aktif' => true],
        ];

        foreach ($data as $item) {
            Subject::updateOrCreate(
                ['kode' => $item['kode']],
                $item
            );
        }
    }
}
