<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'kode' => fake()->unique()->bothify('PKT-##'),
            'nama' => fake()->unique()->words(2, true),
            'deskripsi' => fake()->optional()->sentence(),
            'status_aktif' => true,
        ];
    }
}
