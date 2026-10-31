<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ClassroomAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();

        Gate::define('classrooms.manage', function () {
            return true;
        });
    }

    public function test_index_loads_with_tutors_subjects_classrooms_relationships(): void
    {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create(['status_aktif' => true]);

        $tutor = Tutor::factory()->create(['user_id' => User::factory()->create()->id]);
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $academicYear->id,
        ]);

        $classroom->tutors()->attach($tutor->id);
        $classroom->subjects()->attach($subject->id);

        $response = $this->actingAs($this->admin)->get(route('classroom-assignments.index'));

        $response->assertStatus(200);
        $response->assertViewIs('classroom-assignments.index');
        $response->assertViewHas('assignments');
    }

    public function test_classroom_tutors_relationship_works(): void
    {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create(['status_aktif' => true]);

        $tutor = Tutor::factory()->create(['user_id' => User::factory()->create()->id]);
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $academicYear->id,
        ]);

        $classroom->tutors()->attach($tutor->id);

        $loadedClassroom = Classroom::with('tutors')->find($classroom->id);

        $this->assertNotNull($loadedClassroom->tutors);
        $this->assertCount(1, $loadedClassroom->tutors);
        $this->assertEquals($tutor->id, $loadedClassroom->tutors->first()->id);
    }

    public function test_classroom_subjects_relationship_works(): void
    {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create(['status_aktif' => true]);

        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $academicYear->id,
        ]);

        $classroom->subjects()->attach($subject->id);

        $loadedClassroom = Classroom::with('subjects')->find($classroom->id);

        $this->assertNotNull($loadedClassroom->subjects);
        $this->assertCount(1, $loadedClassroom->subjects);
        $this->assertEquals($subject->id, $loadedClassroom->subjects->first()->id);
    }

    public function test_tutor_classrooms_relationship_works(): void
    {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create(['status_aktif' => true]);

        $tutor = Tutor::factory()->create(['user_id' => User::factory()->create()->id]);
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $academicYear->id,
        ]);

        $tutor->classrooms()->attach($classroom->id);

        $loadedTutor = Tutor::with('classrooms')->find($tutor->id);

        $this->assertNotNull($loadedTutor->classrooms);
        $this->assertCount(1, $loadedTutor->classrooms);
        $this->assertEquals($classroom->id, $loadedTutor->classrooms->first()->id);
    }

    public function test_subject_tutors_relationship_works(): void
    {
        $subject = Subject::factory()->create();
        $tutor = Tutor::factory()->create(['user_id' => User::factory()->create()->id]);

        $subject->tutors()->attach($tutor->id);

        $loadedSubject = Subject::with('tutors')->find($subject->id);

        $this->assertNotNull($loadedSubject->tutors);
        $this->assertCount(1, $loadedSubject->tutors);
        $this->assertEquals($tutor->id, $loadedSubject->tutors->first()->id);
    }

    public function test_subject_classrooms_relationship_works(): void
    {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create(['status_aktif' => true]);

        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $academicYear->id,
        ]);

        $subject->classrooms()->attach($classroom->id);

        $loadedSubject = Subject::with('classrooms')->find($subject->id);

        $this->assertNotNull($loadedSubject->classrooms);
        $this->assertCount(1, $loadedSubject->classrooms);
        $this->assertEquals($classroom->id, $loadedSubject->classrooms->first()->id);
    }
}
