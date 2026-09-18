<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'schedule_id' => Schedule::factory(),
            'student_id' => Student::factory(),
            'tanggal' => $this->faker->date(),
            'status' => $this->faker->randomElement(['hadir', 'izin', 'sakit', 'alpa']),
            'keterangan' => $this->faker->sentence(),
        ];
    }
}
