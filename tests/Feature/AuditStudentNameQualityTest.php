<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AuditStudentNameQualityTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_command_runs_read_only_and_detects_name_anomalies(): void
    {
        // Fixtures:
        // 1. Clean valid Indonesian names
        $s1 = Student::factory()->create(['nama_lengkap' => 'Budi Santoso']);
        $s2 = Student::factory()->create(['nama_lengkap' => 'Ida']); // Short, valid
        $s3 = Student::factory()->create(['nama_lengkap' => 'Muhammad Ali']);

        // 2. Numeric import misalignment
        $s4 = Student::factory()->create(['nama_lengkap' => '123456789012']);

        // 3. Punctuation only
        $s5 = Student::factory()->create(['nama_lengkap' => '---']);

        // 4. Single char / empty
        $s6 = Student::factory()->create(['nama_lengkap' => 'X']);

        // 5. Embedded digit
        $s7 = Student::factory()->create(['nama_lengkap' => 'Siswa 123']);

        $tempCsv = storage_path('app/private/test_audits/audit_test_'.uniqid().'.csv');

        $this->artisan('students:audit-name-quality', ['--output' => $tempCsv])
            ->assertSuccessful()
            ->expectsOutputToContain('Total Record Discan')
            ->expectsOutputToContain('Template rekonsiliasi manual tersimpan pada');

        $this->assertFileExists($tempCsv);

        $lines = file($tempCsv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->assertNotEmpty($lines);

        // Header verification
        $header = str_getcsv($lines[0]);
        $this->assertContains('student_id', $header);
        $this->assertContains('record_ref', $header);
        $this->assertContains('anomaly_type', $header);
        $this->assertContains('koreksi_nama_disetujui', $header);

        // Content verification: only anomalies should be in CSV
        $rows = array_slice($lines, 1);
        $this->assertCount(4, $rows); // s4, s5, s6, s7

        $csvText = implode("\n", $rows);
        $this->assertStringContainsString('all_digits', $csvText);
        $this->assertStringContainsString('symbol_only', $csvText);
        $this->assertStringContainsString('single_char_or_empty', $csvText);
        $this->assertStringContainsString('embedded_digits', $csvText);

        // No unmasked PII or raw numeric sequence
        $this->assertStringNotContainsString('123456789012', $csvText);
        $this->assertStringContainsString('[NUMERIC_12_DIGITS]', $csvText);

        // Verify read-only: data in database remains untouched
        $this->assertEquals('Budi Santoso', $s1->fresh()->nama_lengkap);
        $this->assertEquals('Ida', $s2->fresh()->nama_lengkap);
        $this->assertEquals('Muhammad Ali', $s3->fresh()->nama_lengkap);
        $this->assertEquals('123456789012', $s4->fresh()->nama_lengkap);

        // Cleanup
        if (File::exists($tempCsv)) {
            File::delete($tempCsv);
        }
    }
}
