<?php

namespace Tests\Feature;

use App\Http\Controllers\QuizAttemptController;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDOException;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

class QuizAttemptSchemaIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private const INDEX_NAME = 'quiz_attempts_quiz_id_student_id_unique';

    private static ?object $migration = null;

    public function test_unique_index_exists_and_rejects_duplicate_attempt(): void
    {
        $this->assertTrue(Schema::hasIndex('quiz_attempts', self::INDEX_NAME, 'unique'));

        $index = collect(Schema::getIndexes('quiz_attempts'))
            ->firstWhere('name', self::INDEX_NAME);

        $this->assertNotNull($index);
        $this->assertTrue($index['unique']);
        $this->assertSame(['quiz_id', 'student_id'], $index['columns']);

        $quiz = Quiz::factory()->create();
        $student = Student::factory()->create();

        $this->createAttempt($quiz, $student);

        try {
            $this->createAttempt($quiz, $student);
            $this->fail('Duplicate quiz attempt was accepted.');
        } catch (QueryException $exception) {
            $this->assertSame('23000', (string) ($exception->errorInfo[0] ?? ''));
            $this->assertStringContainsString('quiz_id', $exception->getMessage());
            $this->assertStringContainsString('student_id', $exception->getMessage());
        }

        $this->assertSame(1, QuizAttempt::count());
    }

    public function test_migration_creates_index_when_no_duplicates_exist(): void
    {
        Schema::table('quiz_attempts', function ($table) {
            $table->dropUnique(self::INDEX_NAME);
        });

        $this->assertFalse(Schema::hasIndex('quiz_attempts', self::INDEX_NAME));

        $this->migration()->up();

        $this->assertTrue(Schema::hasIndex('quiz_attempts', self::INDEX_NAME, 'unique'));
    }

    public function test_migration_is_idempotent_when_unique_index_already_exists(): void
    {
        $this->assertTrue(Schema::hasIndex('quiz_attempts', self::INDEX_NAME, 'unique'));

        $this->migration()->up();

        $this->assertTrue(Schema::hasIndex('quiz_attempts', self::INDEX_NAME, 'unique'));
        $this->assertSame(1, collect(Schema::getIndexes('quiz_attempts'))
            ->where('name', self::INDEX_NAME)
            ->count());
    }

    public function test_migration_aborts_and_preserves_records_when_duplicates_exist(): void
    {
        Schema::table('quiz_attempts', function ($table) {
            $table->dropUnique(self::INDEX_NAME);
        });

        $quiz = Quiz::factory()->create();
        $student = Student::factory()->create();

        $this->createAttempt($quiz, $student);
        $this->createAttempt($quiz, $student);

        $attemptIds = QuizAttempt::orderBy('id')->pluck('id')->all();

        try {
            $this->migration()->up();
            $this->fail('Migration accepted duplicate quiz attempts.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('attempt duplikat', $exception->getMessage());
            $this->assertStringContainsString(self::INDEX_NAME, $exception->getMessage());
        }

        $this->assertSame($attemptIds, QuizAttempt::orderBy('id')->pluck('id')->all());
        $this->assertFalse(Schema::hasIndex('quiz_attempts', self::INDEX_NAME));
    }

    public function test_migration_down_only_removes_its_own_index(): void
    {
        $quiz = Quiz::factory()->create();
        $student = Student::factory()->create();
        $attempt = $this->createAttempt($quiz, $student);

        $this->migration()->down();

        $this->assertFalse(Schema::hasIndex('quiz_attempts', self::INDEX_NAME));
        $this->assertTrue(Schema::hasTable('quiz_attempts'));
        $this->assertDatabaseHas('quiz_attempts', [
            'id' => $attempt->id,
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_duplicate_violation_detector_accepts_only_attempt_index_violation(): void
    {
        $controller = new QuizAttemptController;
        $method = new ReflectionMethod($controller, 'isDuplicateQuizAttemptViolation');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($controller, $this->queryException(
            '23000',
            1062,
            "Duplicate entry '1-1' for key 'quiz_attempts.quiz_attempts_quiz_id_student_id_unique'"
        )));

        $this->assertTrue($method->invoke($controller, $this->queryException(
            '23505',
            0,
            'duplicate key value violates unique constraint "quiz_attempts_quiz_id_student_id_unique"'
        )));

        $this->assertFalse($method->invoke($controller, $this->queryException(
            '40001',
            1213,
            'Deadlock found when trying to get lock; try restarting transaction'
        )));

        $this->assertFalse($method->invoke($controller, $this->queryException(
            '23000',
            1452,
            'Cannot add or update a child row: a foreign key constraint fails'
        )));

        $quiz = Quiz::factory()->create();
        $student = Student::factory()->create();

        $this->createAttempt($quiz, $student);

        try {
            $this->createAttempt($quiz, $student);
            $this->fail('Duplicate quiz attempt was accepted.');
        } catch (QueryException $exception) {
            $this->assertTrue($method->invoke($controller, $exception));
        }

        try {
            DB::table('quiz_attempts')->insert([
                'quiz_id' => null,
                'student_id' => $student->id,
                'mulai_waktu' => now(),
                'nilai' => 0,
                'selesai' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->fail('Null quiz_id was accepted.');
        } catch (QueryException $exception) {
            $this->assertFalse($method->invoke($controller, $exception));
        }
    }

    private function queryException(string $sqlState, int $driverCode, string $message): QueryException
    {
        $previous = new PDOException($message);
        $previous->errorInfo = [$sqlState, $driverCode, $message];

        return new QueryException('mysql', 'insert into quiz_attempts', [], $previous);
    }

    private function createAttempt(Quiz $quiz, Student $student): QuizAttempt
    {
        return QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'mulai_waktu' => now(),
            'selesai' => false,
        ]);
    }

    private function migration(): object
    {
        if (self::$migration === null) {
            self::$migration = require database_path('migrations/2026_10_20_174927_add_unique_quiz_attempt_index.php');
        }

        return self::$migration;
    }
}
