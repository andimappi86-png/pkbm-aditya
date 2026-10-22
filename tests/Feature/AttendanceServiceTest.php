<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private Classroom $classroom;

    private AcademicYear $academicYear;

    private Schedule $schedule;

    private Student $studentInClass;

    private Student $studentOtherClass;

    protected function setUp(): void
    {
        parent::setUp();

        $program = Program::factory()->create();
        $this->academicYear = AcademicYear::factory()->create(['status_aktif' => true]);

        $this->classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        $otherClassroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        $subject = Subject::factory()->create();
        $tutor = Tutor::factory()->create();

        $pm = PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'status_aktif' => true,
        ]);

        // Schedule on Monday (Senin)
        $this->schedule = Schedule::factory()->create([
            'penugasan_mengajar_id' => $pm->id,
            'hari' => 'Senin',
            'status_aktif' => true,
        ]);

        $this->studentInClass = Student::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $this->studentOtherClass = Student::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $otherClassroom->id,
            'status' => 'aktif',
        ]);
    }

    public function test_generates_attendance_for_enrolled_students(): void
    {
        // 2026-10-12 is Monday (Senin)
        $targetDate = Carbon::parse('2026-10-12');
        $service = app(AttendanceService::class);

        $result = $service->generateForDate($targetDate);

        $this->assertEquals(1, $result['processed_schedules']);
        $this->assertEquals(1, $result['created_attendances']);

        $this->assertDatabaseHas('attendances', [
            'schedule_id' => $this->schedule->id,
            'student_id' => $this->studentInClass->id,
            'tanggal' => '2026-10-12',
        ]);

        $this->assertDatabaseMissing('attendances', [
            'schedule_id' => $this->schedule->id,
            'student_id' => $this->studentOtherClass->id,
            'tanggal' => '2026-10-12',
        ]);
    }

    public function test_idempotent_execution_does_not_duplicate_records(): void
    {
        $targetDate = Carbon::parse('2026-10-12');
        $service = app(AttendanceService::class);

        // Run once
        $service->generateForDate($targetDate);
        $this->assertDatabaseCount('attendances', 1);

        // Run twice
        $result = $service->generateForDate($targetDate);
        $this->assertEquals(0, $result['created_attendances']);
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_existing_attendance_status_is_not_overwritten(): void
    {
        $targetDate = Carbon::parse('2026-10-12');
        $service = app(AttendanceService::class);

        // Pre-create attendance with 'izin'
        Attendance::create([
            'schedule_id' => $this->schedule->id,
            'student_id' => $this->studentInClass->id,
            'tanggal' => '2026-10-12',
            'status' => 'izin',
            'keterangan' => 'Sakit demam',
        ]);

        $service->generateForDate($targetDate);

        $this->assertDatabaseHas('attendances', [
            'schedule_id' => $this->schedule->id,
            'student_id' => $this->studentInClass->id,
            'tanggal' => '2026-10-12',
            'status' => 'izin',
            'keterangan' => 'Sakit demam',
        ]);
    }

    public function test_skips_when_day_does_not_match(): void
    {
        // 2026-10-13 is Tuesday (Selasa)
        $targetDate = Carbon::parse('2026-10-13');
        $service = app(AttendanceService::class);

        $result = $service->generateForDate($targetDate);

        $this->assertEquals(0, $result['processed_schedules']);
        $this->assertEquals(0, $result['created_attendances']);
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_artisan_command_generates_attendance(): void
    {
        $this->artisan('attendance:generate', ['tanggal' => '2026-10-12'])
            ->expectsOutputToContain('Proses selesai.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('attendances', [
            'schedule_id' => $this->schedule->id,
            'student_id' => $this->studentInClass->id,
            'tanggal' => '2026-10-12',
        ]);
    }
}
