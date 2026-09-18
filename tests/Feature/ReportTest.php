<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function createAdminUser()
    {
        $user = User::factory()->create();
        $user->assignRole('kepala-pkbm');

        return $user;
    }

    private function createStudentUser()
    {
        $user = User::factory()->create();
        $user->assignRole('peserta-didik');

        return $user;
    }

    public function test_admin_can_view_reports_index()
    {
        $admin = $this->createAdminUser();
        $response = $this->actingAs($admin)->get(route('reports.index'));
        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
    }

    public function test_student_cannot_view_reports()
    {
        $student = $this->createStudentUser();
        $response = $this->actingAs($student)->get(route('reports.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_students_report()
    {
        $admin = $this->createAdminUser();
        $response = $this->actingAs($admin)->get(route('reports.students'));
        $response->assertStatus(200);
        $response->assertViewIs('reports.students');
    }

    public function test_admin_can_view_attendance_report()
    {
        $admin = $this->createAdminUser();
        $response = $this->actingAs($admin)->get(route('reports.attendance'));
        $response->assertStatus(200);
        $response->assertViewIs('reports.attendance');
    }

    public function test_admin_can_print_report()
    {
        $admin = $this->createAdminUser();
        $response = $this->actingAs($admin)->get(route('reports.print', ['type' => 'students']));
        $response->assertStatus(200);
        $response->assertViewIs('reports.print.students');
    }
}
