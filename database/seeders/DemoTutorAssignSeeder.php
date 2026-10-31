<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoTutorAssignSeeder extends Seeder
{
    public function run(): void
    {
        // Only run for demo/testing/local environments
        if (! app()->environment('demo', 'testing', 'local')) {
            $this->command->warn('DemoTutorAssignSeeder skipped: not a demo/testing/local environment.');
            return;
        }

        $tutorUser = User::where('email', 'tutor@pkbm-aditya.test')->first();
        if (! $tutorUser) {
            $this->command->error('Demo tutor user not found. Run DemoUsersSeeder first.');
            return;
        }

        // Ensure Tutor record exists for this user
        $tutor = Tutor::firstOrCreate(
            ['user_id' => $tutorUser->id],
            [
                'nomor_induk' => 'TUTOR-DEMO-001',
                'nik' => '1234567890123456',
                'nama_lengkap' => $tutorUser->name,
                'nama_panggilan' => 'Tutor',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Mamuju',
                'tanggal_lahir' => '1990-01-01',
                'agama' => 'Islam',
                'alamat' => optional($tutorUser->profile)->address ?? 'Mamuju, Sulawesi Barat',
                'no_hp' => $tutorUser->phone,
                'email' => $tutorUser->email,
                'pendidikan_terakhir' => 'S1',
                'bidang_keahlian' => 'Umum',
                'status_tutor' => 'aktif',
                'tanggal_mulai_mengajar' => now()->toDateString(),
            ]
        );

        $this->command->info("Tutor record ensured for {$tutorUser->name} (ID: {$tutor->id})");

        // Pick first available subject, classroom, active academic year & semester
        $subject = Subject::where('status_aktif', true)->first();
        $classroom = Classroom::first();
        $academicYear = AcademicYear::where('status_aktif', true)->first();
        $semester = Semester::where('status_aktif', true)->first();

        if (! $subject || ! $classroom || ! $academicYear || ! $semester) {
            $this->command->error('Required reference data missing (subject, classroom, academic year, semester).');
            return;
        }

        // Create or update PenugasanMengajar for this tutor
        $penugasan = PenugasanMengajar::firstOrCreate(
            [
                'tutor_id' => $tutor->id,
                'subject_id' => $subject->id,
                'classroom_id' => $classroom->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
            ],
            [
                'status_aktif' => true,
            ]
        );

        // Attach subject to tutor_subject pivot if not already
        if (! $tutor->subjects()->where('subject_id', $subject->id)->exists()) {
            $tutor->subjects()->attach($subject->id);
        }

        // Attach classroom to tutor_classroom pivot if not already
        if (! $tutor->classrooms()->where('classroom_id', $classroom->id)->exists()) {
            $tutor->classrooms()->attach($classroom->id);
        }

        $this->command->info("PenugasanMengajar created/updated (ID: {$penugasan->id}) for Subject {$subject->nama} in Classroom {$classroom->nama_rombel}");
    }
}