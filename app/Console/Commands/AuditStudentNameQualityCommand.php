<?php

namespace App\Console\Commands;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AuditStudentNameQualityCommand extends Command
{
    protected $signature = 'students:audit-name-quality {--output= : Custom destination path for the audit reconciliation CSV}';

    protected $description = 'Audit data kualitas kolom nama_lengkap peserta didik (read-only, tanpa mengekspos PII)';

    public function handle(): int
    {
        $this->info('Memulai audit kualitas data nama peserta didik (mode: READ-ONLY)...');

        $outputPath = $this->resolveOutputPath();
        File::ensureDirectoryExists(dirname($outputPath));

        $handle = fopen($outputPath, 'w');
        if (! $handle) {
            $this->error('Gagal membuka file output: '.$outputPath);

            return self::FAILURE;
        }

        // CSV Header template untuk rekonsiliasi manual oleh pemilik data
        fputcsv($handle, [
            'student_id',
            'record_ref',
            'anomaly_type',
            'severity',
            'length',
            'value_pattern_hint',
            'masked_preview',
            'koreksi_nama_disetujui',
            'nama_penyetuju',
            'tanggal_persetujuan',
            'catatan_revisi',
        ]);

        $counts = [
            'total_scanned' => 0,
            'clean' => 0,
            'all_digits' => 0,
            'symbol_only' => 0,
            'single_char_or_empty' => 0,
            'embedded_digits' => 0,
        ];

        Student::query()
            ->select(['id', 'nama_lengkap'])
            ->orderBy('id')
            ->chunk(250, function ($students) use ($handle, &$counts): void {
                foreach ($students as $student) {
                    $counts['total_scanned']++;
                    $raw = $student->nama_lengkap;
                    $trimmed = trim((string) $raw);
                    $length = mb_strlen($trimmed);

                    $anomaly = $this->detectAnomaly($trimmed, $length);

                    if ($anomaly === null) {
                        $counts['clean']++;

                        continue;
                    }

                    $counts[$anomaly['type']] = ($counts[$anomaly['type']] ?? 0) + 1;

                    fputcsv($handle, [
                        $student->id,
                        sprintf('STD-%06d', $student->id),
                        $anomaly['type'],
                        $anomaly['severity'],
                        $length,
                        $anomaly['pattern_hint'],
                        $anomaly['masked_preview'],
                        '', // koreksi_nama_disetujui (kolom kosong untuk diisi pemilik data)
                        '', // nama_penyetuju
                        '', // tanggal_persetujuan
                        '', // catatan_revisi
                    ]);
                }
            });

        fclose($handle);

        $totalAnomalies = $counts['all_digits']
            + $counts['symbol_only']
            + $counts['single_char_or_empty']
            + $counts['embedded_digits'];

        $this->newLine();
        $this->info('Audit selesai. Ringkasan temuan:');
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total Record Discan', $counts['total_scanned']],
                ['Record Bersih (Valid)', $counts['clean']],
                ['Anomali: Seluruh Karakter Angka (All Digits)', $counts['all_digits']],
                ['Anomali: Simbol/Whitespace Saja', $counts['symbol_only']],
                ['Anomali: 1 Karakter atau Kosong', $counts['single_char_or_empty']],
                ['Perlu Review: Mengandung Angka', $counts['embedded_digits']],
                ['Total Memerlukan Rekonsiliasi', $totalAnomalies],
            ]
        );

        $this->newLine();
        $this->line('Template rekonsiliasi manual tersimpan pada:');
        $this->line('  '.$outputPath);
        $this->comment('CATATAN: File audit disimpan di private storage dan data sensitif (PII) telah diminimalisasi.');

        return self::SUCCESS;
    }

    /**
     * @return array{type: string, severity: string, pattern_hint: string, masked_preview: string}|null
     */
    private function detectAnomaly(string $name, int $length): ?array
    {
        if ($length === 0 || $length === 1) {
            return [
                'type' => 'single_char_or_empty',
                'severity' => 'HIGH',
                'pattern_hint' => $length === 0 ? 'empty_string' : 'single_character',
                'masked_preview' => $length === 0 ? '[EMPTY]' : '[1_CHAR]',
            ];
        }

        if (preg_match('/^\d+$/', $name) === 1) {
            return [
                'type' => 'all_digits',
                'severity' => 'HIGH',
                'pattern_hint' => 'numeric_import_misalignment',
                'masked_preview' => sprintf('[NUMERIC_%d_DIGITS]', $length),
            ];
        }

        if (preg_match('/^[\p{P}\p{Z}\s]+$/u', $name) === 1) {
            return [
                'type' => 'symbol_only',
                'severity' => 'HIGH',
                'pattern_hint' => 'punctuation_whitespace_only',
                'masked_preview' => '[PUNCTUATION_ONLY]',
            ];
        }

        if (preg_match('/\d/', $name) === 1) {
            return [
                'type' => 'embedded_digits',
                'severity' => 'MEDIUM',
                'pattern_hint' => 'contains_numbers',
                'masked_preview' => $this->maskNameWithDigits($name),
            ];
        }

        return null;
    }

    private function maskNameWithDigits(string $name): string
    {
        $firstChar = mb_substr($name, 0, 1);
        $lastChar = mb_substr($name, -1, 1);

        return sprintf('%s***%s [len:%d]', $firstChar, $lastChar, mb_strlen($name));
    }

    private function resolveOutputPath(): string
    {
        $custom = $this->option('output');
        if (is_string($custom) && trim($custom) !== '') {
            return trim($custom);
        }

        $timestamp = Carbon::now()->format('Ymd_His');

        return storage_path("app/private/audits/student_name_quality_{$timestamp}.csv");
    }
}
