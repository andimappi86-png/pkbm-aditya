<?php

namespace Database\Factories;

use App\Models\PenugasanMengajar;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'penugasan_mengajar_id' => PenugasanMengajar::factory(),
            'judul' => $this->faker->sentence(3),
            'deskripsi' => $this->faker->paragraph(),
            'durasi_menit' => 30,
            'status_aktif' => true,
        ];
    }
}
