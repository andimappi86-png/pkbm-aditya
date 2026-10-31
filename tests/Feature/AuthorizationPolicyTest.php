<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Program;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    private User $waliA;

    private User $waliB;

    private User $tutorA;

    private User $tutorB;

    private User $studentAUser;

    private User $studentBUser;

    private Program $program;

    private AcademicYear $year;

    private Semester $semester;

    private Subject $subject;

    private Classroom $classroomA;

    private Classroom $classroomB;

    private Tutor $tutorProfileA;

    private Tutor $tutorProfileB;

    private PenugasanMengajar $penugasanA;

    private PenugasanMengajar $penugasanB;

    private Student $studentA;

    private Student $studentB;

    private Assignment $assignmentA;

    private Assignment $assignmentB;

    private Submission $submissionA;

    private Submission $submissionB;

    private Quiz $quizA;

    private Quiz $quizB;

    private QuizAttempt $attemptA;

    private QuizAttempt $attemptB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRolesAndPermissions();

        $this->operator = $this->userWithRole('operator');
        $this->waliA = $this->userWithRole('wali-kelas');
        $this->waliB = $this->userWithRole('wali-kelas');
        $this->tutorA = $this->userWithRole('tutor');
        $this->tutorB = $this->userWithRole('tutor');
        $this->studentAUser = $this->userWithRole('peserta-didik');
        $this->studentBUser = $this->userWithRole('peserta-didik');

        $this->program = Program::factory()->create();
        $this->year = AcademicYear::factory()->create();
        $this->semester = Semester::factory()->create();
        $this->subject = Subject::factory()->create();

        $this->classroomA = Classroom::factory()->create([
            'wali_kelas_id' => $this->waliA->id,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
        ]);
        $this->classroomB = Classroom::factory()->create([
            'wali_kelas_id' => $this->waliB->id,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
        ]);

        $this->tutorProfileA = Tutor::factory()->create(['user_id' => $this->tutorA->id]);
        $this->tutorProfileB = Tutor::factory()->create(['user_id' => $this->tutorB->id]);

        $this->penugasanA = PenugasanMengajar::factory()->create([
            'tutor_id' => $this->tutorProfileA->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroomA->id,
            'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id,
            'status_aktif' => true,
        ]);
        $this->penugasanB = PenugasanMengajar::factory()->create([
            'tutor_id' => $this->tutorProfileB->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroomB->id,
            'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id,
            'status_aktif' => true,
        ]);

        $this->studentA = Student::factory()->create([
            'user_id' => $this->studentAUser->id,
            'rombel_id' => $this->classroomA->id,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
        ]);
        $this->studentB = Student::factory()->create([
            'user_id' => $this->studentBUser->id,
            'rombel_id' => $this->classroomB->id,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
        ]);

        $this->assignmentA = Assignment::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanA->id,
        ]);
        $this->assignmentB = Assignment::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanB->id,
        ]);

        $this->submissionA = Submission::factory()->create([
            'assignment_id' => $this->assignmentA->id,
            'student_id' => $this->studentA->id,
        ]);
        $this->submissionB = Submission::factory()->create([
            'assignment_id' => $this->assignmentB->id,
            'student_id' => $this->studentB->id,
        ]);

        $this->quizA = Quiz::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanA->id,
        ]);
        $this->quizB = Quiz::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanB->id,
        ]);

        $this->attemptA = QuizAttempt::factory()->create([
            'quiz_id' => $this->quizA->id,
            'student_id' => $this->studentA->id,
        ]);
        $this->attemptB = QuizAttempt::factory()->create([
            'quiz_id' => $this->quizB->id,
            'student_id' => $this->studentB->id,
        ]);

        Artisan::call('permission:cache-reset');
    }

    private function createRolesAndPermissions(): void
    {
        $permissions = [
            'students.view', 'students.update', 'students.delete',
            'classrooms.view', 'classrooms.create', 'classrooms.update', 'classrooms.manage',
            'assignments.view', 'assignments.manage', 'assignments.submit',
            'quizzes.view', 'quizzes.manage', 'quizzes.take',
            'materials.view', 'materials.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->syncRole('operator', $permissions);
        $this->syncRole('wali-kelas', [
            'students.view', 'classrooms.view', 'classrooms.manage',
            'assignments.view', 'quizzes.view', 'materials.view',
        ]);
        $this->syncRole('tutor', [
            'students.view', 'classrooms.view',
            'assignments.view', 'assignments.manage',
            'quizzes.view', 'quizzes.manage', 'materials.view', 'materials.manage',
        ]);
        $this->syncRole('peserta-didik', [
            'students.view', 'classrooms.view',
            'assignments.view', 'assignments.submit',
            'quizzes.view', 'quizzes.take', 'materials.view',
        ]);
    }

    /**
     * @param  list<string>  $permissions
     */
    private function syncRole(string $role, array $permissions): void
    {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web'])
            ->syncPermissions($permissions);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function classroomPayload(Classroom $classroom): array
    {
        return [
            'nama_rombel' => $classroom->nama_rombel,
            'kode_rombel' => $classroom->kode_rombel,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
            'wali_kelas_id' => $classroom->wali_kelas_id,
            'status_aktif' => true,
        ];
    }

    private function assignmentPayload(): array
    {
        return [
            'judul' => 'Judul yang diperbarui',
            'deskripsi' => 'Deskripsi',
            'instruksi' => 'Instruksi',
            'deadline' => now()->addDays(3)->toDateString(),
            'is_active' => true,
            'type' => 'tugas',
        ];
    }

    private function quizPayload(): array
    {
        return [
            'judul' => 'Kuis yang diperbarui',
            'deskripsi' => 'Deskripsi',
            'durasi_menit' => 20,
            'status_aktif' => true,
        ];
    }

    public function test_wali_kelas_can_view_own_student_but_not_other_classroom_student(): void
    {
        $this->actingAs($this->waliA)
            ->get(route('peserta-didik.show', $this->studentA))
            ->assertOk();

        $this->actingAs($this->waliA)
            ->get(route('peserta-didik.show', $this->studentB))
            ->assertRedirect(route('dashboard.walikelas'));
    }

    public function test_tutor_without_profile_cannot_view_student(): void
    {
        $tutorWithoutProfile = $this->userWithRole('tutor');

        $this->actingAs($tutorWithoutProfile)
            ->get(route('peserta-didik.show', $this->studentA))
            ->assertRedirect(route('dashboard.tutor'));
    }

    public function test_tutor_can_view_assigned_classroom_student_but_not_other(): void
    {
        $this->actingAs($this->tutorA)
            ->get(route('peserta-didik.show', $this->studentA))
            ->assertOk();

        $this->actingAs($this->tutorA)
            ->get(route('peserta-didik.show', $this->studentB))
            ->assertRedirect(route('dashboard.tutor'));
    }

    public function test_student_can_view_only_own_student_record(): void
    {
        $this->actingAs($this->studentAUser)
            ->get(route('peserta-didik.show', $this->studentA))
            ->assertOk();

        $this->actingAs($this->studentAUser)
            ->get(route('peserta-didik.show', $this->studentB))
            ->assertRedirect(route('dashboard.siswa'));
    }

    public function test_operator_can_view_students_in_both_classrooms(): void
    {
        $this->actingAs($this->operator)
            ->get(route('peserta-didik.show', $this->studentA))
            ->assertOk();

        $this->actingAs($this->operator)
            ->get(route('peserta-didik.show', $this->studentB))
            ->assertOk();
    }

    public function test_wali_kelas_can_view_owned_classroom_but_not_other(): void
    {
        $this->actingAs($this->waliA)
            ->get(route('rombel.show', $this->classroomA))
            ->assertOk();

        $this->actingAs($this->waliA)
            ->get(route('rombel.show', $this->classroomB))
            ->assertRedirect(route('dashboard.walikelas'));
    }

    public function test_tutor_can_view_assigned_classroom_but_not_other(): void
    {
        $this->actingAs($this->tutorA)
            ->get(route('rombel.show', $this->classroomA))
            ->assertOk();

        $this->actingAs($this->tutorA)
            ->get(route('rombel.show', $this->classroomB))
            ->assertRedirect(route('dashboard.tutor'));
    }

    public function test_student_can_view_own_classroom_but_not_other(): void
    {
        $this->actingAs($this->studentAUser)
            ->get(route('rombel.show', $this->classroomA))
            ->assertOk();

        $this->actingAs($this->studentAUser)
            ->get(route('rombel.show', $this->classroomB))
            ->assertRedirect(route('dashboard.siswa'));
    }

    public function test_wali_kelas_can_update_owned_classroom_but_not_other(): void
    {
        $this->actingAs($this->waliA)
            ->put(route('rombel.update', $this->classroomA), $this->classroomPayload($this->classroomA))
            ->assertRedirect(route('rombel.index'));

        $this->actingAs($this->waliA)
            ->put(route('rombel.update', $this->classroomB), $this->classroomPayload($this->classroomB))
            ->assertRedirect(route('dashboard.walikelas'));
    }

    public function test_wali_kelas_cannot_create_classroom(): void
    {
        $this->actingAs($this->waliA)
            ->get(route('rombel.create'))
            ->assertRedirect(route('dashboard.walikelas'));

        $this->actingAs($this->waliA)
            ->post(route('rombel.store'), [
                'nama_rombel' => 'Rombel Tidak Sah',
                'kode_rombel' => 'RBL-NA',
                'program_id' => $this->program->id,
                'tahun_ajaran_id' => $this->year->id,
                'status_aktif' => true,
            ])
            ->assertRedirect(route('dashboard.walikelas'));

        $this->assertDatabaseMissing('classrooms', ['kode_rombel' => 'RBL-NA']);
    }

    public function test_classroom_create_policy_rejects_wali_kelas_even_with_manage_permission(): void
    {
        $this->assertFalse(
            Gate::forUser($this->waliA)->allows('create', Classroom::class)
        );
    }

    public function test_operator_can_create_classroom(): void
    {
        $this->actingAs($this->operator)
            ->get(route('rombel.create'))
            ->assertOk();

        $this->actingAs($this->operator)
            ->post(route('rombel.store'), [
                'nama_rombel' => 'Rombel Baru',
                'kode_rombel' => 'RBL-NEW',
                'program_id' => $this->program->id,
                'tahun_ajaran_id' => $this->year->id,
                'status_aktif' => true,
            ])
            ->assertRedirect(route('rombel.index'));

        $this->assertDatabaseHas('classrooms', ['kode_rombel' => 'RBL-NEW']);
    }

    public function test_student_can_view_only_assignments_from_own_classroom(): void
    {
        $this->actingAs($this->studentAUser)
            ->get(route('tugas.show', $this->assignmentA))
            ->assertOk();

        $this->actingAs($this->studentAUser)
            ->get(route('tugas.show', $this->assignmentB))
            ->assertRedirect(route('dashboard.siswa'));
    }

    public function test_tutor_can_view_only_assignments_from_own_penugasan(): void
    {
        $this->actingAs($this->tutorA)
            ->get(route('tugas.show', $this->assignmentA))
            ->assertOk();

        $this->actingAs($this->tutorA)
            ->get(route('tugas.show', $this->assignmentB))
            ->assertRedirect(route('dashboard.tutor'));
    }

    public function test_wali_kelas_can_view_only_assignments_from_own_classroom(): void
    {
        $this->actingAs($this->waliA)
            ->get(route('tugas.show', $this->assignmentA))
            ->assertOk();

        $this->actingAs($this->waliA)
            ->get(route('tugas.show', $this->assignmentB))
            ->assertRedirect(route('dashboard.walikelas'));
    }

    public function test_tutor_can_update_own_assignment_but_not_other(): void
    {
        $this->actingAs($this->tutorA)
            ->put(route('tugas.update', $this->assignmentA), $this->assignmentPayload())
            ->assertRedirect(route('tugas.index'));

        $this->actingAs($this->tutorA)
            ->put(route('tugas.update', $this->assignmentB), $this->assignmentPayload())
            ->assertRedirect(route('dashboard.tutor'));
    }

    public function test_tutor_cannot_delete_assignment_from_other_penugasan(): void
    {
        $this->actingAs($this->tutorA)
            ->delete(route('tugas.destroy', $this->assignmentB))
            ->assertRedirect(route('dashboard.tutor'));

        $this->assertDatabaseHas('assignments', ['id' => $this->assignmentB->id]);
    }

    public function test_student_can_update_own_submission_but_not_other_submission(): void
    {
        $this->actingAs($this->studentAUser)
            ->put(route('pengumpulan.update', $this->submissionA), ['jawaban' => 'Jawaban baru'])
            ->assertRedirect(route('tugas.show', $this->assignmentA));

        $this->actingAs($this->studentAUser)
            ->put(route('pengumpulan.update', $this->submissionB), ['jawaban' => 'Ambil alih'])
            ->assertRedirect(route('dashboard.siswa'));

        $this->assertDatabaseHas('submissions', [
            'id' => $this->submissionB->id,
            'jawaban' => $this->submissionB->jawaban,
        ]);
    }

    public function test_student_cannot_update_graded_submission(): void
    {
        $this->submissionA->update(['status' => 'graded']);

        $this->actingAs($this->studentAUser)
            ->put(route('pengumpulan.update', $this->submissionA), ['jawaban' => 'Setelah dinilai'])
            ->assertRedirect(route('dashboard.siswa'));

        $this->assertDatabaseMissing('submissions', [
            'id' => $this->submissionA->id,
            'jawaban' => 'Setelah dinilai',
        ]);
    }

    public function test_tutor_can_update_submission_in_own_penugasan_but_not_other(): void
    {
        $this->actingAs($this->tutorA)
            ->put(route('pengumpulan.update', $this->submissionA), [
                'feedback' => 'Bagus',
                'nilai' => 90,
            ])
            ->assertRedirect(route('tugas.show', $this->assignmentA));

        $this->actingAs($this->tutorA)
            ->put(route('pengumpulan.update', $this->submissionB), [
                'feedback' => 'Tidak sah',
                'nilai' => 100,
            ])
            ->assertRedirect(route('dashboard.tutor'));

        $this->assertDatabaseHas('submissions', [
            'id' => $this->submissionA->id,
            'status' => 'graded',
        ]);
    }

    public function test_operator_can_update_submission_in_any_classroom(): void
    {
        $this->actingAs($this->operator)
            ->put(route('pengumpulan.update', $this->submissionB), [
                'feedback' => 'Diverifikasi operator',
                'nilai' => 85,
            ])
            ->assertRedirect(route('tugas.show', $this->assignmentB));
    }

    public function test_student_and_tutor_cannot_update_or_delete_student_records(): void
    {
        $this->actingAs($this->studentAUser)
            ->put(route('peserta-didik.update', $this->studentA), ['nama_lengkap' => 'Nama Diubah'])
            ->assertRedirect(route('dashboard.siswa'));

        $this->actingAs($this->studentAUser)
            ->delete(route('peserta-didik.destroy', $this->studentA))
            ->assertRedirect(route('dashboard.siswa'));

        $this->actingAs($this->tutorA)
            ->delete(route('peserta-didik.destroy', $this->studentA))
            ->assertRedirect(route('dashboard.tutor'));

        $this->assertDatabaseHas('students', [
            'id' => $this->studentA->id,
            'nama_lengkap' => $this->studentA->nama_lengkap,
        ]);
    }

    public function test_tutor_cannot_create_assignment_in_other_penugasan(): void
    {
        $this->actingAs($this->tutorA)
            ->post(route('tugas.store'), [
                'penugasan_mengajar_id' => $this->penugasanB->id,
                'judul' => 'Tugas lintas penugasan',
                'type' => 'tugas',
                'is_active' => true,
            ])
            ->assertRedirect(route('dashboard.tutor'));

        $this->assertDatabaseMissing('assignments', ['judul' => 'Tugas lintas penugasan']);
    }

    public function test_tutor_cannot_create_quiz_in_other_penugasan(): void
    {
        $this->actingAs($this->tutorA)
            ->post(route('quiz.store'), [
                'penugasan_mengajar_id' => $this->penugasanB->id,
                'judul' => 'Kuis lintas penugasan',
                'status_aktif' => true,
            ])
            ->assertRedirect(route('dashboard.tutor'));

        $this->assertDatabaseMissing('quizzes', ['judul' => 'Kuis lintas penugasan']);
    }

    public function test_tutor_can_create_quiz_without_manual_penugasan_id(): void
    {
        $this->actingAs($this->tutorA)
            ->post(route('quiz.store'), [
                'judul' => 'Kuis Mandiri Tutor A',
                'status_aktif' => true,
            ])
            ->assertRedirect(route('quiz.index'));

        $this->assertDatabaseHas('quizzes', [
            'judul' => 'Kuis Mandiri Tutor A',
            'penugasan_mengajar_id' => $this->penugasanA->id,
        ]);
    }

    public function test_student_cannot_submit_assignment_from_other_classroom(): void
    {
        $this->actingAs($this->studentAUser)
            ->post(route('pengumpulan.store'), [
                'assignment_id' => $this->assignmentB->id,
                'jawaban' => 'Jawaban lintas rombel',
            ])
            ->assertRedirect(route('dashboard.siswa'));

        $this->assertDatabaseMissing('submissions', [
            'assignment_id' => $this->assignmentB->id,
            'jawaban' => 'Jawaban lintas rombel',
        ]);
    }

    public function test_student_can_view_only_quizzes_from_own_classroom(): void
    {
        $this->actingAs($this->studentAUser)
            ->get(route('quiz.show', $this->quizA))
            ->assertOk();

        $this->actingAs($this->studentAUser)
            ->get(route('quiz.show', $this->quizB))
            ->assertRedirect(route('dashboard.siswa'));
    }

    public function test_tutor_can_view_only_quizzes_from_own_penugasan(): void
    {
        $this->actingAs($this->tutorA)
            ->get(route('quiz.show', $this->quizA))
            ->assertOk();

        $this->actingAs($this->tutorA)
            ->get(route('quiz.show', $this->quizB))
            ->assertRedirect(route('dashboard.tutor'));
    }

    public function test_wali_kelas_can_view_only_quizzes_from_own_classroom(): void
    {
        $this->actingAs($this->waliA)
            ->get(route('quiz.show', $this->quizA))
            ->assertOk();

        $this->actingAs($this->waliA)
            ->get(route('quiz.show', $this->quizB))
            ->assertRedirect(route('dashboard.walikelas'));
    }

    public function test_tutor_can_update_own_quiz_but_not_other(): void
    {
        $this->actingAs($this->tutorA)
            ->put(route('quiz.update', $this->quizA), $this->quizPayload())
            ->assertRedirect(route('quiz.show', $this->quizA));

        $this->actingAs($this->tutorA)
            ->put(route('quiz.update', $this->quizB), $this->quizPayload())
            ->assertRedirect(route('dashboard.tutor'));
    }

    public function test_student_can_view_only_own_quiz_attempt(): void
    {
        $this->actingAs($this->studentAUser)
            ->get(route('quiz.attempts.show', [$this->quizA, $this->attemptA]))
            ->assertOk();

        $this->actingAs($this->studentAUser)
            ->get(route('quiz.attempts.show', [$this->quizB, $this->attemptB]))
            ->assertRedirect(route('dashboard.siswa'));
    }

    public function test_wali_kelas_cannot_view_attempt_outside_own_classroom(): void
    {
        $this->actingAs($this->waliA)
            ->get(route('quiz.attempts.show', [$this->quizA, $this->attemptA]))
            ->assertOk();

        $this->actingAs($this->waliA)
            ->get(route('quiz.attempts.show', [$this->quizB, $this->attemptB]))
            ->assertRedirect(route('dashboard.walikelas'));
    }

    public function test_tutor_can_view_only_attempt_from_own_penugasan(): void
    {
        $this->actingAs($this->tutorA)
            ->get(route('quiz.attempts.show', [$this->quizA, $this->attemptA]))
            ->assertOk();

        $this->actingAs($this->tutorA)
            ->get(route('quiz.attempts.show', [$this->quizB, $this->attemptB]))
            ->assertRedirect(route('dashboard.tutor'));
    }

    public function test_attempt_route_rejects_mismatched_quiz_and_attempt(): void
    {
        $this->actingAs($this->studentAUser)
            ->get(route('quiz.attempts.show', [$this->quizA, $this->attemptB]))
            ->assertNotFound();
    }
}
