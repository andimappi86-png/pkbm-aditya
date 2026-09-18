<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    protected $model = Classroom::class;

    public function definition(): array
    {
        return [
            'nama_rombel' => fake()->unique()->words(2, true),
            'kode_rombel' => fake()->unique()->bothify('RBL-###'),
            'program_id' => Program::factory(),
            'tahun_ajaran_id' => AcademicYear::factory(),
            'wali_kelas_id' => null,
            'status_aktif' => true,
        ];
    }
}
