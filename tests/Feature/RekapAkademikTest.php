<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekapAkademikTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $tutorUser;

    private Tutor $tutor;

    private Classroom $classroom;

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
        $subject = Subject::factory()->create();

        $penugasan = PenugasanMengajar::factory()->create([
            'tutor_id' => $this->tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $this->classroom->id,
        ]);

        $this->student = Student::factory()->create([
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);
    }

    public function test_admin_can_view_rekap_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('rekap.index'));

        $response->assertOk();
        $response->assertViewIs('rekap.index');
    }

    public function test_admin_can_view_student_rekap_detail(): void
    {
        $response = $this->actingAs($this->admin)->get(route('rekap.show', $this->student));

        $response->assertOk();
        $response->assertViewIs('rekap.show');
        $response->assertSee($this->student->nama_lengkap);
    }

    public function test_student_can_view_own_rekap_detail(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        $ownStudent = Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($studentUser)->get(route('rekap.show', $ownStudent));
        $response->assertOk();
    }

    public function test_student_cannot_view_other_student_rekap_detail(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        $ownStudent = Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($studentUser)->get(route('rekap.show', $this->student));
        $response->assertForbidden();
    }

    public function test_rekap_filters(): void
    {
        $response = $this->actingAs($this->admin)->get(route('rekap.index', [
            'classroom_id' => $this->classroom->id,
            'keyword' => $this->student->nama_lengkap,
        ]));

        $response->assertOk();
        $response->assertSee($this->student->nama_lengkap);
    }
}
