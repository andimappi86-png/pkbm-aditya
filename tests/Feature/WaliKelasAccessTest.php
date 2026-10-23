<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WaliKelasAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $waliA;

    protected User $waliB;

    protected Classroom $classroomA;

    protected Classroom $classroomB;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'classrooms.view', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'wali-kelas', 'guard_name' => 'web']);
        $role->givePermissionTo('classrooms.view');

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $this->waliA = User::factory()->create();
        $this->waliA->assignRole('wali-kelas');
        $this->classroomA = Classroom::factory()->create([
            'wali_kelas_id' => $this->waliA->id,
            'program_id' => $program->id,
            'tahun_ajaran_id' => $academicYear->id,
        ]);

        $this->waliB = User::factory()->create();
        $this->waliB->assignRole('wali-kelas');
        $this->classroomB = Classroom::factory()->create([
            'wali_kelas_id' => $this->waliB->id,
            'program_id' => $program->id,
            'tahun_ajaran_id' => $academicYear->id,
        ]);
    }

    /**
     * 1. Wali kelas terautentikasi dapat membuka rombel milik sendiri.
     */
    public function test_wali_kelas_can_view_own_classroom()
    {
        $response = $this->actingAs($this->waliA)->get(route('rombel.show', $this->classroomA));

        $response->assertStatus(200);
        $response->assertSee($this->classroomA->nama_rombel);
    }

    /**
     * 2. Wali kelas ditolak saat mengakses rombel lain (redirect ke dashboard dengan pesan error oleh Exception Handler).
     */
    public function test_wali_kelas_cannot_view_other_classrooms()
    {
        $response = $this->actingAs($this->waliA)->get(route('rombel.show', $this->classroomB));

        // Exception handler di bootstrap/app.php menangkap 403 HTTP Exception untuk request web terautentikasi
        // dan melakukan redirect 302 ke dashboardRoute() ('/dashboard/walikelas') dengan pesan error.
        $response->assertRedirect(route('dashboard.walikelas'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');

        // Untuk JSON request, 403 returned secara eksplisit
        $jsonResponse = $this->actingAs($this->waliA)->getJson(route('rombel.show', $this->classroomB));
        $jsonResponse->assertStatus(403);
    }

    /**
     * 3. Pengguna tidak terautentikasi di-redirect ke halaman login oleh middleware auth.
     */
    public function test_unauthenticated_user_redirected_to_login()
    {
        $response = $this->get(route('rombel.show', $this->classroomA));

        $response->assertRedirect(route('login'));
    }

    /**
     * 4. Pengguna tanpa permission/role di-redirect sesuai kebijakan exception handler.
     */
    public function test_user_without_permission_is_denied()
    {
        $userTanpaAkses = User::factory()->create();

        $response = $this->actingAs($userTanpaAkses)->get(route('rombel.show', $this->classroomA));

        $response->assertRedirect(route($userTanpaAkses->dashboardRoute()));
        $response->assertSessionHas('error');
    }
}
