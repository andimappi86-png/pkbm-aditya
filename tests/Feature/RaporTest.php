<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\PenugasanMengajar;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaporTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $tutorUser;

    private Tutor $tutor;

    private Classroom $classroom;

    private Student $student;

    private AcademicYear $academicYear;

    private Semester $semester;

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
        $this->academicYear = AcademicYear::factory()->create();
        $this->semester = Semester::factory()->create();
        $subject = Subject::factory()->create();

        $penugasan = PenugasanMengajar::factory()->create([
            'tutor_id' => $this->tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
        ]);

        $this->student = Student::factory()->create([
            'rombel_id' => $this->classroom->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'status' => 'aktif',
        ]);

        Grade::factory()->create([
            'penugasan_mengajar_id' => $penugasan->id,
            'student_id' => $this->student->id,
            'nilai_akhir' => 88,
        ]);
    }

    public function test_admin_can_view_rapor_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('rapor.index'));

        $response->assertOk();
        $response->assertViewIs('rapor.index');
    }

    public function test_admin_can_view_student_rapor(): void
    {
        $response = $this->actingAs($this->admin)->get(route('rapor.show', $this->student));

        $response->assertOk();
        $response->assertViewIs('rapor.show');
        $response->assertSee($this->student->nama_lengkap);
    }

    public function test_admin_can_view_student_rapor_print_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('rapor.print', $this->student));

        $response->assertOk();
        $response->assertViewIs('rapor.print');
        $response->assertSee($this->student->nama_lengkap);
    }

    public function test_student_can_view_own_rapor(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        $ownStudent = Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($studentUser)->get(route('rapor.show', $ownStudent));
        $response->assertOk();
    }

    public function test_student_cannot_view_other_student_rapor(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        $ownStudent = Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($studentUser)->get(route('rapor.show', $this->student));
        $response->assertRedirect()->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');
    }
}
