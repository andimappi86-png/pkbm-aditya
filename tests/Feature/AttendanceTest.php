<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $tutorUser;

    private Tutor $tutor;

    private Classroom $classroom;

    private Subject $subject;

    private Schedule $schedule;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('kepala-pkbm');

        $this->tutorUser = User::factory()->create();
        $this->tutorUser->assignRole('tutor');
        $this->tutor = Tutor::factory()->create(['user_id' => $this->tutorUser->id]);

        $this->classroom = Classroom::factory()->create();
        $this->subject = Subject::factory()->create();

        $pm = PenugasanMengajar::factory()->create([
            'tutor_id' => $this->tutor->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
        ]);

        $this->schedule = Schedule::factory()->create([
            'penugasan_mengajar_id' => $pm->id,
        ]);

        $this->student = Student::factory()->create([
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);
    }

    public function test_admin_can_view_attendance_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('absensi.index'));

        $response->assertOk();
        $response->assertViewIs('attendances.index');
    }

    public function test_tutor_can_view_attendance_create_for_own_schedule(): void
    {
        $response = $this->actingAs($this->tutorUser)
            ->get(route('absensi.create', ['schedule_id' => $this->schedule->id]));

        $response->assertOk();
        $response->assertViewIs('attendances.create');
    }

    public function test_admin_can_create_bulk_attendance(): void
    {
        $response = $this->actingAs($this->admin)->post(route('absensi.store'), [
            'schedule_id' => $this->schedule->id,
            'tanggal' => '2026-09-17',
            'attendances' => [
                [
                    'student_id' => $this->student->id,
                    'status' => 'hadir',
                    'keterangan' => 'Hadir tepat waktu',
                ],
            ],
        ]);

        $response->assertRedirect(route('absensi.index'));
        $this->assertDatabaseHas('attendances', [
            'schedule_id' => $this->schedule->id,
            'student_id' => $this->student->id,
            'status' => 'hadir',
        ]);
    }

    public function test_tutor_can_create_bulk_attendance_for_own_schedule(): void
    {
        $response = $this->actingAs($this->tutorUser)->post(route('absensi.store'), [
            'schedule_id' => $this->schedule->id,
            'tanggal' => '2026-09-17',
            'attendances' => [
                [
                    'student_id' => $this->student->id,
                    'status' => 'sakit',
                    'keterangan' => 'Demam',
                ],
            ],
        ]);

        $response->assertRedirect(route('absensi.index'));
        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'status' => 'sakit',
        ]);
    }

    public function test_cannot_create_duplicate_attendance(): void
    {
        Attendance::factory()->create([
            'schedule_id' => $this->schedule->id,
            'student_id' => $this->student->id,
            'tanggal' => '2026-09-17',
            'status' => 'hadir',
        ]);

        // Attempt single creation with same key
        $response = $this->actingAs($this->admin)->post(route('absensi.store'), [
            'schedule_id' => $this->schedule->id,
            'student_id' => $this->student->id,
            'tanggal' => '2026-09-17',
            'status' => 'izin',
            'keterangan' => 'Acara keluarga',
        ]);

        // Should fail validation (duplicate)
        $response->assertSessionHasErrors(['tanggal']);
    }

    public function test_student_must_belong_to_schedule_classroom(): void
    {
        $otherClassroom = Classroom::factory()->create();
        $otherStudent = Student::factory()->create(['rombel_id' => $otherClassroom->id, 'status' => 'aktif']);

        $response = $this->actingAs($this->admin)->post(route('absensi.store'), [
            'schedule_id' => $this->schedule->id,
            'student_id' => $otherStudent->id,
            'tanggal' => '2026-09-17',
            'status' => 'hadir',
            'keterangan' => null,
        ]);

        $response->assertSessionHasErrors(['student_id']);
    }

    public function test_tutor_cannot_manage_other_tutor_attendance(): void
    {
        $otherTutorUser = User::factory()->create();
        $otherTutorUser->assignRole('tutor');
        $otherTutor = Tutor::factory()->create(['user_id' => $otherTutorUser->id]);

        $otherPm = PenugasanMengajar::factory()->create([
            'tutor_id' => $otherTutor->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
        ]);
        $otherSchedule = Schedule::factory()->create(['penugasan_mengajar_id' => $otherPm->id]);

        $response = $this->actingAs($otherTutorUser)
            ->get(route('absensi.create', ['schedule_id' => $this->schedule->id]));

        $response->assertForbidden();
    }

    public function test_peserta_didik_can_only_view_own_attendance(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        $ownStudent = Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $ownAttendance = Attendance::factory()->create([
            'schedule_id' => $this->schedule->id,
            'student_id' => $ownStudent->id,
            'tanggal' => '2026-09-17',
        ]);

        // Own attendance -> OK
        $response = $this->actingAs($studentUser)->get(route('absensi.show', $ownAttendance));
        $response->assertOk();

        // Other student's attendance -> Forbidden
        $otherAttendance = Attendance::factory()->create([
            'schedule_id' => $this->schedule->id,
            'student_id' => $this->student->id,
            'tanggal' => '2026-09-18',
        ]);
        $response = $this->actingAs($studentUser)->get(route('absensi.show', $otherAttendance));
        $response->assertForbidden();
    }

    public function test_admin_can_update_attendance(): void
    {
        $attendance = Attendance::factory()->create([
            'schedule_id' => $this->schedule->id,
            'student_id' => $this->student->id,
            'tanggal' => '2026-09-17',
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($this->admin)->put(route('absensi.update', $attendance), [
            'schedule_id' => $this->schedule->id,
            'student_id' => $this->student->id,
            'tanggal' => '2026-09-17',
            'status' => 'izin',
            'keterangan' => 'Acara keluarga',
        ]);

        $response->assertRedirect(route('absensi.index'));
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'izin',
        ]);
    }

    public function test_admin_can_delete_attendance(): void
    {
        $attendance = Attendance::factory()->create([
            'schedule_id' => $this->schedule->id,
            'student_id' => $this->student->id,
            'tanggal' => '2026-09-17',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('absensi.destroy', $attendance));

        $response->assertRedirect(route('absensi.index'));
        $this->assertDatabaseMissing('attendances', ['id' => $attendance->id]);
    }
}
