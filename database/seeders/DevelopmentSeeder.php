<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or find admin user for development
        $user = User::firstOrCreate(
            ['email' => 'admin@pkbmaditya.test'],
            [
                'name' => 'Admin PKBM Aditya',
                'password' => Hash::make('password'),
                'phone' => '081234567890',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->command->info('✅ Development user created:');
        } else {
            $this->command->info('✅ Development user already exists:');
        }

        $this->command->info('   Email: admin@pkbmaditya.test');
        $this->command->info('   Password: password');
    }
}
