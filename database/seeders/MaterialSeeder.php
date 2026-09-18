<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\PenugasanMengajar;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $penugasans = PenugasanMengajar::where('status_aktif', true)->get();

        foreach ($penugasans as $penugasan) {
            // Teks Material
            Material::factory()->create([
                'penugasan_mengajar_id' => $penugasan->id,
                'judul' => 'Bab 1: Pengantar '.$penugasan->subject->nama,
                'tipe' => 'teks',
                'urutan' => 1,
            ]);

            // PDF Material
            Material::factory()->create([
                'penugasan_mengajar_id' => $penugasan->id,
                'judul' => 'Bab 2: Modul Utama '.$penugasan->subject->nama,
                'tipe' => 'pdf',
                'file_path' => 'materials/sample.pdf',
                'urutan' => 2,
            ]);

            // Video Material
            Material::factory()->create([
                'penugasan_mengajar_id' => $penugasan->id,
                'judul' => 'Bab 3: Video Penjelasan',
                'tipe' => 'video',
                'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'urutan' => 3,
            ]);
        }
    }
}
