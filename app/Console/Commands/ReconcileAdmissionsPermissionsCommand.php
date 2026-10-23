<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;

class ReconcileAdmissionsPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:reconcile-admissions
                            {--dry-run : Run in audit/dry-run mode without modifying the database}
                            {--execute : Run actual database reconciliation instead of dry-run}
                            {--force : Bypass interactive confirmation when --execute is used}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit and safely reconcile legacy admissions.* permissions without affecting active modules';

    /**
     * Target obsolete permissions.
     *
     * @var array<int, string>
     */
    protected array $targetPermissions = [
        'admissions.apply',
        'admissions.manage',
        'admissions.view',
        'admissions.view_own',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isExecute = (bool) $this->option('execute');
        $isForce = (bool) $this->option('force');

        $this->info('Scanning database for legacy admissions permissions...');

        // Query permissions strictly by name
        $permissions = DB::table('permissions')
            ->whereIn('name', $this->targetPermissions)
            ->select('id', 'name', 'guard_name')
            ->get();

        if ($permissions->isEmpty()) {
            $this->info('No legacy admissions permissions found. Database is already clean.');

            return self::SUCCESS;
        }

        $permissionIds = $permissions->pluck('id')->all();

        // Query role relationships
        $rolePermissions = DB::table('role_has_permissions')
            ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->whereIn('role_has_permissions.permission_id', $permissionIds)
            ->select(
                'roles.id as role_id',
                'roles.name as role_name',
                'permissions.id as permission_id',
                'permissions.name as permission_name'
            )
            ->orderBy('roles.name')
            ->orderBy('permissions.name')
            ->get();

        // Query direct user relationships if any
        $modelPermissions = DB::table('model_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
            ->whereIn('model_has_permissions.permission_id', $permissionIds)
            ->select(
                'model_has_permissions.model_type',
                'model_has_permissions.model_id',
                'permissions.name as permission_name'
            )
            ->get();

        // Display summary table
        $this->warn(sprintf('Found %d target permission(s) and %d role mapping(s).', $permissions->count(), $rolePermissions->count()));

        $this->table(
            ['Permission ID', 'Name', 'Guard'],
            $permissions->map(fn ($p) => [$p->id, $p->name, $p->guard_name])->toArray()
        );

        if ($rolePermissions->isNotEmpty()) {
            $this->line('Role Associations to be removed:');
            $this->table(
                ['Role ID', 'Role Name', 'Permission ID', 'Permission Name'],
                $rolePermissions->map(fn ($rp) => [$rp->role_id, $rp->role_name, $rp->permission_id, $rp->permission_name])->toArray()
            );
        }

        if ($modelPermissions->isNotEmpty()) {
            $this->line('Direct Model Associations to be removed:');
            $this->table(
                ['Model Type', 'Model ID', 'Permission Name'],
                $modelPermissions->map(fn ($mp) => [$mp->model_type, $mp->model_id, $mp->permission_name])->toArray()
            );
        }

        // Handle dry-run mode
        if (! $isExecute) {
            $this->info('[DRY RUN] No database changes were made. Pass --execute to apply reconciliation.');

            return self::SUCCESS;
        }

        // Confirmation required unless --force is provided
        if (! $isForce) {
            if (! $this->confirm('Are you sure you want to delete these permissions and their role associations?', false)) {
                $this->warn('Reconciliation cancelled by user. No changes made.');

                return self::SUCCESS;
            }
        }

        // Execute inside database transaction
        $startTime = microtime(true);

        try {
            DB::beginTransaction();

            // 1. Delete role associations
            $deletedRolePerms = DB::table('role_has_permissions')
                ->whereIn('permission_id', $permissionIds)
                ->delete();

            // 2. Delete direct model associations if any
            $deletedModelPerms = DB::table('model_has_permissions')
                ->whereIn('permission_id', $permissionIds)
                ->delete();

            // 3. Delete permissions
            $deletedPerms = DB::table('permissions')
                ->whereIn('id', $permissionIds)
                ->delete();

            DB::commit();

            // Flush Spatie cache post-commit
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('Admissions permissions reconciled successfully.', [
                'deleted_permissions_count' => $deletedPerms,
                'deleted_role_mappings_count' => $deletedRolePerms,
                'deleted_model_mappings_count' => $deletedModelPerms,
                'duration_ms' => $duration,
                'target_permissions' => $this->targetPermissions,
            ]);

            $this->info(sprintf(
                'Successfully reconciled: %d permission(s), %d role mapping(s), and %d direct mapping(s) removed in %s ms.',
                $deletedPerms,
                $deletedRolePerms,
                $deletedModelPerms,
                $duration
            ));
            $this->info('Spatie permission cache cleared.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admissions permissions reconciliation failed.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Failed to reconcile permissions: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
