<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Program;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuizAttemptIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private User $studentAUser;

    private User $studentBUser;

    private User $tutorA;

    private Classroom $classroomA;

    private Classroom $classroomB;

    private Quiz $quizA;

    private Quiz $quizB;

    private Student $studentA;

    private Student $studentB;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = [
            'students.view', 'classrooms.view', 'classrooms.manage',
            'quizzes.view', 'quizzes.manage', 'quizzes.take',
            'materials.view', 'materials.manage',
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'peserta-didik', 'guard_name' => 'web'])
            ->syncPermissions($permissions);
        Role::firstOrCreate(['name' => 'tutor', 'guard_name' => 'web'])
            ->syncPermissions([
                'students.view', 'classrooms.view', 'quizzes.view', 'quizzes.manage', 'materials.view', 'materials.manage',
            ]);
        Role::firstOrCreate(['name' => 'wali-kelas', 'guard_name' => 'web'])
            ->syncPermissions(['students.view', 'classrooms.view', 'classrooms.manage', 'quizzes.view', 'materials.view']);

        $this->studentAUser = $this->userWithRole('peserta-didik');
        $this->studentBUser = $this->userWithRole('peserta-didik');
        $this->tutorA = $this->userWithRole('tutor');
        $waliA = $this->userWithRole('wali-kelas');
        $waliB = $this->userWithRole('wali-kelas');

        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create();
        $subject = Subject::factory()->create();

        $this->classroomA = Classroom::factory()->create([
            'wali_kelas_id' => $waliA->id,
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $this->classroomB = Classroom::factory()->create([
            'wali_kelas_id' => $waliB->id,
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);

        $tutorProfile = Tutor::factory()->create(['user_id' => $this->tutorA->id]);

        $penugasanA = PenugasanMengajar::factory()->create([
            'tutor_id' => $tutorProfile->id,
            'subject_id' => $subject->id,
            'classroom_id' => $this->classroomA->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);
        $penugasanB = PenugasanMengajar::factory()->create([
            'tutor_id' => $tutorProfile->id,
            'subject_id' => $subject->id,
            'classroom_id' => $this->classroomB->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        $this->studentA = Student::factory()->create([
            'user_id' => $this->studentAUser->id,
            'rombel_id' => $this->classroomA->id,
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $this->studentB = Student::factory()->create([
            'user_id' => $this->studentBUser->id,
            'rombel_id' => $this->classroomB->id,
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);

        $this->quizA = Quiz::factory()->create([
            'penugasan_mengajar_id' => $penugasanA->id,
            'status_aktif' => true,
        ]);
        $this->quizB = Quiz::factory()->create([
            'penugasan_mengajar_id' => $penugasanB->id,
            'status_aktif' => true,
        ]);

        Artisan::call('permission:cache-reset');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_duplicate_start_returns_same_attempt(): void
    {
        // Ensure no pre-existing attempt
        QuizAttempt::where('quiz_id', $this->quizA->id)
            ->where('student_id', $this->studentA->id)
            ->delete();

        $first = $this->actingAs($this->studentAUser)
            ->post(route('quiz.attempts.start', $this->quizA));

        $first->assertRedirect();
        /** @var QuizAttempt $attempt */
        $attempt = QuizAttempt::where('quiz_id', $this->quizA->id)
            ->where('student_id', $this->studentA->id)
            ->firstOrFail();
        $first->assertRedirect(route('quiz.attempts.show', [$this->quizA, $attempt]));

        $second = $this->actingAs($this->studentAUser)
            ->post(route('quiz.attempts.start', $this->quizA));

        $second->assertRedirect($first->headers->get('Location'));

        $this->assertEquals(1, QuizAttempt::where('quiz_id', $this->quizA->id)
            ->where('student_id', $this->studentA->id)
            ->count());
    }

    public function test_student_without_quizzes_take_permission_cannot_start(): void
    {
        Role::findByName('peserta-didik', 'web')->revokePermissionTo('quizzes.take');
        Artisan::call('permission:cache-reset');

        $this->actingAs($this->studentAUser)
            ->post(route('quiz.attempts.start', $this->quizA))
            ->assertRedirect(route('dashboard.siswa'));

        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    public function test_student_cannot_start_quiz_outside_own_classroom(): void
    {
        $response = $this->actingAs($this->studentAUser)
            ->post(route('quiz.attempts.start', $this->quizB));

        $response->assertRedirect(route('dashboard.siswa'));
    }

    public function test_student_cannot_start_quiz_without_student_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('peserta-didik');

        $response = $this->actingAs($user)
            ->post(route('quiz.attempts.start', $this->quizA));

        $response->assertRedirect(route('dashboard.siswa'));
    }

    public function test_tutor_and_wali_kelas_cannot_start_quiz(): void
    {
        $this->actingAs($this->tutorA)
            ->post(route('quiz.attempts.start', $this->quizA))
            ->assertRedirect(route('dashboard.tutor'));

        $wali = $this->userWithRole('wali-kelas');
        $this->actingAs($wali)
            ->post(route('quiz.attempts.start', $this->quizA))
            ->assertRedirect(route('dashboard.walikelas'));
    }

    public function test_attempt_show_prevents_cross_classroom_access(): void
    {
        // Pre-create attempt for studentA
        $attempt = QuizAttempt::factory()->create([
            'quiz_id' => $this->quizA->id,
            'student_id' => $this->studentA->id,
        ]);

        $this->actingAs($this->studentAUser)
            ->get(route('quiz.attempts.show', [$this->quizA, $attempt]))
            ->assertOk();

        $this->actingAs($this->studentBUser)
            ->get(route('quiz.attempts.show', [$this->quizA, $attempt]))
            ->assertRedirect(route('dashboard.siswa'));

        // Wali from other classroom cannot view
        $waliB = $this->userWithRole('wali-kelas');
        $this->actingAs($waliB)
            ->get(route('quiz.attempts.show', [$this->quizA, $attempt]))
            ->assertRedirect(route('dashboard.walikelas'));
    }

    public function test_attempt_mismatch_returns_404(): void
    {
        $attemptB = QuizAttempt::factory()->create([
            'quiz_id' => $this->quizB->id,
            'student_id' => $this->studentB->id,
        ]);

        $this->actingAs($this->studentAUser)
            ->get(route('quiz.attempts.show', [$this->quizA, $attemptB]))
            ->assertNotFound();
    }

    public function test_completed_attempt_does_not_allow_new_start(): void
    {
        QuizAttempt::where('quiz_id', $this->quizA->id)
            ->where('student_id', $this->studentA->id)
            ->delete();

        $attempt = QuizAttempt::factory()->create([
            'quiz_id' => $this->quizA->id,
            'student_id' => $this->studentA->id,
            'selesai' => true,
        ]);

        $response = $this->actingAs($this->studentAUser)
            ->post(route('quiz.attempts.start', $this->quizA));

        $response->assertRedirect(route('quiz.attempts.show', [$this->quizA, $attempt]));

        $this->assertEquals(1, QuizAttempt::where('quiz_id', $this->quizA->id)
            ->where('student_id', $this->studentA->id)
            ->count());
    }
}
