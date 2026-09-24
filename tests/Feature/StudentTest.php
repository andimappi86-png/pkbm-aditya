<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Program $program;

    private AcademicYear $year;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup base roles/permissions
        Permission::create(['name' => 'students.view']);
        Permission::create(['name' => 'students.create']);
        Permission::create(['name' => 'students.update']);
        Permission::create(['name' => 'students.delete']);

        $role = Role::create(['name' => 'kepala-pkbm']);
        $role->givePermissionTo(['students.view', 'students.create', 'students.update', 'students.delete']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('kepala-pkbm');

        $this->program = Program::factory()->create();
        $this->year = AcademicYear::factory()->create();
    }

    public function test_can_view_students_list(): void
    {
        Student::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('peserta-didik.index'));

        $response->assertStatus(200);
        $response->assertViewHas('students');
    }

    public function test_can_create_student(): void
    {
        $response = $this->actingAs($this->admin)->post(route('peserta-didik.store'), [
            'nis' => '12345',
            'nik' => '7600000000',
            'nama_lengkap' => 'Test Student',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Jl. Test',
            'no_hp' => '081234567890',
            'nama_ayah' => 'Ayah',
            'nama_ibu' => 'Ibu',
            'pekerjaan_ayah' => 'Pekerjaan',
            'pekerjaan_ibu' => 'Pekerjaan',
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('peserta-didik.index'));
        $this->assertDatabaseHas('students', [
            'nis' => '12345',
            'nama_lengkap' => 'Test Student',
        ]);
    }

    public function test_validates_unique_nis(): void
    {
        Student::factory()->create([
            'nis' => '12345',
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('peserta-didik.store'), [
            'nis' => '12345', // Duplicate
            'nik' => '7600000000',
            'nama_lengkap' => 'Another Student',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Test Address',
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
            'status' => 'aktif',
        ]);

        $response->assertSessionHasErrors('nis');
    }

    public function test_can_update_student_ignoring_unique_rule(): void
    {
        $student = Student::factory()->create([
            'nis' => '12345',
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
        ]);

        $response = $this->actingAs($this->admin)->put(route('peserta-didik.update', $student->id), [
            'nis' => '12345', // Same NIS
            'nik' => '7600000000',
            'nama_lengkap' => 'Updated Name',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Test Address',
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('peserta-didik.index'));
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'nama_lengkap' => 'Updated Name',
        ]);
    }

    public function test_wali_kelas_can_only_view_own_students(): void
    {
        $waliKelasRole = Role::create(['name' => 'wali-kelas']);
        $waliKelasRole->givePermissionTo(['students.view']);

        $teacher = User::factory()->create();
        $teacher->assignRole('wali-kelas');

        $classroom1 = Classroom::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
            'wali_kelas_id' => $teacher->id,
        ]);

        $classroom2 = Classroom::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
            'wali_kelas_id' => null, // Other classroom
        ]);

        $student1 = Student::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
            'rombel_id' => $classroom1->id,
        ]);

        $student2 = Student::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->year->id,
            'rombel_id' => $classroom2->id,
        ]);

        // View list
        $response = $this->actingAs($teacher)->get(route('peserta-didik.index'));
        $response->assertSee($student1->nama_lengkap);
        $response->assertDontSee($student2->nama_lengkap);

        // View detail own student
        $response = $this->actingAs($teacher)->get(route('peserta-didik.show', $student1->id));
        $response->assertStatus(200);

        // View detail other student (forbidden)
        $response = $this->actingAs($teacher)->get(route('peserta-didik.show', $student2->id));
        $response->assertRedirect('/dashboard/walikelas')->assertSessionHas('error');
    }
}
