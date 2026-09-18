<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /**
         * =========================================================================
         * USER DEVELOPMENT / ADMINISTRATOR SEMENTARA (PHASE 1)
         * =========================================================================
         * CATATAN PENTING KEAMANAN:
         * Akun dan password ini HANYA diperuntukkan untuk keperluan development & testing lokal.
         * JANGAN PERNAH gunakan akun dan password default ini pada lingkungan PRODUCTION!
         * Role & Permission (RBAC) lengkap akan dikonfigurasi pada PHASE 2.
         * =========================================================================
         */
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.test'],
            [
                'name' => 'Administrator PKBM',
                'phone' => '081234567890',
                'status' => 'active',
                // PENTING: Password ini HANYA untuk development, jangan gunakan di production!
                'password' => Hash::make('Admin12345!'),
                'email_verified_at' => now(),
            ]
        );

        // Create or update profile
        Profile::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'bio' => 'Pengelola Utama Sistem Informasi PKBM Aditya Kabupaten Mamuju.',
                'address' => 'Jl. Poros Mamuju, Kabupaten Mamuju, Sulawesi Barat',
                'gender' => 'L',
            ]
        );

        // Phase 2: RBAC seeders
        $this->call([
            RolePermissionSeeder::class,
            RoleCalonPesertaSeeder::class,
            DemoUsersSeeder::class,
            MasterDataSeeder::class,
            StudentSeeder::class,
            AttendanceSeeder::class,
            GradeSeeder::class,
            AssignmentSeeder::class,
            MaterialSeeder::class,
            ForumSeeder::class,
            PpdbSeeder::class,
            DocumentLetterSeeder::class,
            AnnouncementSeeder::class,
        ]);
    }
}
