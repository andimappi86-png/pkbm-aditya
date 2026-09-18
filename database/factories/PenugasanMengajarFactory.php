<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Tutor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PenugasanMengajar>
 */
class PenugasanMengajarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tutor_id' => Tutor::factory(),
            'subject_id' => Subject::factory(),
            'classroom_id' => Classroom::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'semester_id' => Semester::factory(),
            'status_aktif' => true,
        ];
    }
}
