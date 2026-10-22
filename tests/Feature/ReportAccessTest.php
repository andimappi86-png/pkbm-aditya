<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // create roles
        $roles = ['operator', 'kepala-pkbm', 'ketua-yayasan', 'wali-kelas', 'tutor', 'peserta-didik'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        Permission::firstOrCreate(['name' => 'reports.view']);

        foreach (['ketua-yayasan', 'kepala-pkbm'] as $role) {
            Role::findByName($role)->givePermissionTo('reports.view');
        }
    }

    public function test_ketua_yayasan_can_access_reports()
    {
        $user = User::factory()->create();
        $user->assignRole('ketua-yayasan');

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
    }

    public function test_wali_kelas_cannot_access_reports()
    {
        $user = User::factory()->create();
        $user->assignRole('wali-kelas');

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(403);
    }
}
