<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Profile;
use App\Models\Program;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Demo fixtures for the temporary public demo.
 *
 * SAFETY: this seeder refuses to run outside APP_ENV=demo and outside the
 * disposable database, so it can never touch the primary `pkbm_aditya` schema.
 */
class DemoSeeder extends Seeder
{
    public const DEMO_DATABASE = 'pkbm_aditya_disposable';

    /** Demo accounts reused from previous fixture runs, keyed by email. */
    private const ACCOUNTS = [
        'operator.test@pkbm.local' => ['Operator Demo', ['operator']],
        'kepala.test@pkbm.local' => ['Kepala PKBM Demo', ['kepala-pkbm']],
        'yayasan.test@pkbm.local' => ['Ketua Yayasan Demo', ['ketua-yayasan']],
        'walikelas.test@pkbm.local' => ['Wali Kelas 10-A Demo', ['wali-kelas']],
        'tutor.test@pkbm.local' => ['Tutor Matematika Demo', ['tutor']],
        'dual.test@pkbm.local' => ['Tutor + Wali Kelas Demo', ['tutor', 'wali-kelas']],
        'siswa.test@pkbm.local' => ['Peserta Didik 10-A Demo', ['peserta-didik']],
        'siswab.test@pkbm.local' => ['Peserta Didik 10-B Demo', ['peserta-didik']],
    ];

    public function run(): void
    {
        $this->assertDisposableTarget();

        $program = Program::firstOrCreate(
            ['kode' => 'PKT_C'],
            ['nama' => 'Paket C Setara SMA', 'deskripsi' => 'Program kesetaraan jenjang SMA.', 'status_aktif' => true],
        );

        $academicYear = AcademicYear::firstOrCreate(
            ['nama' => '2026/2027'],
            ['tanggal_mulai' => '2026-07-01', 'tanggal_selesai' => '2027-06-30', 'status_aktif' => true],
        );

        $semester = Semester::firstOrCreate(
            ['tahun_ajaran_id' => $academicYear->id, 'kode' => '20261'],
            ['nama_semester' => 'Ganjil', 'status_aktif' => true],
        );

        $this->seedAccounts();

        $waliA = User::where('email', 'walikelas.test@pkbm.local')->firstOrFail();
        $waliB = User::where('email', 'dual.test@pkbm.local')->firstOrFail();
        $tutorUser = User::where('email', 'tutor.test@pkbm.local')->firstOrFail();

        $classroomA = Classroom::firstOrCreate(
            ['kode_rombel' => 'R_10A'],
            [
                'nama_rombel' => 'Kelas 10-A Paket C',
                'program_id' => $program->id,
                'tahun_ajaran_id' => $academicYear->id,
                'wali_kelas_id' => $waliA->id,
                'status_aktif' => true,
            ],
        );

        $classroomB = Classroom::firstOrCreate(
            ['kode_rombel' => 'R_10B'],
            [
                'nama_rombel' => 'Kelas 10-B Paket C',
                'program_id' => $program->id,
                'tahun_ajaran_id' => $academicYear->id,
                'wali_kelas_id' => $waliB->id,
                'status_aktif' => true,
            ],
        );

        $matematika = Subject::firstOrCreate(
            ['kode' => 'MAT10'],
            ['nama' => 'Matematika Paket C', 'status_aktif' => true],
        );

        $bahasa = Subject::firstOrCreate(
            ['kode' => 'IND10'],
            ['nama' => 'Bahasa Indonesia Paket C', 'status_aktif' => true],
        );

        $tutor = Tutor::where('user_id', $tutorUser->id)->first();

        if ($tutor) {
            PenugasanMengajar::firstOrCreate([
                'tutor_id' => $tutor->id,
                'subject_id' => $matematika->id,
                'classroom_id' => $classroomA->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
            ], ['status_aktif' => true]);
        }

        $dualTutor = Tutor::where('user_id', $waliB->id)->first();

        if ($dualTutor) {
            PenugasanMengajar::firstOrCreate([
                'tutor_id' => $dualTutor->id,
                'subject_id' => $bahasa->id,
                'classroom_id' => $classroomB->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
            ], ['status_aktif' => true]);
        }

        Artisan::call('permission:cache-reset');

        $this->command->info('Demo fixture siap pada database '.DB::connection()->getDatabaseName().'.');
        $this->command->info('Akun demo:');

        foreach (self::ACCOUNTS as $email => $meta) {
            $this->command->info(sprintf('  %-28s %s', $email, implode(' + ', $meta[1])));
        }
    }

    /**
     * Fail closed: demo fixtures may only run against the disposable schema.
     */
    private function assertDisposableTarget(): void
    {
        if (! app()->environment('demo', 'testing')) {
            throw new RuntimeException('DemoSeeder hanya boleh dijalankan dengan APP_ENV=demo atau testing.');
        }

        $database = DB::connection()->getDatabaseName();

        if ($database !== self::DEMO_DATABASE && ! app()->environment('testing')) {
            throw new RuntimeException(
                'DemoSeeder menolak berjalan pada database "'.$database.'". Target wajib '.self::DEMO_DATABASE.'.'
            );
        }
    }

    private function seedAccounts(): void
    {
        foreach (self::ACCOUNTS as $email => [$name, $roles]) {
            $roleKey = $roles[0]; // primary role
            $password = config("demo.passwords.{$roleKey}");
            if (! $password) {
                throw new RuntimeException("Demo password not configured for role: {$roleKey}");
            }

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                    'status' => 'active',
                ],
            );

            $user->syncRoles($roles);

            Profile::updateOrCreate(
                ['user_id' => $user->id],
                ['gender' => 'L', 'address' => 'Mamuju, Sulawesi Barat (data demo)'],
            );
        }
    }
}
