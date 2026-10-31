<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReconcileAdmissionsPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private array $legacyPermissions = [
        'admissions.apply',
        'admissions.manage',
        'admissions.view',
        'admissions.view_own',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Setup baseline roles
        $roles = [
            'operator',
            'kepala-pkbm',
            'ketua-yayasan',
            'calon-peserta',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Setup active permission that must remain untouched
        Permission::firstOrCreate(['name' => 'dashboard.view', 'guard_name' => 'web']);
        Role::findByName('calon-peserta')->givePermissionTo('dashboard.view');

        // Setup legacy permissions
        foreach ($this->legacyPermissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // Setup legacy role_has_permissions
        Role::findByName('operator')->givePermissionTo(['admissions.view', 'admissions.manage']);
        Role::findByName('kepala-pkbm')->givePermissionTo(['admissions.view', 'admissions.manage']);
        Role::findByName('ketua-yayasan')->givePermissionTo(['admissions.view']);
        Role::findByName('calon-peserta')->givePermissionTo(['admissions.apply', 'admissions.view_own']);
    }

    public function test_dry_run_does_not_modify_database(): void
    {
        $this->artisan('permission:reconcile-admissions')
            ->expectsOutputToContain('[DRY RUN] No database changes were made.')
            ->assertSuccessful();

        foreach ($this->legacyPermissions as $permName) {
            $this->assertDatabaseHas('permissions', ['name' => $permName]);
        }

        $this->assertTrue(Role::findByName('calon-peserta')->hasPermissionTo('admissions.apply'));
        $this->assertTrue(Role::findByName('operator')->hasPermissionTo('admissions.manage'));
    }

    public function test_dry_run_reports_actual_detected_permissions_and_relations(): void
    {
        $this->artisan('permission:reconcile-admissions')
            ->expectsOutputToContain('Found 4 target permission(s) and 7 role mapping(s).')
            ->expectsOutputToContain('admissions.apply')
            ->expectsOutputToContain('admissions.manage')
            ->expectsOutputToContain('admissions.view')
            ->assertSuccessful();
    }

    public function test_cancelled_confirmation_leaves_database_unmodified(): void
    {
        $this->artisan('permission:reconcile-admissions', ['--execute' => true])
            ->expectsConfirmation('Are you sure you want to delete these permissions and their role associations?', 'no')
            ->expectsOutputToContain('Reconciliation cancelled by user. No changes made.')
            ->assertSuccessful();

        foreach ($this->legacyPermissions as $permName) {
            $this->assertDatabaseHas('permissions', ['name' => $permName]);
        }
    }

    public function test_execute_mode_removes_only_target_admissions_permissions_and_relations(): void
    {
        $this->artisan('permission:reconcile-admissions', [
            '--execute' => true,
            '--force' => true,
        ])
            ->expectsOutputToContain('Successfully reconciled: 4 permission(s), 7 role mapping(s)')
            ->assertSuccessful();

        foreach ($this->legacyPermissions as $permName) {
            $this->assertDatabaseMissing('permissions', ['name' => $permName]);
        }

        $this->assertEquals(0, DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->whereIn('permissions.name', $this->legacyPermissions)
            ->count());
    }

    public function test_other_permissions_and_roles_remain_intact(): void
    {
        $this->artisan('permission:reconcile-admissions', [
            '--execute' => true,
            '--force' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('roles', ['name' => 'calon-peserta']);
        $this->assertDatabaseHas('roles', ['name' => 'operator']);
        $this->assertDatabaseHas('permissions', ['name' => 'dashboard.view']);

        $calonRole = Role::findByName('calon-peserta');
        $this->assertTrue($calonRole->hasPermissionTo('dashboard.view'));
    }

    public function test_public_registration_still_works_with_calon_peserta_role(): void
    {
        // Run reconcile first
        $this->artisan('permission:reconcile-admissions', [
            '--execute' => true,
            '--force' => true,
        ])->assertSuccessful();

        $response = $this->post('/register', [
            'name' => 'Pendaftar Baru Test',
            'email' => 'pendaftar.baru@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'pendaftar.baru@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('calon-peserta'));
        $this->assertEquals('announcements.index', $user->dashboardRoute());
    }

    public function test_command_is_idempotent_when_target_already_removed(): void
    {
        // First execution
        $this->artisan('permission:reconcile-admissions', [
            '--execute' => true,
            '--force' => true,
        ])->assertSuccessful();

        // Second execution should report already clean
        $this->artisan('permission:reconcile-admissions', [
            '--execute' => true,
            '--force' => true,
        ])
            ->expectsOutputToContain('No legacy admissions permissions found. Database is already clean.')
            ->assertSuccessful();
    }

    public function test_failed_transaction_rolls_back_partial_changes(): void
    {
        // Mock query exception during deletion of permissions table by dropping permissions or simulating failure
        // We simulate failure by hooking into database event or forcing an exception
        DB::listen(function ($query) {
            if (str_contains(strtolower($query->sql), 'delete from "permissions"') || str_contains(strtolower($query->sql), 'delete from `permissions`')) {
                throw new \RuntimeException('Simulated database failure during permissions deletion');
            }
        });

        $this->artisan('permission:reconcile-admissions', [
            '--execute' => true,
            '--force' => true,
        ])
            ->expectsOutputToContain('Failed to reconcile permissions: Simulated database failure')
            ->assertExitCode(1);

        // Verification: database rolled back, relations and permissions still exist
        $this->assertDatabaseHas('permissions', ['name' => 'admissions.view']);
        $this->assertTrue(Role::findByName('operator')->hasPermissionTo('admissions.view'));
    }

    public function test_spatie_cache_is_cleared_after_successful_reconciliation(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->getPermissions(); // Warm up cache

        $this->artisan('permission:reconcile-admissions', [
            '--execute' => true,
            '--force' => true,
        ])
            ->expectsOutputToContain('Spatie permission cache cleared.')
            ->assertSuccessful();

        // Check that permissions cache does not contain obsolete permissions
        $cachedPermissions = $registrar->getPermissions();
        $this->assertFalse($cachedPermissions->contains('name', 'admissions.view'));
    }
}
