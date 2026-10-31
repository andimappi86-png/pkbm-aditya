<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\PenugasanMengajar;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PenugasanRefactorTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected AcademicYear $academicYear;
    protected Program $program;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->academicYear = AcademicYear::factory()->create([
            'nama' => '2026/2027 Ganjil',
            'status_aktif' => true,
        ]);

        $this->program = Program::factory()->create(['status_aktif' => true]);
    }

    public function test_penetapan_wali_kelas_pada_rombel(): void
    {
        $waliUser = User::factory()->create(['name' => 'Wali Kelas 1']);
        $waliUser->assignRole('wali-kelas');

        $classroom = Classroom::factory()->create([
            'nama_rombel' => 'Paket C Tingkat 10',
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'wali_kelas_id' => null,
        ]);

        $response = $this->actingAs($this->operator)->post(route('classroom-assignments.assign-wali-kelas'), [
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $classroom->id,
            'wali_kelas_id' => $waliUser->id,
        ]);

        $response->assertRedirect(route('classroom-assignments.index', [
            'tab' => 'wali-kelas',
            'academic_year_id' => $this->academicYear->id,
        ]));

        $this->assertDatabaseHas('classrooms', [
            'id' => $classroom->id,
            'wali_kelas_id' => $waliUser->id,
        ]);

        $classroom->refresh();
        $this->assertEquals($waliUser->id, $classroom->wali_kelas_id);
        $this->assertEquals($waliUser->id, $classroom->waliKelas->id);
    }

    public function test_perubahan_wali_kelas_tidak_mengubah_penugasan_tutor(): void
    {
        $tutorUser = User::factory()->create();
        $tutorUser->assignRole('tutor');
        $tutor = Tutor::factory()->create(['user_id' => $tutorUser->id]);

        $waliLama = User::factory()->create(['name' => 'Wali Lama']);
        $waliLama->assignRole('wali-kelas');

        $waliBaru = User::factory()->create(['name' => 'Wali Baru']);
        $waliBaru->assignRole('wali-kelas');

        $classroom = Classroom::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'wali_kelas_id' => $waliLama->id,
        ]);

        $subject = Subject::factory()->create();

        // Penugasan tutor di rombel tersebut
        $penugasan = PenugasanMengajar::create([
            'academic_year_id' => $this->academicYear->id,
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'status_aktif' => true,
        ]);

        // Ubah wali kelas ke wali baru
        $response = $this->actingAs($this->operator)->post(route('classroom-assignments.assign-wali-kelas'), [
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $classroom->id,
            'wali_kelas_id' => $waliBaru->id,
        ]);

        $response->assertRedirect();

        // Rombel berganti wali kelas
        $this->assertDatabaseHas('classrooms', [
            'id' => $classroom->id,
            'wali_kelas_id' => $waliBaru->id,
        ]);

        // Penugasan tutor tetap utuh tanpa berubah
        $this->assertDatabaseHas('penugasan_mengajars', [
            'id' => $penugasan->id,
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'status_aktif' => true,
        ]);
    }

    public function test_penugasan_mengajar_satu_tutor_mengajar_beberapa_rombel(): void
    {
        $tutorUser = User::factory()->create();
        $tutorUser->assignRole('tutor');
        $tutor = Tutor::factory()->create(['user_id' => $tutorUser->id]);

        $subject = Subject::factory()->create();

        $rombel1 = Classroom::factory()->create([
            'nama_rombel' => 'Kelas X-A',
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        $rombel2 = Classroom::factory()->create([
            'nama_rombel' => 'Kelas X-B',
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        // Assign to rombel 1
        $this->actingAs($this->operator)->post(route('classroom-assignments.store-teaching'), [
            'academic_year_id' => $this->academicYear->id,
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $rombel1->id,
            'status_aktif' => 1,
        ])->assertRedirect();

        // Assign to rombel 2
        $this->actingAs($this->operator)->post(route('classroom-assignments.store-teaching'), [
            'academic_year_id' => $this->academicYear->id,
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $rombel2->id,
            'status_aktif' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('penugasan_mengajars', [
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $rombel1->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->assertDatabaseHas('penugasan_mengajars', [
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $rombel2->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
    }

    public function test_beberapa_tutor_mengajar_pada_satu_rombel(): void
    {
        $tutorUser1 = User::factory()->create();
        $tutorUser1->assignRole('tutor');
        $tutor1 = Tutor::factory()->create(['user_id' => $tutorUser1->id]);

        $tutorUser2 = User::factory()->create();
        $tutorUser2->assignRole('tutor');
        $tutor2 = Tutor::factory()->create(['user_id' => $tutorUser2->id]);

        $subjectMath = Subject::factory()->create(['nama' => 'Matematika']);
        $subjectBio = Subject::factory()->create(['nama' => 'Biologi']);

        $rombel = Classroom::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        // Tutor 1 teaches Math
        $this->actingAs($this->operator)->post(route('classroom-assignments.store-teaching'), [
            'academic_year_id' => $this->academicYear->id,
            'tutor_id' => $tutor1->id,
            'subject_id' => $subjectMath->id,
            'classroom_id' => $rombel->id,
            'status_aktif' => 1,
        ])->assertRedirect();

        // Tutor 2 teaches Biology
        $this->actingAs($this->operator)->post(route('classroom-assignments.store-teaching'), [
            'academic_year_id' => $this->academicYear->id,
            'tutor_id' => $tutor2->id,
            'subject_id' => $subjectBio->id,
            'classroom_id' => $rombel->id,
            'status_aktif' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('penugasan_mengajars', [
            'tutor_id' => $tutor1->id,
            'subject_id' => $subjectMath->id,
            'classroom_id' => $rombel->id,
        ]);

        $this->assertDatabaseHas('penugasan_mengajars', [
            'tutor_id' => $tutor2->id,
            'subject_id' => $subjectBio->id,
            'classroom_id' => $rombel->id,
        ]);
    }

    public function test_satu_orang_dapat_menjadi_wali_kelas_dan_tutor_sekaligus(): void
    {
        $person = User::factory()->create(['name' => 'Budi Santoso']);
        $person->assignRole('wali-kelas');
        $person->assignRole('tutor');

        $tutor = Tutor::factory()->create([
            'user_id' => $person->id,
            'nama_lengkap' => $person->name,
        ]);

        $rombel = Classroom::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        $subject = Subject::factory()->create();

        // Tetapkan sebagai wali kelas
        $this->actingAs($this->operator)->post(route('classroom-assignments.assign-wali-kelas'), [
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $rombel->id,
            'wali_kelas_id' => $person->id,
        ])->assertRedirect();

        // Tugaskan sebagai tutor pengampu mapel di kelas tersebut
        $this->actingAs($this->operator)->post(route('classroom-assignments.store-teaching'), [
            'academic_year_id' => $this->academicYear->id,
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $rombel->id,
            'status_aktif' => 1,
        ])->assertRedirect();

        $rombel->refresh();
        $this->assertEquals($person->id, $rombel->wali_kelas_id);

        $this->assertDatabaseHas('penugasan_mengajars', [
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $rombel->id,
        ]);
    }

    public function test_pencegahan_duplikasi_penugasan_mengajar(): void
    {
        $tutorUser = User::factory()->create();
        $tutorUser->assignRole('tutor');
        $tutor = Tutor::factory()->create(['user_id' => $tutorUser->id]);

        $subject = Subject::factory()->create();

        $rombel = Classroom::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        // Initial assignment
        $this->actingAs($this->operator)->post(route('classroom-assignments.store-teaching'), [
            'academic_year_id' => $this->academicYear->id,
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $rombel->id,
            'status_aktif' => 1,
        ])->assertRedirect();

        // Duplicate assignment attempt
        $response = $this->actingAs($this->operator)->post(route('classroom-assignments.store-teaching'), [
            'academic_year_id' => $this->academicYear->id,
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $rombel->id,
            'status_aktif' => 1,
        ]);

        $response->assertSessionHasErrors(['tutor_id']);
        $this->assertEquals(1, PenugasanMengajar::where('tutor_id', $tutor->id)
            ->where('subject_id', $subject->id)
            ->where('classroom_id', $rombel->id)
            ->count());
    }

    public function test_validasi_tahun_ajaran_dan_rombel_harus_cocok(): void
    {
        $otherYear = AcademicYear::factory()->create([
            'nama' => '2027/2028 Genap',
            'status_aktif' => false,
        ]);

        $waliUser = User::factory()->create();
        $waliUser->assignRole('wali-kelas');

        $classroom = Classroom::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id, // terdaftar di 2026/2027
        ]);

        // Submit dengan academic_year_id yang berbeda dengan classroom->tahun_ajaran_id
        $response = $this->actingAs($this->operator)->post(route('classroom-assignments.assign-wali-kelas'), [
            'academic_year_id' => $otherYear->id,
            'classroom_id' => $classroom->id,
            'wali_kelas_id' => $waliUser->id,
        ]);

        $response->assertSessionHasErrors(['classroom_id']);
    }

    public function test_toggle_status_dan_penghapusan_penugasan_mengajar(): void
    {
        $tutorUser = User::factory()->create();
        $tutorUser->assignRole('tutor');
        $tutor = Tutor::factory()->create(['user_id' => $tutorUser->id]);

        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        $pm = PenugasanMengajar::create([
            'academic_year_id' => $this->academicYear->id,
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'status_aktif' => true,
        ]);

        // Toggle to inactive
        $this->actingAs($this->operator)
            ->patch(route('classroom-assignments.toggle-teaching-status', $pm->id))
            ->assertRedirect();

        $pm->refresh();
        $this->assertFalse((bool) $pm->status_aktif);

        // Delete
        $this->actingAs($this->operator)
            ->delete(route('classroom-assignments.destroy-teaching', $pm->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('penugasan_mengajars', ['id' => $pm->id]);
    }
}
