<?php

namespace Database\Factories;

use App\Models\PenugasanMengajar;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ForumFactory extends Factory
{
    public function definition(): array
    {
        return [
            'penugasan_mengajar_id' => PenugasanMengajar::factory(),
            'user_id' => User::factory(),
            'judul' => $this->faker->sentence(),
            'isi' => $this->faker->paragraph(3),
            'status_aktif' => true,
        ];
    }
}
