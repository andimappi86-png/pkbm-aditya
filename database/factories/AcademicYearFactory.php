<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $startYear = fake()->unique()->numberBetween(2020, 2050);

        return [
            'nama' => "{$startYear}/".($startYear + 1),
            'tanggal_mulai' => "{$startYear}-07-01",
            'tanggal_selesai' => ($startYear + 1).'-06-30',
            'status_aktif' => true,
        ];
    }
}
