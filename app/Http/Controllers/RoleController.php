<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        Gate::authorize('users.manage');

        $roles = Role::withCount(['users', 'permissions'])->orderBy('name')->get();

        $permissionGroups = $this->groupPermissions(Permission::orderBy('name')->get());

        return view('roles.index', compact('roles', 'permissionGroups'));
    }

    public function create(): View
    {
        Gate::authorize('users.manage');

        $permissions = Permission::orderBy('name')->get();

        $permissionGroups = $this->groupPermissions($permissions);

        return view('roles.create', compact('permissionGroups'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        Gate::authorize('users.manage');

        $data = $request->validated();

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        if (! empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return redirect()
            ->route('roles.edit', $role)
            ->with('success', "Role {$role->name} berhasil dibuat.");
    }

    public function edit(Role $role): View
    {
        Gate::authorize('users.manage');

        $role->load('permissions');
        $permissions = Permission::orderBy('name')->get();

        $permissionGroups = $this->groupPermissions($permissions);

        return view('roles.edit', compact('role', 'permissionGroups'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        Gate::authorize('users.manage');

        $data = $request->validated();

        if ($role->name === 'operator') {
            $requiredPerms = ['users.manage', 'dashboard.view'];
            foreach ($requiredPerms as $perm) {
                if (! in_array($perm, $data['permissions'] ?? [])) {
                    throw ValidationException::withMessages([
                        'permissions' => "Role operator harus mempertahankan permission: {$perm}.",
                    ]);
                }
            }
        }

        $protectedRoles = ['operator', 'kepala-pkbm', 'ketua-yayasan', 'wali-kelas', 'tutor', 'peserta-didik', 'calon-peserta'];

        if (in_array($role->name, $protectedRoles) && $role->name !== $data['name']) {
            throw ValidationException::withMessages([
                'name' => "Role sistem {$role->name} tidak dapat diganti namanya.",
            ]);
        }

        $role->update(['name' => $data['name']]);

        if (! empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()
            ->route('roles.edit', $role)
            ->with('success', "Role {$role->name} berhasil diperbarui.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('users.manage');

        $protectedRoles = ['operator', 'kepala-pkbm', 'ketua-yayasan', 'wali-kelas', 'tutor', 'peserta-didik', 'calon-peserta'];

        if (in_array($role->name, $protectedRoles)) {
            return back()->with('error', "Role sistem {$role->name} tidak dapat dihapus.");
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', "Role {$role->name} masih digunakan oleh {$role->users()->count()} pengguna.");
        }

        $name = $role->name;
        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', "Role {$name} berhasil dihapus.");
    }

    private function groupPermissions($permissions): array
    {
        $groups = [];
        $moduleLabels = [
            'dashboard' => 'Dashboard',
            'users' => 'Pengguna',
            'students' => 'Peserta Didik',
            'tutors' => 'Tutor',
            'classrooms' => 'Rombel',
            'programs' => 'Program',
            'academic-years' => 'Tahun Akademik',
            'semesters' => 'Semester',
            'subjects' => 'Mata Pelajaran',
            'schedules' => 'Jadwal',
            'attendance' => 'Absensi',
            'grades' => 'Nilai',
            'materials' => 'Materi',
            'assignments' => 'Tugas',
            'quizzes' => 'Kuis',
            'exams' => 'Ujian',
            'announcements' => 'Pengumuman',
            'documents' => 'Dokumen',
            'letters' => 'Surat',
            'reports' => 'Laporan',
            'settings' => 'Pengaturan',
            'audit' => 'Audit',
            'operator' => 'Operator',
        ];

        foreach ($permissions as $perm) {
            $parts = explode('.', $perm->name, 2);
            $module = $parts[0];
            $label = $moduleLabels[$module] ?? ucfirst(str_replace('-', ' ', $module));

            if (! isset($groups[$module])) {
                $groups[$module] = [
                    'label' => $label,
                    'permissions' => [],
                ];
            }

            $groups[$module]['permissions'][] = $perm;
        }

        ksort($groups);

        return $groups;
    }
}
