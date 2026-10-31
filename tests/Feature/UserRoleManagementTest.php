<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    private User $kepala;

    private User $tutorUser;

    private Tutor $tutor;

    private User $waliUser;

    private User $ketua;

    private User $siswaUser;

    private Student $studentA;

    private Student $studentB;

    private Classroom $classroomA;

    private Classroom $classroomB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        // Roles
        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->kepala = User::factory()->create();
        $this->kepala->assignRole('kepala-pkbm');

        $this->tutorUser = User::factory()->create();
        $this->tutorUser->assignRole('tutor');
        $this->tutor = Tutor::factory()->create(['user_id' => $this->tutorUser->id]);

        $this->waliUser = User::factory()->create();
        $this->waliUser->assignRole('wali-kelas');

        $this->ketua = User::factory()->create();
        $this->ketua->assignRole('ketua-yayasan');

        $this->siswaUser = User::factory()->create();
        $this->siswaUser->assignRole('peserta-didik');

        // Master data
        $program = Program::factory()->create(['status_aktif' => true]);
        $academicYear = AcademicYear::factory()->create(['status_aktif' => true]);

        $this->classroomA = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $academicYear->id,
            'status_aktif' => true,
        ]);
        $this->classroomB = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $academicYear->id,
            'status_aktif' => true,
        ]);

        // Students (unlinked initially)
        $this->studentA = Student::factory()->create([
            'rombel_id' => $this->classroomA->id,
            'status' => 'aktif',
        ]);
        $this->studentB = Student::factory()->create([
            'rombel_id' => $this->classroomB->id,
            'status' => 'aktif',
        ]);
    }

    public function test_operator_can_view_users_index(): void
    {
        $response = $this->actingAs($this->operator)->get(route('users.index'));

        $response->assertOk()
            ->assertViewIs('users.index')
            ->assertViewHas('users')
            ->assertViewHas('roles')
            ->assertViewHas('stats');
    }

    public function test_operator_can_view_user_create_page(): void
    {
        $response = $this->actingAs($this->operator)->get(route('users.create'));

        $response->assertOk()
            ->assertViewIs('users.create')
            ->assertViewHas('roles')
            ->assertViewHas('unlinkedStudents')
            ->assertViewHas('unlinkedTutors');
    }

    public function test_operator_can_create_user_with_peserta_didik_role_and_student_link(): void
    {
        // Ensure student is unlinked
        $student = Student::factory()->create(['user_id' => null, 'status' => 'aktif']);

        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@test.example.com',
            'phone' => '08123456789',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'peserta-didik',
            'student_id' => $student->id,
        ]);

        $response->assertRedirect(route('users.show', User::where('email', 'budi@test.example.com')->first()));
        $response->assertSessionHas('success');

        $user = User::where('email', 'budi@test.example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('peserta-didik'));
        $this->assertEquals($student->id, $user->student->id);
        $this->assertEquals('active', $user->status);
    }

    public function test_operator_cannot_create_user_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@test.example.com']);

        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Another User',
            'email' => 'existing@test.example.com',
            'phone' => '08123456789',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'peserta-didik',
            'student_id' => $this->studentA->id,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_operator_can_create_user_with_tutor_role_and_link(): void
    {
        $tutor = Tutor::factory()->create(['user_id' => null]);

        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Siti Rahma',
            'email' => 'siti@test.example.com',
            'phone' => '08129876543',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'tutor',
            'tutor_id' => $tutor->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user = User::where('email', 'siti@test.example.com')->first();
        $this->assertTrue($user->hasRole('tutor'));
        $this->assertEquals($tutor->id, $user->tutor->id);
    }

    public function test_operator_cannot_link_already_linked_student(): void
    {
        // Create user already linked to studentA
        $existingUser = User::factory()->create();
        $existingUser->assignRole('peserta-didik');
        $this->studentA->update(['user_id' => $existingUser->id]);

        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'New User',
            'email' => 'new@test.example.com',
            'phone' => '08111111111',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'peserta-didik',
            'student_id' => $this->studentA->id,
        ]);

        $response->assertSessionHasErrors('student_id');
    }

    public function test_operator_can_view_user_detail(): void
    {
        $response = $this->actingAs($this->operator)->get(route('users.show', $this->operator));

        $response->assertOk()
            ->assertViewIs('users.show')
            ->assertViewHas('user');
    }

    public function test_operator_can_update_user_basic_info(): void
    {
        $response = $this->actingAs($this->operator)->put(route('users.update', $this->operator), [
            'name' => 'Updated Name',
            'email' => 'updated@test.example.com',
            'phone' => '08999999999',
            'status' => 'active',
            'role' => 'operator',
        ]);

        $response->assertRedirect(route('users.show', $this->operator));
        $response->assertSessionHas('success');

        $this->operator->refresh();
        $this->assertEquals('Updated Name', $this->operator->name);
        $this->assertEquals('updated@test.example.com', $this->operator->email);
    }

    public function test_operator_cannot_change_own_role(): void
    {
        $response = $this->actingAs($this->operator)->put(route('users.update', $this->operator), [
            'name' => $this->operator->name,
            'email' => $this->operator->email,
            'status' => 'active',
            'role' => 'kepala-pkbm',
        ]);

        $response->assertSessionHasErrors('role');
    }

    public function test_operator_cannot_deactivate_own_account(): void
    {
        $response = $this->actingAs($this->operator)->put(route('users.update', $this->operator), [
            'name' => $this->operator->name,
            'email' => $this->operator->email,
            'status' => 'inactive',
            'role' => 'operator',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_operator_can_reset_password(): void
    {
        $targetUser = User::factory()->create();
        $targetUser->assignRole('peserta-didik');

        $response = $this->actingAs($this->operator)->post(route('users.reset-password', $targetUser), [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify password was changed
        $this->assertTrue(Hash::check('newpassword123', $targetUser->fresh()->password));
    }

    public function test_operator_can_toggle_user_status(): void
    {
        $targetUser = User::factory()->create(['status' => 'active']);
        $targetUser->assignRole('peserta-didik');

        $response = $this->actingAs($this->operator)->patch(route('users.toggle-status', $targetUser));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('inactive', $targetUser->fresh()->status);
    }

    public function test_operator_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->operator)->delete(route('users.destroy', $this->operator));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');

        $this->assertTrue(User::whereKey($this->operator->id)->exists());
    }

    public function test_operator_cannot_delete_last_admin(): void
    {
        // Keep operator able to manage users without an admin role,
        // so kepala-pkbm becomes the last active administrator.
        $this->operator->removeRole('operator');
        $this->operator->givePermissionTo(['users.manage', 'users.view', 'dashboard.view']);

        $response = $this->actingAs($this->operator)->delete(route('users.destroy', $this->kepala));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Akun ini adalah administrator aktif terakhir dan tidak dapat dihapus.');
        $this->assertTrue(User::whereKey($this->kepala->id)->exists());
    }

    public function test_operator_can_delete_user_without_relations(): void
    {
        $targetUser = User::factory()->create();
        $targetUser->assignRole('peserta-didik');

        $response = $this->actingAs($this->operator)->delete(route('users.destroy', $targetUser));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');
        $this->assertFalse(User::whereKey($targetUser->id)->exists());
    }

    public function test_operator_cannot_delete_user_with_student_relation(): void
    {
        $userWithStudent = User::factory()->create();
        $userWithStudent->assignRole('peserta-didik');
        $student = Student::factory()->create(['user_id' => $userWithStudent->id]);

        $response = $this->actingAs($this->operator)->delete(route('users.destroy', $userWithStudent));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Akun masih terhubung dengan profil siswa/tutor. Lepas tautan profil terlebih dahulu atau nonaktifkan akun.');
    }

    // --- ROLE MANAGEMENT ---

    public function test_operator_can_view_roles_index(): void
    {
        $response = $this->actingAs($this->operator)->get(route('roles.index'));

        $response->assertOk()
            ->assertViewIs('roles.index')
            ->assertViewHas('roles')
            ->assertViewHas('permissionGroups');
    }

    public function test_operator_can_view_role_create_page(): void
    {
        $response = $this->actingAs($this->operator)->get(route('roles.create'));

        $response->assertOk()
            ->assertViewIs('roles.create')
            ->assertViewHas('permissionGroups');
    }

    public function test_operator_can_create_custom_role_with_permissions(): void
    {
        $perms = ['students.view', 'students.create'];

        $response = $this->actingAs($this->operator)->post(route('roles.store'), [
            'name' => 'custom-operator',
            'permissions' => $perms,
        ]);

        $response->assertRedirect(route('roles.edit', Role::where('name', 'custom-operator')->first()));
        $response->assertSessionHas('success');

        $role = Role::where('name', 'custom-operator')->first();
        $this->assertNotNull($role);
        $this->assertEqualsCanonicalizing($perms, $role->permissions->pluck('name')->all());
    }

    public function test_operator_can_view_role_edit_page(): void
    {
        $role = Role::where('name', 'wali-kelas')->first();

        $response = $this->actingAs($this->operator)->get(route('roles.edit', $role));

        $response->assertOk()
            ->assertViewIs('roles.edit')
            ->assertViewHas('role')
            ->assertViewHas('permissionGroups');
    }

    public function test_operator_can_update_role_permissions(): void
    {
        $role = Role::where('name', 'wali-kelas')->first();
        $originalPerms = $role->permissions->pluck('name')->all();

        $newPerms = ['students.view', 'classrooms.view', 'attendance.manage'];

        $response = $this->actingAs($this->operator)->put(route('roles.update', $role), [
            'name' => $role->name,
            'permissions' => $newPerms,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $role->refresh();
        $this->assertEqualsCanonicalizing($newPerms, $role->permissions->pluck('name')->all());
    }

    public function test_operator_cannot_rename_system_role(): void
    {
        $role = Role::where('name', 'operator')->first();

        $response = $this->actingAs($this->operator)->put(route('roles.update', $role), [
            'name' => 'super-operator',
            'permissions' => $role->permissions->pluck('name')->all(),
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_operator_cannot_remove_required_permissions_from_operator_role(): void
    {
        $role = Role::where('name', 'operator')->first();

        $response = $this->actingAs($this->operator)->put(route('roles.update', $role), [
            'name' => 'operator',
            'permissions' => ['dashboard.view'], // missing users.manage
        ]);

        $response->assertSessionHasErrors('permissions');
    }

    public function test_operator_cannot_delete_system_role(): void
    {
        $role = Role::where('name', 'peserta-didik')->first();

        $response = $this->actingAs($this->operator)->delete(route('roles.destroy', $role));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Role sistem peserta-didik tidak dapat dihapus.');
        $this->assertTrue(Role::whereKey($role->id)->exists());
    }

    public function test_operator_cannot_delete_role_in_use(): void
    {
        $customRole = Role::create(['name' => 'custom-test', 'guard_name' => 'web']);
        User::factory()->create()->assignRole('custom-test');

        $response = $this->actingAs($this->operator)->delete(route('roles.destroy', $customRole));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_operator_can_delete_unused_custom_role(): void
    {
        $customRole = Role::create(['name' => 'custom-unused', 'guard_name' => 'web']);

        $response = $this->actingAs($this->operator)->delete(route('roles.destroy', $customRole));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertFalse(Role::whereKey($customRole->id)->exists());
    }

    // --- ACCESS CONTROL ---

    public function test_non_operator_cannot_access_users_index(): void
    {
        $response = $this->actingAs($this->tutorUser)->get(route('users.index'));

        $response->assertRedirect(route('dashboard.tutor'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');
    }

    public function test_non_operator_cannot_access_roles_index(): void
    {
        $response = $this->actingAs($this->tutorUser)->get(route('roles.index'));

        $response->assertRedirect(route('dashboard.tutor'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');
    }

    public function test_ketua_yayasan_cannot_access_users_index(): void
    {
        $response = $this->actingAs($this->ketua)->get(route('users.index'));

        $response->assertRedirect(route('dashboard.yayasan'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');
    }

    // --- OPERATOR DASHBOARD ---

    public function test_operator_dashboard_renders_with_operational_indicators(): void
    {
        $response = $this->actingAs($this->operator)->get(route('operator.dashboard'));

        $response->assertOk()
            ->assertViewIs('operator.dashboard')
            ->assertViewHas('stats')
            ->assertViewHas('ops')
            ->assertViewHas('rolesCount')
            ->assertViewHas('recentAnnouncements');

        // Check key indicators present
        $ops = $response->viewData('ops');
        $this->assertArrayHasKey('total_users', $ops);
        $this->assertArrayHasKey('inactive_accounts', $ops);
        $this->assertArrayHasKey('unlinked_student_accounts', $ops);
        $this->assertArrayHasKey('students_without_account', $ops);
        $this->assertArrayHasKey('total_permissions', $ops);
    }

    public function test_operator_dashboard_shows_quick_actions(): void
    {
        $response = $this->actingAs($this->operator)->get(route('operator.dashboard'));

        // Check quick action links exist
        $response->assertSee(route('peserta-didik.create'))
            ->assertSee(route('tutors.create'))
            ->assertSee(route('rombel.create'))
            ->assertSee(route('announcements.create'))
            ->assertSee(route('users.create'))
            ->assertSee(route('roles.index'))
            ->assertSee(route('tugas.index'))
            ->assertSee(route('operator.import.index'));
    }

    public function test_operator_dashboard_shows_recent_announcements(): void
    {
        $announcement = Announcement::factory()->create([
            'judul' => 'Pengumuman Test',
            'status_aktif' => true,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->operator)->get(route('operator.dashboard'));

        $response->assertSee('Pengumuman Terbaru')
            ->assertSee($announcement->judul);
    }
}
