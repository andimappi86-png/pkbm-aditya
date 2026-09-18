<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = Schedule::with('penugasanMengajar')->get();

        foreach ($schedules as $schedule) {
            $classroomId = $schedule->penugasanMengajar->classroom_id;
            $students = Student::where('rombel_id', $classroomId)->get();

            foreach ($students as $student) {
                Attendance::firstOrCreate(
                    [
                        'schedule_id' => $schedule->id,
                        'student_id' => $student->id,
                        'tanggal' => now()->toDateString(),
                    ],
                    [
                        'status' => 'hadir',
                        'keterangan' => 'Hadir tepat waktu',
                    ]
                );
            }
        }
    }
}
