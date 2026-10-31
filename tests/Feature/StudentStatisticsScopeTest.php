<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Program;
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

class StudentStatisticsScopeTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    private User $waliA;

    private User $tutorA;

    private User $studentAUser;

    private Classroom $classroomA;

    private Classroom $classroomB;

    private Student $studentA;

    private Student $studentB;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = ['students.view', 'classrooms.view', 'classrooms.manage'];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach ([
            'operator' => $permissions,
            'wali-kelas' => ['students.view', 'classrooms.view', 'classrooms.manage'],
            'tutor' => ['students.view', 'classrooms.view'],
            'peserta-didik' => ['students.view', 'classrooms.view'],
        ] as $role => $rolePermissions) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web'])
                ->syncPermissions($rolePermissions);
        }

        $this->operator = $this->userWithRole('operator');
        $this->waliA = $this->userWithRole('wali-kelas');
        $waliB = $this->userWithRole('wali-kelas');
        $this->tutorA = $this->userWithRole('tutor');
        $this->studentAUser = $this->userWithRole('peserta-didik');
        $studentBUser = $this->userWithRole('peserta-didik');

        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create();
        $subject = Subject::factory()->create();

        $this->classroomA = Classroom::factory()->create([
            'wali_kelas_id' => $this->waliA->id,
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $this->classroomB = Classroom::factory()->create([
            'wali_kelas_id' => $waliB->id,
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);

        $tutorProfile = Tutor::factory()->create(['user_id' => $this->tutorA->id]);

        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutorProfile->id,
            'subject_id' => $subject->id,
            'classroom_id' => $this->classroomA->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        $this->studentA = Student::factory()->create([
            'user_id' => $this->studentAUser->id,
            'rombel_id' => $this->classroomA->id,
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
            'status' => 'aktif',
        ]);
        $this->studentB = Student::factory()->create([
            'user_id' => $studentBUser->id,
            'rombel_id' => $this->classroomB->id,
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
            'status' => 'nonaktif',
        ]);

        Artisan::call('permission:cache-reset');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_wali_kelas_statistics_only_count_owned_classroom_students(): void
    {
        $response = $this->actingAs($this->waliA)->get(route('peserta-didik.index'));

        $response->assertOk();
        $this->assertSame([
            'total' => 1,
            'aktif' => 1,
            'nonaktif' => 0,
        ], $response->viewData('stats'));
    }

    public function test_tutor_statistics_only_count_assigned_classroom_students(): void
    {
        $response = $this->actingAs($this->tutorA)->get(route('peserta-didik.index'));

        $response->assertOk();
        $this->assertSame([
            'total' => 1,
            'aktif' => 1,
            'nonaktif' => 0,
        ], $response->viewData('stats'));
    }

    public function test_student_statistics_only_count_own_record(): void
    {
        $response = $this->actingAs($this->studentAUser)->get(route('peserta-didik.index'));

        $response->assertOk();
        $this->assertSame([
            'total' => 1,
            'aktif' => 1,
            'nonaktif' => 0,
        ], $response->viewData('stats'));
    }

    public function test_operator_keeps_global_statistics(): void
    {
        $response = $this->actingAs($this->operator)->get(route('peserta-didik.index'));

        $response->assertOk();
        $this->assertSame([
            'total' => 2,
            'aktif' => 1,
            'nonaktif' => 1,
        ], $response->viewData('stats'));
    }

    public function test_tutor_without_profile_sees_no_students(): void
    {
        $tutorWithoutProfile = $this->userWithRole('tutor');

        $response = $this->actingAs($tutorWithoutProfile)->get(route('peserta-didik.index'));

        $response->assertOk();
        $this->assertSame([
            'total' => 0,
            'aktif' => 0,
            'nonaktif' => 0,
        ], $response->viewData('stats'));
        $this->assertCount(0, $response->viewData('students')->items());
    }

    public function test_search_does_not_expand_statistics_beyond_role_scope(): void
    {
        $response = $this->actingAs($this->waliA)
            ->get(route('peserta-didik.index', ['search' => $this->studentB->nama_lengkap]));

        $response->assertOk();
        $this->assertSame(1, $response->viewData('stats')['total']);
        $this->assertCount(0, $response->viewData('students')->items());
    }
}
