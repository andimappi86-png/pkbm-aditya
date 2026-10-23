<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardKepalaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permission used by dashboard
        Permission::firstOrCreate(['name' => 'dashboard.view', 'guard_name' => 'web']);

        // Create kepala-pkbm role with permission
        $role = Role::firstOrCreate(['name' => 'kepala-pkbm', 'guard_name' => 'web']);
        $role->givePermissionTo('dashboard.view');
    }

    public function test_kepala_can_access_dashboard()
    {
        $user = User::factory()->create();
        $user->assignRole('kepala-pkbm');

        $response = $this->actingAs($user)->get('/dashboard/kepala');
        $response->assertStatus(200);
        $response->assertViewIs('dashboards.kepala');
    }
}
