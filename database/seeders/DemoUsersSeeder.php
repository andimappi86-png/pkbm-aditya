<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Safety: only run in demo environment against disposable database
        if (! app()->environment('demo', 'testing')) {
            throw new RuntimeException('DemoUsersSeeder hanya boleh dijalankan dengan APP_ENV=demo atau testing.');
        }
        $database = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
        if ($database !== 'pkbm_aditya_disposable' && ! app()->environment('testing')) {
            throw new RuntimeException(
                'DemoUsersSeeder menolak berjalan pada database "'.$database.'". Target wajib pkbm_aditya_disposable.'
            );
        }

        $this->command->info('Creating demo users for each role...');

        // 1. Ketua Yayasan
        $ketua = User::updateOrCreate(
            ['email' => 'ketua@pkbm-aditya.test'],
            [
                'name' => 'Ketua Yayasan Demo',
                'phone' => '081234567801',
                'status' => 'active',
                'password' => Hash::make(config('demo.passwords.ketua-yayasan')),
                'email_verified_at' => now(),
            ]
        );
        $ketua->syncRoles(['ketua-yayasan']);
        Profile::updateOrCreate(
            ['user_id' => $ketua->id],
            [
                'gender' => 'L',
                'address' => 'Mamuju, Sulawesi Barat',
            ]
        );
        $this->command->info('✅ Ketua Yayasan: ketua@pkbm-aditya.test');

        // 2. Kepala PKBM
        $kepala = User::updateOrCreate(
            ['email' => 'kepala@pkbm-aditya.test'],
            [
                'name' => 'Kepala PKBM Demo',
                'phone' => '081234567802',
                'status' => 'active',
                'password' => Hash::make(config('demo.passwords.kepala-pkbm')),
                'email_verified_at' => now(),
            ]
        );
        $kepala->syncRoles(['kepala-pkbm']);
        Profile::updateOrCreate(
            ['user_id' => $kepala->id],
            [
                'gender' => 'L',
                'address' => 'Mamuju, Sulawesi Barat',
            ]
        );
        $this->command->info('✅ Kepala PKBM: kepala@pkbm-aditya.test');

        // 3. Wali Kelas
        $walikelas = User::updateOrCreate(
            ['email' => 'walikelas@pkbm-aditya.test'],
            [
                'name' => 'Wali Kelas Demo',
                'phone' => '081234567803',
                'status' => 'active',
                'password' => Hash::make(config('demo.passwords.wali-kelas')),
                'email_verified_at' => now(),
            ]
        );
        $walikelas->syncRoles(['wali-kelas']);
        Profile::updateOrCreate(
            ['user_id' => $walikelas->id],
            [
                'gender' => 'P',
                'address' => 'Mamuju, Sulawesi Barat',
            ]
        );
        $this->command->info('✅ Wali Kelas: walikelas@pkbm-aditya.test');

        // 4. Tutor
        $tutor = User::updateOrCreate(
            ['email' => 'tutor@pkbm-aditya.test'],
            [
                'name' => 'Tutor Demo',
                'phone' => '081234567804',
                'status' => 'active',
                'password' => Hash::make(config('demo.passwords.tutor')),
                'email_verified_at' => now(),
            ]
        );
        $tutor->syncRoles(['tutor']);
        Profile::updateOrCreate(
            ['user_id' => $tutor->id],
            [
                'gender' => 'L',
                'address' => 'Mamuju, Sulawesi Barat',
            ]
        );
        $this->command->info('✅ Tutor: tutor@pkbm-aditya.test');

        // 5. Peserta Didik
        $siswa = User::updateOrCreate(
            ['email' => 'siswa@pkbm-aditya.test'],
            [
                'name' => 'Siswa Demo',
                'phone' => '081234567805',
                'status' => 'active',
                'password' => Hash::make(config('demo.passwords.peserta-didik')),
                'email_verified_at' => now(),
            ]
        );
        $siswa->syncRoles(['peserta-didik']);
        Profile::updateOrCreate(
            ['user_id' => $siswa->id],
            [
                'gender' => 'L',
                'address' => 'Mamuju, Sulawesi Barat',
            ]
        );
        $this->command->info('✅ Peserta Didik: siswa@pkbm-aditya.test');

        // Assign role to existing admin (kepala-pkbm by default)
        $admin = User::where('email', 'admin@example.test')->first();
        if ($admin && ! $admin->hasAnyRole(['kepala-pkbm', 'ketua-yayasan'])) {
            $admin->syncRoles(['kepala-pkbm']);
            $this->command->info('✅ Admin user assigned kepala-pkbm role');
        }

        // 6. Operator
        $operator = User::updateOrCreate(
            ['email' => 'operator@pkbm-aditya.test'],
            [
                'name' => 'Operator Demo',
                'phone' => '081234567806',
                'status' => 'active',
                'password' => Hash::make(config('demo.passwords.operator')),
                'email_verified_at' => now(),
            ]
        );
        $operator->syncRoles(['operator']);
        Profile::updateOrCreate(
            ['user_id' => $operator->id],
            [
                'gender' => 'L',
                'address' => 'Mamuju, Sulawesi Barat',
            ]
        );
        $this->command->info('✅ Operator: operator@pkbm-aditya.test');

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('Demo Users Created:');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('1. Ketua Yayasan : ketua@pkbm-aditya.test');
        $this->command->info('2. Kepala PKBM   : kepala@pkbm-aditya.test');
        $this->command->info('3. Wali Kelas    : walikelas@pkbm-aditya.test');
        $this->command->info('4. Tutor         : tutor@pkbm-aditya.test');
        $this->command->info('5. Peserta Didik : siswa@pkbm-aditya.test');
        $this->command->info('6. Operator      : operator@pkbm-aditya.test');
        $this->command->info('Passwords are unique per role and stored in .env.demo (not displayed)');
        $this->command->info('═══════════════════════════════════════');
    }
}
