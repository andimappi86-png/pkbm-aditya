<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Generate attendance records for a given date.
     * Idempotent – does not duplicate records.
     * Existing attendance rows (including status/keterangan) are left untouched.
     *
     * @param  string|Carbon  $date  Target date (Y-m-d format or Carbon instance)
     * @return array Summary of operation
     */
    public function generateForDate($date): array
    {
        $date = $date instanceof Carbon ? $date->startOfDay() : Carbon::parse($date)->startOfDay();
        $dayName = $date->locale('id')->isoFormat('dddd'); // Indonesian day name
        $summary = [
            'date' => $date->toDateString(),
            'processed_schedules' => 0,
            'created_attendances' => 0,
            'skipped' => [],
        ];

        DB::transaction(function () use ($dayName, $date, &$summary) {
            // Active schedules matching the day name
            $schedules = Schedule::where('status_aktif', true)
                ->where('hari', $dayName)
                ->with(['penugasanMengajar.classroom', 'penugasanMengajar.academicYear'])
                ->get();

            foreach ($schedules as $schedule) {
                $summary['processed_schedules']++;
                $penugasan = $schedule->penugasanMengajar;
                if (! $penugasan) {
                    $summary['skipped'][] = ['schedule_id' => $schedule->id, 'reason' => 'penugasan missing'];

                    continue;
                }
                $classroom = $penugasan->classroom;
                if (! $classroom) {
                    $summary['skipped'][] = ['schedule_id' => $schedule->id, 'reason' => 'classroom missing'];

                    continue;
                }
                // Filter students by classroom (and academic year if set)
                $studentQuery = $classroom->students();
                if ($penugasan->academic_year_id) {
                    $studentQuery->where('tahun_ajaran_id', $penugasan->academic_year_id);
                }
                /** @var Collection<int, Student> $students */
                $students = $studentQuery->get();

                foreach ($students as $student) {
                    $attendance = Attendance::firstOrCreate(
                        [
                            'schedule_id' => $schedule->id,
                            'student_id' => $student->id,
                            'tanggal' => $date->format('Y-m-d'),
                        ],
                        ['status' => 'hadir']
                    );
                    if ($attendance->wasRecentlyCreated) {
                        $summary['created_attendances']++;
                    }
                }
            }
        });

        return $summary;
    }
}
