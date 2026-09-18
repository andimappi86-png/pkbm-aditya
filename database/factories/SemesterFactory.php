<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Semester>
 */
class SemesterFactory extends Factory
{
    protected $model = Semester::class;

    public function definition(): array
    {
        $semester = fake()->randomElement([
            ['nama_semester' => 'Ganjil', 'kode' => 'GJL'],
            ['nama_semester' => 'Genap', 'kode' => 'GNP'],
        ]);

        return [
            'tahun_ajaran_id' => AcademicYear::factory(),
            ...$semester,
            'status_aktif' => true,
        ];
    }
}
