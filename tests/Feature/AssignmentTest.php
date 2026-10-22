<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RoleCalonPesertaSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentTest extends TestCase
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

    public function test_admin_can_view_assignment_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('tugas.index'));

        $response->assertOk();
        $response->assertViewIs('assignments.index');
    }

    public function test_tutor_can_create_assignment(): void
    {
        $response = $this->actingAs($this->tutorUser)->post(route('tugas.store'), [
            'penugasan_mengajar_id' => $this->penugasan->id,
            'judul' => 'Tugas Baru',
            'type' => 'tugas',
            'deskripsi' => 'Deskripsi tugas',
            'instruksi' => 'Instruksi lengkap',
            'deadline' => now()->addDays(5)->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('tugas.index'));
        $this->assertDatabaseHas('assignments', [
            'penugasan_mengajar_id' => $this->penugasan->id,
            'judul' => 'Tugas Baru',
            'type' => 'tugas',
        ]);
    }

    public function test_student_can_view_assignment_index_for_own_classroom(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $assignment = Assignment::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
            'is_active' => true,
            'judul' => 'Tugas yang dapat diakses',
        ]);

        $response = $this->actingAs($studentUser)->get(route('tugas.index'));

        $response->assertOk()
            ->assertViewIs('assignments.index')
            ->assertSee('Tugas yang dapat diakses')
            ->assertSee(route('tugas.show', $assignment));
    }

    public function test_student_sees_empty_state_when_no_assignment_is_available(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($studentUser)->get(route('tugas.index'));

        $response->assertOk()
            ->assertSee('Belum ada Tugas/Quiz');
    }

    public function test_guest_is_redirected_to_login_from_assignment_index(): void
    {
        $this->get(route('tugas.index'))->assertRedirect(route('login'));
    }

    public function test_student_can_view_assignment_for_own_classroom(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        $ownStudent = Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $assignment = Assignment::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($studentUser)->get(route('tugas.show', $assignment));
        $response->assertOk();
    }

    public function test_student_cannot_view_assignment_from_other_classroom(): void
    {
        $otherClassroom = Classroom::factory()->create();
        $otherPenugasan = PenugasanMengajar::factory()->create([
            'tutor_id' => $this->tutor->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $otherClassroom->id,
        ]);

        $assignment = Assignment::factory()->create([
            'penugasan_mengajar_id' => $otherPenugasan->id,
            'is_active' => true,
        ]);

        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        $ownStudent = Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($studentUser)->get(route('tugas.show', $assignment));
        $response->assertRedirect()->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');
    }

    public function test_student_can_submit_assignment(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        $ownStudent = Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $assignment = Assignment::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($studentUser)->post(route('pengumpulan.store'), [
            'assignment_id' => $assignment->id,
            'jawaban' => 'Jawaban saya untuk tugas ini.',
        ]);

        $response->assertRedirect(route('tugas.show', $assignment));
        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $ownStudent->id,
            'status' => 'submitted',
        ]);
    }

    public function test_student_cannot_submit_duplicate(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        $ownStudent = Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $assignment = Assignment::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
        ]);

        Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $ownStudent->id,
        ]);

        $response = $this->actingAs($studentUser)->post(route('pengumpulan.store'), [
            'assignment_id' => $assignment->id,
            'jawaban' => 'Jawaban kedua.',
        ]);

        $response->assertSessionHasErrors(['assignment_id']);
    }

    public function test_tutor_can_grade_submission(): void
    {
        $submission = Submission::factory()->create([
            'assignment_id' => Assignment::factory()->create(['penugasan_mengajar_id' => $this->penugasan->id])->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->tutorUser)->put(route('pengumpulan.update', $submission), [
            'nilai' => 85,
            'feedback' => 'Bagus, tapi perlu diperbaiki bagian ke-2.',
        ]);

        $response->assertRedirect(route('tugas.show', $submission->assignment));
        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'nilai' => 85,
            'status' => 'graded',
        ]);
    }

    public function test_tutor_cannot_manage_other_tutor_submission(): void
    {
        $otherTutorUser = User::factory()->create();
        $otherTutorUser->assignRole('tutor');
        $otherTutor = Tutor::factory()->create(['user_id' => $otherTutorUser->id]);

        $otherPenugasan = PenugasanMengajar::factory()->create([
            'tutor_id' => $otherTutor->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
        ]);

        $otherAssignment = Assignment::factory()->create(['penugasan_mengajar_id' => $otherPenugasan->id]);
        $submission = Submission::factory()->create([
            'assignment_id' => $otherAssignment->id,
            'student_id' => $this->student->id,
        ]);

        $response = $this->actingAs($this->tutorUser)->put(route('pengumpulan.update', $submission), [
            'nilai' => 90,
        ]);

        $response->assertRedirect()->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');
    }

    public function test_admin_can_update_assignment(): void
    {
        $assignment = Assignment::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
            'judul' => 'Judul Lama',
        ]);

        $response = $this->actingAs($this->admin)->put(route('tugas.update', $assignment), [
            'judul' => 'Judul Baru',
            'type' => 'tugas',
            'deskripsi' => 'Deskripsi',
            'instruksi' => 'Instruksi',
        ]);

        $response->assertRedirect(route('tugas.index'));
        $this->assertDatabaseHas('assignments', [
            'id' => $assignment->id,
            'judul' => 'Judul Baru',
        ]);
    }

    public function test_admin_can_delete_assignment(): void
    {
        $assignment = Assignment::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('tugas.destroy', $assignment));

        $response->assertRedirect(route('tugas.index'));
        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
    }

    public function test_student_without_linked_student_record_can_open_assignment_index(): void
    {
        Assignment::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
            'is_active' => true,
            'judul' => 'Tugas Kelas Lain',
        ]);

        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');

        $response = $this->actingAs($studentUser)->get(route('tugas.index'));

        $response->assertOk()
            ->assertViewIs('assignments.index')
            ->assertSee('belum terhubung dengan data peserta didik')
            ->assertDontSee('Tugas Kelas Lain');
    }

    public function test_student_without_rombel_can_open_assignment_index(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => null,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($studentUser)->get(route('tugas.index'));

        $response->assertOk()
            ->assertSee('belum ditempatkan pada rombel')
            ->assertSee('Belum ada Tugas/Quiz');
    }

    public function test_student_only_sees_assignments_of_own_classroom_in_index(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        Assignment::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
            'is_active' => true,
            'judul' => 'Tugas Rombel Sendiri',
        ]);

        $otherPenugasan = PenugasanMengajar::factory()->create([
            'tutor_id' => $this->tutor->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => Classroom::factory()->create()->id,
        ]);
        Assignment::factory()->create([
            'penugasan_mengajar_id' => $otherPenugasan->id,
            'is_active' => true,
            'judul' => 'Tugas Rombel Lain',
        ]);

        $response = $this->actingAs($studentUser)->get(route('tugas.index'));

        $response->assertOk()
            ->assertSee('Tugas Rombel Sendiri')
            ->assertDontSee('Tugas Rombel Lain');
    }

    public function test_student_without_linked_student_record_cannot_open_assignment_detail(): void
    {
        $assignment = Assignment::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
            'is_active' => true,
        ]);

        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');

        $response = $this->actingAs($studentUser)->get(route('tugas.show', $assignment));

        $response->assertRedirect(route('dashboard.siswa'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');
    }

    public function test_student_cannot_open_assignment_management_pages(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($studentUser)->get(route('tugas.create'))
            ->assertRedirect(route('dashboard.siswa'));
    }

    public function test_student_dashboard_menu_points_to_assignment_index(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($studentUser)->get(route('dashboard.siswa'));

        $response->assertOk()
            ->assertSee('href="'.route('tugas.index').'"', false);
    }

    public function test_role_without_assignment_permission_is_denied(): void
    {
        $this->seed(RoleCalonPesertaSeeder::class);

        $calonUser = User::factory()->create();
        $calonUser->assignRole('calon-peserta');

        $response = $this->actingAs($calonUser)->get(route('tugas.index'));

        $response->assertRedirect(route('announcements.index'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');
    }

    public function test_tutor_only_sees_assignments_of_own_teaching_assignment(): void
    {
        Assignment::factory()->create([
            'penugasan_mengajar_id' => $this->penugasan->id,
            'is_active' => true,
            'judul' => 'Tugas Milik Tutor',
        ]);

        $otherTutorUser = User::factory()->create();
        $otherTutorUser->assignRole('tutor');
        $otherTutor = Tutor::factory()->create(['user_id' => $otherTutorUser->id]);

        $otherPenugasan = PenugasanMengajar::factory()->create([
            'tutor_id' => $otherTutor->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
        ]);
        Assignment::factory()->create([
            'penugasan_mengajar_id' => $otherPenugasan->id,
            'is_active' => true,
            'judul' => 'Tugas Tutor Lain',
        ]);

        $response = $this->actingAs($this->tutorUser)->get(route('tugas.index'));

        $response->assertOk()
            ->assertSee('Tugas Milik Tutor')
            ->assertDontSee('Tugas Tutor Lain');
    }
}
