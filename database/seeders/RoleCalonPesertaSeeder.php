<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleCalonPesertaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        Artisan::call('permission:cache-reset');

        // Create new permissions
        Permission::firstOrCreate(['name' => 'dashboard.view', 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'calon-peserta', 'guard_name' => 'web']);

        $role->syncPermissions([
            'dashboard.view',
        ]);
    }
}
