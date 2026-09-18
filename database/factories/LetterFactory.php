<?php

namespace Database\Factories;

use App\Models\Letter;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Letter>
 */
class LetterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'jenis_surat' => 'aktif_belajar',
            'nomor_surat' => $this->faker->unique()->numerify('###').'/PKBM-A/AKT/'.date('Y'),
            'tanggal' => $this->faker->date(),
            'tujuan' => 'Keperluan Beasiswa',
            'penandatangan_id' => User::factory(),
            'penandatangan_nama' => $this->faker->name(),
            'isi' => $this->faker->paragraphs(3, true),
        ];
    }
}
