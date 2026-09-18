<?php

namespace Database\Factories;

use App\Models\PenugasanMengajar;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'penugasan_mengajar_id' => PenugasanMengajar::factory(),
            'judul' => $this->faker->sentence(),
            'deskripsi' => $this->faker->paragraph(),
            'tipe' => $this->faker->randomElement(['pdf', 'video', 'teks', 'link']),
            'isi_materi' => $this->faker->text(500),
            'urutan' => $this->faker->numberBetween(1, 10),
            'status_aktif' => true,
        ];
    }
}
