<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'kode' => fake()->unique()->bothify('MAP-###'),
            'nama' => fake()->unique()->words(3, true),
            'deskripsi' => fake()->optional()->sentence(),
            'status_aktif' => true,
        ];
    }
}
