<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Student;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'student_id' => Student::factory(),
            'jawaban' => $this->faker->paragraph(),
            'submitted_at' => now(),
            'status' => 'submitted',
        ];
    }
}
