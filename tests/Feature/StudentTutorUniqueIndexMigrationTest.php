<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class StudentTutorUniqueIndexMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const STUDENT_INDEX = 'students_user_id_unique';

    private const TUTOR_INDEX = 'tutors_user_id_unique';

    private function studentMigration(): object
    {
        return require database_path('migrations/2026_10_20_025512_add_unique_index_to_students_user_id.php');
    }

    private function tutorMigration(): object
    {
        return require database_path('migrations/2026_10_20_062650_add_unique_index_to_tutors_user_id.php');
    }

    public function test_students_user_id_unique_index_exists_and_blocks_duplicates(): void
    {
        $this->assertTrue(Schema::hasIndex('students', self::STUDENT_INDEX, 'unique'));

        $user = User::factory()->create();
        Student::factory()->create(['user_id' => $user->id]);

        $this->expectException(QueryException::class);
        Student::factory()->create(['user_id' => $user->id]);
    }

    public function test_students_allows_multiple_null_user_ids(): void
    {
        $first = Student::factory()->create(['user_id' => null]);
        $second = Student::factory()->create(['user_id' => null]);
        $third = Student::factory()->create(['user_id' => null]);

        $this->assertNotNull($first->id);
        $this->assertNotNull($second->id);
        $this->assertNotNull($third->id);
        $this->assertEquals(3, Student::whereNull('user_id')->count());
    }

    public function test_student_migration_aborts_safely_when_duplicates_exist(): void
    {
        Schema::table('students', function ($table) {
            $table->dropUnique(self::STUDENT_INDEX);
        });

        $user = User::factory()->create();
        $s1 = Student::factory()->create(['user_id' => $user->id]);
        $s2 = Student::factory()->create(['user_id' => $user->id]);

        try {
            $this->studentMigration()->up();
            $this->fail('Migration should have aborted on duplicate user_id');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('ditemukan duplikasi', $e->getMessage());
            $this->assertStringContainsString(self::STUDENT_INDEX, $e->getMessage());
        }

        // Data must remain intact
        $this->assertDatabaseHas('students', ['id' => $s1->id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('students', ['id' => $s2->id, 'user_id' => $user->id]);
        $this->assertFalse(Schema::hasIndex('students', self::STUDENT_INDEX));
    }

    public function test_student_migration_is_idempotent(): void
    {
        $this->assertTrue(Schema::hasIndex('students', self::STUDENT_INDEX, 'unique'));
        $this->studentMigration()->up();
        $this->assertTrue(Schema::hasIndex('students', self::STUDENT_INDEX, 'unique'));
    }

    public function test_student_migration_down_only_removes_index(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);

        $this->studentMigration()->down();

        $this->assertFalse(Schema::hasIndex('students', self::STUDENT_INDEX));
        $this->assertTrue(Schema::hasTable('students'));
        $this->assertDatabaseHas('students', ['id' => $student->id, 'user_id' => $user->id]);
    }

    public function test_tutors_user_id_unique_index_exists_and_blocks_duplicates(): void
    {
        $this->assertTrue(Schema::hasIndex('tutors', self::TUTOR_INDEX, 'unique'));

        $user = User::factory()->create();
        Tutor::factory()->create(['user_id' => $user->id]);

        $this->expectException(QueryException::class);
        Tutor::factory()->create(['user_id' => $user->id]);
    }

    public function test_tutors_allows_multiple_null_user_ids(): void
    {
        $t1 = Tutor::factory()->create(['user_id' => null]);
        $t2 = Tutor::factory()->create(['user_id' => null]);

        $this->assertNotNull($t1->id);
        $this->assertNotNull($t2->id);
        $this->assertEquals(2, Tutor::whereNull('user_id')->count());
    }

    public function test_tutor_migration_aborts_safely_when_duplicates_exist(): void
    {
        Schema::table('tutors', function ($table) {
            $table->dropUnique(self::TUTOR_INDEX);
        });

        $user = User::factory()->create();
        $t1 = Tutor::factory()->create(['user_id' => $user->id]);
        $t2 = Tutor::factory()->create(['user_id' => $user->id]);

        try {
            $this->tutorMigration()->up();
            $this->fail('Migration should have aborted on duplicate user_id');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('ditemukan duplikasi', $e->getMessage());
            $this->assertStringContainsString(self::TUTOR_INDEX, $e->getMessage());
        }

        // Data must remain intact
        $this->assertDatabaseHas('tutors', ['id' => $t1->id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('tutors', ['id' => $t2->id, 'user_id' => $user->id]);
        $this->assertFalse(Schema::hasIndex('tutors', self::TUTOR_INDEX));
    }

    public function test_tutor_migration_is_idempotent(): void
    {
        $this->assertTrue(Schema::hasIndex('tutors', self::TUTOR_INDEX, 'unique'));
        $this->tutorMigration()->up();
        $this->assertTrue(Schema::hasIndex('tutors', self::TUTOR_INDEX, 'unique'));
    }

    public function test_tutor_migration_down_only_removes_index(): void
    {
        $user = User::factory()->create();
        $tutor = Tutor::factory()->create(['user_id' => $user->id]);

        $this->tutorMigration()->down();

        $this->assertFalse(Schema::hasIndex('tutors', self::TUTOR_INDEX));
        $this->assertTrue(Schema::hasTable('tutors'));
        $this->assertDatabaseHas('tutors', ['id' => $tutor->id, 'user_id' => $user->id]);
    }
}
