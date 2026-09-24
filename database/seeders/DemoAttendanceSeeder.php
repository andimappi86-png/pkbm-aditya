<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $dates = [
            '2026-09-21',
            '2026-09-22',
            '2026-09-23',
            '2026-09-24',
            '2026-09-25',
        ];

        // eager load classroom relation
        $students = Student::with('classroom')->get();
        $totalStudents = $students->count();
        $studentsWithoutClassroom = $students->filter(fn ($s) => is_null($s->classroom))->count();

        $totalSchedules = Schedule::count();
        $validScheduleCount = 0;
        $studentsWithoutSchedule = 0;

        $this->command->info("Total students: {$totalStudents}");
        $this->command->info("Students without classroom: {$studentsWithoutClassroom}");
        $this->command->info("Total schedules: {$totalSchedules}");

        foreach ($dates as $date) {
            $dayName = Carbon::parse($date)->format('l'); // full English day name
            foreach ($students as $student) {
                if (is_null($student->classroom)) {
                    continue; // skip student lacking classroom
                }

                // schedule where day matches and belongs to student's classroom via penugasan_mengajar
                $schedule = Schedule::where('hari', $dayName)
                    ->whereHas('penugasanMengajar', function ($q) use ($student) {
                        $q->where('classroom_id', $student->classroom->id);
                    })
                    ->first();

                if (! $schedule) {
                    $studentsWithoutSchedule++;

                    continue; // no schedule for this class/day
                }
                $validScheduleCount++;

                // deterministic status based on student id
                $mod = $student->id % 20;
                if ($mod <= 15) {
                    $status = 'hadir';
                } elseif ($mod <= 17) {
                    $status = 'izin';
                } elseif ($mod == 18) {
                    $status = 'sakit';
                } else {
                    $status = 'alpa';
                }

                Attendance::updateOrCreate(
                    [
                        'schedule_id' => $schedule->id,
                        'student_id' => $student->id,
                        'tanggal' => $date,
                    ],
                    [
                        'status' => $status,
                        'keterangan' => null,
                    ]
                );
            }
        }

        $this->command->info("Valid schedules used: {$validScheduleCount}");
        $this->command->info("Students without matching schedule: {$studentsWithoutSchedule}");
    }
}
