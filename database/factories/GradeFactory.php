<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\PenugasanMengajar;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grade>
 */
class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        return [
            'penugasan_mengajar_id' => PenugasanMengajar::factory(),
            'student_id' => Student::factory(),
            'nilai_tugas' => $this->faker->randomFloat(2, 60, 100),
            'nilai_uts' => $this->faker->randomFloat(2, 60, 100),
            'nilai_uas' => $this->faker->randomFloat(2, 60, 100),
            'nilai_akhir' => $this->faker->randomFloat(2, 60, 100),
            'catatan' => $this->faker->sentence(),
        ];
    }
}
