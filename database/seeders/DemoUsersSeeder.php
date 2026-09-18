<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating demo users for each role...');

        // 1. Ketua Yayasan
        $ketua = User::firstOrCreate(
            ['email' => 'ketua@pkbm-aditya.test'],
            [
                'name' => 'Ketua Yayasan Demo',
                'phone' => '081234567801',
                'status' => 'active',
                'password' => Hash::make('password'),
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
        $this->command->info('✅ Ketua Yayasan: ketua@pkbm-aditya.test / password');

        // 2. Kepala PKBM
        $kepala = User::firstOrCreate(
            ['email' => 'kepala@pkbm-aditya.test'],
            [
                'name' => 'Kepala PKBM Demo',
                'phone' => '081234567802',
                'status' => 'active',
                'password' => Hash::make('password'),
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
        $this->command->info('✅ Kepala PKBM: kepala@pkbm-aditya.test / password');

        // 3. Wali Kelas
        $walikelas = User::firstOrCreate(
            ['email' => 'walikelas@pkbm-aditya.test'],
            [
                'name' => 'Wali Kelas Demo',
                'phone' => '081234567803',
                'status' => 'active',
                'password' => Hash::make('password'),
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
        $this->command->info('✅ Wali Kelas: walikelas@pkbm-aditya.test / password');

        // 4. Tutor
        $tutor = User::firstOrCreate(
            ['email' => 'tutor@pkbm-aditya.test'],
            [
                'name' => 'Tutor Demo',
                'phone' => '081234567804',
                'status' => 'active',
                'password' => Hash::make('password'),
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
        $this->command->info('✅ Tutor: tutor@pkbm-aditya.test / password');

        // 5. Peserta Didik
        $siswa = User::firstOrCreate(
            ['email' => 'siswa@pkbm-aditya.test'],
            [
                'name' => 'Siswa Demo',
                'phone' => '081234567805',
                'status' => 'active',
                'password' => Hash::make('password'),
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
        $this->command->info('✅ Peserta Didik: siswa@pkbm-aditya.test / password');

        // Assign role to existing admin (kepala-pkbm by default)
        $admin = User::where('email', 'admin@example.test')->first();
        if ($admin && ! $admin->hasAnyRole(['kepala-pkbm', 'ketua-yayasan'])) {
            $admin->syncRoles(['kepala-pkbm']);
            $this->command->info('✅ Admin user assigned kepala-pkbm role');
        }

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('Demo Users Created:');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('1. Ketua Yayasan : ketua@pkbm-aditya.test');
        $this->command->info('2. Kepala PKBM   : kepala@pkbm-aditya.test');
        $this->command->info('3. Wali Kelas    : walikelas@pkbm-aditya.test');
        $this->command->info('4. Tutor         : tutor@pkbm-aditya.test');
        $this->command->info('5. Peserta Didik : siswa@pkbm-aditya.test');
        $this->command->info('Password (all)   : password');
        $this->command->info('═══════════════════════════════════════');
    }
}
