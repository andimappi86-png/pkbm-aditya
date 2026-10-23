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
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentBulkAssignTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;

    protected User $unauthorizedUser;

    protected Program $programA;

    protected Program $programB;

    protected AcademicYear $academicYear1;

    protected AcademicYear $academicYear2;

    protected Classroom $classroomA1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->unauthorizedUser = User::factory()->create();
        $this->unauthorizedUser->assignRole('peserta-didik');

        $this->programA = Program::factory()->create(['status_aktif' => true]);
        $this->programB = Program::factory()->create(['status_aktif' => true]);

        $this->academicYear1 = AcademicYear::factory()->create(['status_aktif' => true]);
        $this->academicYear2 = AcademicYear::factory()->create(['status_aktif' => true]);

        $this->classroomA1 = Classroom::factory()->create([
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear1->id,
            'status_aktif' => true,
            'nama_rombel' => 'Kelas A-1',
        ]);
    }

    public function test_unauthorized_user_cannot_access_bulk_assign(): void
    {
        $response = $this->actingAs($this->unauthorizedUser)->get(route('peserta-didik.bulk-assign'));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $jsonResponse = $this->actingAs($this->unauthorizedUser)->getJson(route('peserta-didik.bulk-assign'));
        $jsonResponse->assertStatus(403);

        $responsePost = $this->actingAs($this->unauthorizedUser)->post(route('peserta-didik.bulk-assign.process'), [
            'student_ids' => [1],
            'rombel_id' => $this->classroomA1->id,
        ]);
        $responsePost->assertRedirect();
        $responsePost->assertSessionHas('error');

        $jsonResponsePost = $this->actingAs($this->unauthorizedUser)->postJson(route('peserta-didik.bulk-assign.process'), [
            'student_ids' => [1],
            'rombel_id' => $this->classroomA1->id,
        ]);
        $jsonResponsePost->assertStatus(403);
    }

    public function test_operator_can_view_bulk_assign_page(): void
    {
        $response = $this->actingAs($this->operator)->get(route('peserta-didik.bulk-assign'));
        $response->assertStatus(200);
        $response->assertViewIs('students.bulk_assign');
    }

    public function test_valid_students_are_assigned_successfully(): void
    {
        $student1 = Student::factory()->create([
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear1->id,
            'rombel_id' => null,
            'status' => 'aktif',
        ]);
        $student2 = Student::factory()->create([
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear1->id,
            'rombel_id' => null,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->operator)->post(route('peserta-didik.bulk-assign.process'), [
            'student_ids' => [$student1->id, $student2->id],
            'rombel_id' => $this->classroomA1->id,
        ]);

        $response->assertRedirect(route('peserta-didik.bulk-assign'));
        $response->assertSessionHas('success');

        $this->assertEquals($this->classroomA1->id, $student1->fresh()->rombel_id);
        $this->assertEquals($this->classroomA1->id, $student2->fresh()->rombel_id);
    }

    public function test_students_already_assigned_are_skipped(): void
    {
        $existingClassroom = Classroom::factory()->create([
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear1->id,
            'status_aktif' => true,
        ]);

        $studentAssigned = Student::factory()->create([
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear1->id,
            'rombel_id' => $existingClassroom->id,
            'status' => 'aktif',
        ]);

        $studentUnassigned = Student::factory()->create([
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear1->id,
            'rombel_id' => null,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->operator)->post(route('peserta-didik.bulk-assign.process'), [
            'student_ids' => [$studentAssigned->id, $studentUnassigned->id],
            'rombel_id' => $this->classroomA1->id,
        ]);

        $response->assertRedirect(route('peserta-didik.bulk-assign'));

        // Assigned remains in old classroom
        $this->assertEquals($existingClassroom->id, $studentAssigned->fresh()->rombel_id);
        // Unassigned moved to target classroom
        $this->assertEquals($this->classroomA1->id, $studentUnassigned->fresh()->rombel_id);
    }

    public function test_students_with_mismatched_program_or_academic_year_are_skipped(): void
    {
        $studentDiffProgram = Student::factory()->create([
            'program_id' => $this->programB->id,
            'tahun_ajaran_id' => $this->academicYear1->id,
            'rombel_id' => null,
            'status' => 'aktif',
        ]);

        $studentDiffYear = Student::factory()->create([
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear2->id,
            'rombel_id' => null,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->operator)->post(route('peserta-didik.bulk-assign.process'), [
            'student_ids' => [$studentDiffProgram->id, $studentDiffYear->id],
            'rombel_id' => $this->classroomA1->id,
        ]);

        $response->assertRedirect(route('peserta-didik.bulk-assign'));
        $response->assertSessionHas('error');

        $this->assertNull($studentDiffProgram->fresh()->rombel_id);
        $this->assertNull($studentDiffYear->fresh()->rombel_id);
    }

    public function test_inactive_students_are_skipped(): void
    {
        $studentInactive = Student::factory()->create([
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear1->id,
            'rombel_id' => null,
            'status' => 'nonaktif',
        ]);

        $response = $this->actingAs($this->operator)->post(route('peserta-didik.bulk-assign.process'), [
            'student_ids' => [$studentInactive->id],
            'rombel_id' => $this->classroomA1->id,
        ]);

        $response->assertRedirect(route('peserta-didik.bulk-assign'));
        $response->assertSessionHas('error');

        $this->assertNull($studentInactive->fresh()->rombel_id);
    }

    public function test_assigned_students_integrate_properly_with_attendance_generation(): void
    {
        $student = Student::factory()->create([
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear1->id,
            'rombel_id' => null,
            'status' => 'aktif',
        ]);

        $tutor = Tutor::factory()->create();
        $subject = Subject::factory()->create();

        $penugasan = PenugasanMengajar::factory()->create([
            'classroom_id' => $this->classroomA1->id,
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $this->academicYear1->id,
            'status_aktif' => true,
        ]);

        $schedule = Schedule::factory()->create([
            'penugasan_mengajar_id' => $penugasan->id,
            'hari' => 'Senin',
            'status_aktif' => true,
        ]);

        // Place student into rombel
        $this->actingAs($this->operator)->post(route('peserta-didik.bulk-assign.process'), [
            'student_ids' => [$student->id],
            'rombel_id' => $this->classroomA1->id,
        ]);

        $this->assertEquals($this->classroomA1->id, $student->fresh()->rombel_id);

        // Generate attendance for a Monday (e.g., 2026-10-12)
        $date = Carbon::parse('2026-10-12');
        $attendanceService = app(AttendanceService::class);
        $result = $attendanceService->generateForDate($date);

        $this->assertEquals(1, $result['created_attendances']);
        $this->assertDatabaseHas('attendances', [
            'schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'tanggal' => '2026-10-12',
            'status' => 'hadir',
        ]);
    }
}
