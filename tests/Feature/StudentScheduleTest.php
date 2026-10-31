<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function studentUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('peserta-didik');

        return $user;
    }

    /**
     * @return array{0: Student, 1: Classroom, 2: Schedule}
     */
    private function studentWithSchedule(string $subjectName = 'Matematika Wajib'): array
    {
        $user = $this->studentUser();
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->create([
            'user_id' => $user->id,
            'rombel_id' => $classroom->id,
        ]);

        $subject = Subject::factory()->create(['nama' => $subjectName]);
        $penugasan = PenugasanMengajar::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'status_aktif' => true,
        ]);

        $schedule = Schedule::factory()->create([
            'penugasan_mengajar_id' => $penugasan->id,
            'status_aktif' => true,
        ]);

        return [$student->refresh(), $classroom, $schedule];
    }

    public function test_student_with_linked_profile_can_open_schedule_page(): void
    {
        [$student, , $schedule] = $this->studentWithSchedule();

        $response = $this->actingAs($student->user)->get(route('jadwal.index'));

        $response->assertOk();
        $response->assertSee($schedule->penugasanMengajar->subject->nama);
        $response->assertSessionMissing('error');
    }

    public function test_student_only_sees_schedule_of_own_classroom(): void
    {
        [$student] = $this->studentWithSchedule('Matematika Wajib');
        [, , $otherSchedule] = $this->studentWithSchedule('Biologi Terapan');

        $response = $this->actingAs($student->user)->get(route('jadwal.index'));

        $response->assertOk();
        $response->assertSee('Matematika Wajib');
        $response->assertDontSee('Biologi Terapan');
        $this->assertSame(
            [$student->rombel_id],
            $response->viewData('schedules')->pluck('penugasanMengajar.classroom_id')->unique()->values()->all(),
        );
    }

    public function test_student_without_linked_profile_gets_notice_and_no_schedule_leak(): void
    {
        $this->studentWithSchedule('Biologi Terapan');
        $user = $this->studentUser();

        $response = $this->actingAs($user)->get(route('jadwal.index'));

        $response->assertOk();
        $response->assertSee('belum tertaut ke profil peserta didik');
        $response->assertDontSee('Biologi Terapan');
        $this->assertCount(0, $response->viewData('schedules'));
    }

    public function test_student_without_classroom_gets_placement_notice(): void
    {
        $this->studentWithSchedule('Biologi Terapan');
        $user = $this->studentUser();
        Student::factory()->create(['user_id' => $user->id, 'rombel_id' => null]);

        $response = $this->actingAs($user)->get(route('jadwal.index'));

        $response->assertOk();
        $response->assertSee('belum ditempatkan pada rombel atau kelas aktif');
        $response->assertDontSee('Biologi Terapan');
        $this->assertCount(0, $response->viewData('schedules'));
    }

    public function test_student_with_classroom_but_no_schedule_gets_empty_state(): void
    {
        $user = $this->studentUser();
        $classroom = Classroom::factory()->create();
        Student::factory()->create(['user_id' => $user->id, 'rombel_id' => $classroom->id]);

        $response = $this->actingAs($user)->get(route('jadwal.index'));

        $response->assertOk();
        $response->assertSee('Belum ada data jadwal pelajaran.');
        $this->assertCount(0, $response->viewData('schedules'));
    }

    public function test_inactive_schedule_is_hidden_from_student(): void
    {
        $user = $this->studentUser();
        $classroom = Classroom::factory()->create();
        Student::factory()->create(['user_id' => $user->id, 'rombel_id' => $classroom->id]);

        $penugasan = PenugasanMengajar::factory()->create([
            'classroom_id' => $classroom->id,
            'status_aktif' => true,
        ]);
        Schedule::factory()->create([
            'penugasan_mengajar_id' => $penugasan->id,
            'status_aktif' => false,
        ]);

        $response = $this->actingAs($user)->get(route('jadwal.index'));

        $response->assertOk();
        $this->assertCount(0, $response->viewData('schedules'));
    }

    public function test_student_can_open_detail_of_own_schedule(): void
    {
        [$student, , $schedule] = $this->studentWithSchedule();

        $this->actingAs($student->user)
            ->get(route('jadwal.show', $schedule))
            ->assertOk();
    }

    public function test_student_cannot_open_detail_of_other_classroom_schedule(): void
    {
        [$student] = $this->studentWithSchedule();
        [, , $otherSchedule] = $this->studentWithSchedule('Biologi Terapan');

        $this->actingAs($student->user)
            ->get(route('jadwal.show', $otherSchedule))
            ->assertRedirect(route('dashboard.siswa'))
            ->assertSessionHas('error');
    }

    public function test_student_without_profile_is_redirected_with_informative_message_not_forbidden(): void
    {
        [, , $schedule] = $this->studentWithSchedule();
        $user = $this->studentUser();

        $response = $this->actingAs($user)->get(route('jadwal.show', $schedule));

        $response->assertRedirect(route('jadwal.index'));
        $response->assertSessionHas('error', fn ($message) => str_contains($message, 'belum tertaut ke profil peserta didik'));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('jadwal.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_schedule_permission_is_denied(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('jadwal.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');
    }

    public function test_student_dashboard_renders_for_missing_profile_and_without_classroom(): void
    {
        $noProfile = $this->studentUser();
        $this->actingAs($noProfile)->get(route('dashboard.siswa'))->assertOk()->assertSee('belum tertaut ke profil peserta didik');

        $withoutClassroom = $this->studentUser();
        Student::factory()->create(['user_id' => $withoutClassroom->id, 'rombel_id' => null]);
        $this->actingAs($withoutClassroom)->get(route('dashboard.siswa'))->assertOk()->assertSee('belum ditempatkan pada rombel atau kelas aktif');
    }

    public function test_student_dashboard_shows_schedule_after_placement(): void
    {
        [$student] = $this->studentWithSchedule('Matematika Wajib');

        $this->actingAs($student->user)
            ->get(route('dashboard.siswa'))
            ->assertOk()
            ->assertDontSee('belum ditempatkan pada rombel atau kelas aktif');
    }

    public function test_student_without_profile_cannot_see_other_students_attendance(): void
    {
        [$otherStudent, , $schedule] = $this->studentWithSchedule();

        Attendance::factory()->create([
            'student_id' => $otherStudent->id,
            'schedule_id' => $schedule->id,
        ]);

        $user = $this->studentUser();

        $response = $this->actingAs($user)->get(route('absensi.index'));

        $response->assertOk();
        $this->assertCount(0, $response->viewData('attendances'));
    }
}
