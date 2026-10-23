<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('kepala-pkbm');
    }

    public function test_can_display_schedule_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('jadwal.index'));

        $response->assertOk();
        $response->assertViewIs('schedules.index');
    }

    public function test_operator_can_access_schedule_index(): void
    {
        $operator = User::factory()->create();
        $operator->assignRole('operator');

        $response = $this->actingAs($operator)->get(route('jadwal.index'));

        $response->assertOk();
        $response->assertViewIs('schedules.index');
    }

    public function test_operator_can_access_penugasan_mengajar_index(): void
    {
        $operator = User::factory()->create();
        $operator->assignRole('operator');

        $response = $this->actingAs($operator)->get(route('penugasan-mengajar.index'));

        $response->assertOk();
        $response->assertViewIs('penugasan-mengajar.index');
    }

    public function test_operator_can_access_nilai_create(): void
    {
        $operator = User::factory()->create();
        $operator->assignRole('operator');

        $response = $this->actingAs($operator)->get(route('nilai.create'));

        $response->assertOk();
    }

    public function test_operator_can_access_quiz_create(): void
    {
        $operator = User::factory()->create();
        $operator->assignRole('operator');

        $response = $this->actingAs($operator)->get(route('quiz.create'));

        $response->assertOk();
    }

    public function test_operator_can_access_materi_create(): void
    {
        $operator = User::factory()->create();
        $operator->assignRole('operator');

        $response = $this->actingAs($operator)->get(route('materi.create'));

        $response->assertOk();
    }

    public function test_user_with_schedules_view_but_no_classrooms_manage_cannot_access_penugasan_mengajar(): void
    {
        $user = User::factory()->create();
        // Give only schedules.view, not classrooms.manage
        $user->givePermissionTo('schedules.view');

        $response = $this->actingAs($user)->get(route('penugasan-mengajar.index'));

        // Should get forbidden (403) or redirect to dashboard with error
        $this->assertTrue($response->isForbidden() || $response->isRedirect());
    }

    public function test_can_create_a_schedule(): void
    {
        $tutor = Tutor::factory()->create();
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create();

        $pm = PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('jadwal.store'), [
            'penugasan_mengajar_id' => $pm->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'ruangan' => 'Ruang Kelas A',
            'status_aktif' => true,
        ]);

        $response->assertRedirect(route('jadwal.index'));
        $this->assertDatabaseHas('schedules', [
            'penugasan_mengajar_id' => $pm->id,
            'hari' => 'Senin',
            'ruangan' => 'Ruang Kelas A',
        ]);
    }

    public function test_can_update_a_schedule(): void
    {
        $tutor = Tutor::factory()->create();
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create();

        $pm = PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
        ]);

        $schedule = Schedule::factory()->create([
            'penugasan_mengajar_id' => $pm->id,
            'hari' => 'Selasa',
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
        ]);

        $response = $this->actingAs($this->admin)->put(route('jadwal.update', $schedule), [
            'penugasan_mengajar_id' => $pm->id,
            'hari' => 'Rabu',
            'jam_mulai' => '10:00',
            'jam_selesai' => '12:00',
            'ruangan' => 'Lab Komputer',
            'status_aktif' => false,
        ]);

        $response->assertRedirect(route('jadwal.index'));
        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'hari' => 'Rabu',
            'ruangan' => 'Lab Komputer',
            'status_aktif' => false,
        ]);
    }

    public function test_can_delete_a_schedule(): void
    {
        $tutor = Tutor::factory()->create();
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create();

        $pm = PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
        ]);

        $schedule = Schedule::factory()->create([
            'penugasan_mengajar_id' => $pm->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('jadwal.destroy', $schedule));

        $response->assertRedirect(route('jadwal.index'));
        $this->assertDatabaseMissing('schedules', [
            'id' => $schedule->id,
        ]);
    }
}
