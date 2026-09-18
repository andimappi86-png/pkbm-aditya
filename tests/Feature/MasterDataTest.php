<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_programs(): void
    {
        $program = Program::factory()->create(['nama' => 'Paket A', 'kode' => 'PA']);

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'nama' => 'Paket A',
            'kode' => 'PA',
        ]);
    }

    public function test_can_create_academic_years_and_semesters(): void
    {
        $year = AcademicYear::factory()->create(['nama' => '2026/2027']);
        $semester = Semester::factory()->create([
            'tahun_ajaran_id' => $year->id,
            'nama_semester' => 'Ganjil',
            'kode' => 'GJL',
        ]);

        $this->assertDatabaseHas('academic_years', ['id' => $year->id]);
        $this->assertDatabaseHas('semesters', [
            'id' => $semester->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $this->assertTrue($semester->academicYear->is($year));
    }

    public function test_can_create_subjects(): void
    {
        $subject = Subject::factory()->create(['nama' => 'Matematika']);

        $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
    }

    public function test_can_create_classrooms_with_relations(): void
    {
        $teacher = User::factory()->create();
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();

        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
            'wali_kelas_id' => $teacher->id,
        ]);

        $this->assertDatabaseHas('classrooms', ['id' => $classroom->id]);
        $this->assertTrue($classroom->program->is($program));
        $this->assertTrue($classroom->academicYear->is($year));
        $this->assertTrue($classroom->homeroomTeacher->is($teacher));
    }
}
