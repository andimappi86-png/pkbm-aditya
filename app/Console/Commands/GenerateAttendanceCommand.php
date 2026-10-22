<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateAttendanceCommand extends Command
{
    protected $signature = 'attendance:generate {tanggal? : Tanggal target (YYYY-MM-DD)}';

    protected $description = 'Membuat sesi absensi otomatis untuk jadwal pelajaran yang aktif pada hari tertentu';

    public function handle(AttendanceService $attendanceService): int
    {
        $tanggalInput = $this->argument('tanggal') ?? Carbon::today()->toDateString();

        try {
            $date = Carbon::parse($tanggalInput);
        } catch (\Exception $e) {
            $this->error('Format tanggal tidak valid. Gunakan format YYYY-MM-DD.');

            return self::FAILURE;
        }

        $this->info('Menjalankan pembuatan absensi untuk tanggal: '.$date->toDateString().' ('.$date->locale('id')->isoFormat('dddd').')...');

        $summary = $attendanceService->generateForDate($date);

        $this->info('Proses selesai.');
        $this->line('- Jadwal diproses: '.$summary['processed_schedules']);
        $this->line('- Catatan absensi baru dibuat: '.$summary['created_attendances']);

        if (! empty($summary['skipped'])) {
            $this->warn('- Dilewati: '.count($summary['skipped']).' jadwal.');
            foreach ($summary['skipped'] as $skipped) {
                $this->line("  * Jadwal ID {$skipped['schedule_id']}: {$skipped['reason']}");
            }
        }

        return self::SUCCESS;
    }
}
