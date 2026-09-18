<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\PenugasanMengajar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        return [
            'penugasan_mengajar_id' => PenugasanMengajar::factory(),
            'judul' => $this->faker->sentence(),
            'deskripsi' => $this->faker->paragraph(),
            'instruksi' => $this->faker->paragraphs(2, true),
            'deadline' => now()->addDays(7),
            'is_active' => true,
            'type' => 'tugas',
        ];
    }
}
