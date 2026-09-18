<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Grade;
use App\Models\PenugasanMengajar;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $tutorUser;

    private Tutor $tutor;

    private Classroom $classroom;

    private Subject $subject;

    private PenugasanMengajar $penugasan;

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

        $this->penugasan = PenugasanMengajar::factory()->create([
            'tutor_id' => $this->tutor->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
        ]);

        $this->student = Student::factory()->create([
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);
    }

    public function test_admin_can_view_grade_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('nilai.index'));

        $response->assertOk();
        $response->assertViewIs('grades.index');
    }

    public function test_tutor_can_create_grade_for_assigned_subject(): void
    {
        $response = $this->actingAs($this->tutorUser)->post(route('nilai.store'), [
            'penugasan_mengajar_id' => $this->penugasan->id,
            'student_id' => $this->student->id,
            'nilai_tugas' => 80,
            'nilai_uts' => 85,
            'nilai_uas' => 90,
            'catatan' => 'Sangat bagus',
        ]);

        $response->assertRedirect(route('nilai.index'));
        $this->assertDatabaseHas('grades', [
            'penugasan_mengajar_id' => $this->penugasan->id,
            'student_id' => $this->student->id,
            'nilai_tugas' => 80,
            'nilai_uts' => 85,
            'nilai_uas' => 90,
        ]);
    }

    public function test_cannot_create_duplicate_grade_for_same_student_and_penugasan(): void
    {
        Grade::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
            'student_id' => $this->student->id,
        ]);

        $response = $this->actingAs($this->tutorUser)->post(route('nilai.store'), [
            'penugasan_mengajar_id' => $this->penugasan->id,
            'student_id' => $this->student->id,
            'nilai_tugas' => 90,
        ]);

        $response->assertSessionHasErrors(['student_id']);
    }

    public function test_student_must_belong_to_penugasan_classroom(): void
    {
        $otherClassroom = Classroom::factory()->create();
        $otherStudent = Student::factory()->create(['rombel_id' => $otherClassroom->id, 'status' => 'aktif']);

        $response = $this->actingAs($this->admin)->post(route('nilai.store'), [
            'penugasan_mengajar_id' => $this->penugasan->id,
            'student_id' => $otherStudent->id,
            'nilai_tugas' => 75,
        ]);

        $response->assertStatus(422);
    }

    public function test_student_can_only_view_own_grade(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        $ownStudent = Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $ownGrade = Grade::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
            'student_id' => $ownStudent->id,
        ]);

        $otherGrade = Grade::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
            'student_id' => $this->student->id,
        ]);

        // Own grade -> OK
        $response = $this->actingAs($studentUser)->get(route('nilai.show', $ownGrade));
        $response->assertOk();

        // Other student's grade -> Forbidden
        $response = $this->actingAs($studentUser)->get(route('nilai.show', $otherGrade));
        $response->assertForbidden();
    }

    public function test_tutor_can_update_grade(): void
    {
        $grade = Grade::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
            'student_id' => $this->student->id,
            'nilai_tugas' => 70,
        ]);

        $response = $this->actingAs($this->tutorUser)->put(route('nilai.update', $grade), [
            'nilai_tugas' => 95,
            'nilai_uts' => 90,
            'nilai_uas' => 90,
        ]);

        $response->assertRedirect(route('nilai.index'));
        $this->assertDatabaseHas('grades', [
            'id' => $grade->id,
            'nilai_tugas' => 95,
        ]);
    }

    public function test_tutor_can_delete_grade(): void
    {
        $grade = Grade::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
            'student_id' => $this->student->id,
        ]);

        $response = $this->actingAs($this->tutorUser)->delete(route('nilai.destroy', $grade));

        $response->assertRedirect(route('nilai.index'));
        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
    }
}
