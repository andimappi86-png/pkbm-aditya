<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Letter;
use App\Models\PenugasanMengajar;
use App\Models\Program;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UiPhase3RenderTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = ['letters.manage', 'classrooms.manage', 'classrooms.view'];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        $this->operator = User::factory()->create();
        $this->operator->assignRole($role);
    }

    public function test_halaman_edit_surat_dapat_dirender(): void
    {
        $student = Student::factory()->create();
        $letter = Letter::factory()->create(['student_id' => $student->id]);

        $this->actingAs($this->operator)
            ->get(route('letters.edit', $letter))
            ->assertOk()
            ->assertSee('Edit Surat Keterangan')
            ->assertSee($letter->nomor_surat);
    }

    public function test_halaman_detail_penugasan_mengajar_dapat_dirender(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create();
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $tutor = Tutor::factory()->create();
        $penugasan = PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.show', $penugasan))
            ->assertOk()
            ->assertSee('Detail Penugasan Mengajar')
            ->assertSee($classroom->nama_rombel)
            ->assertSee($subject->nama);
    }

    public function test_halaman_penugasan_tutor_dapat_dirender(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $tutor = Tutor::factory()->create();

        $classroom->tutors()->attach($tutor->id);
        $classroom->subjects()->attach($subject->id);

        $this->actingAs($this->operator)
            ->get(route('classroom-assignments.tutor-assignments', $tutor))
            ->assertOk()
            ->assertSee('Penugasan Tutor')
            ->assertSee($classroom->nama_rombel)
            ->assertSee($subject->nama);
    }
}
