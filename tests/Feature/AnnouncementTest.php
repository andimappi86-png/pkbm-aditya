<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
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

    public function test_admin_can_view_announcements_index()
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('announcements.index'));

        $response->assertStatus(200);
        $response->assertViewIs('announcements.index');
    }

    public function test_admin_can_create_announcement()
    {
        $admin = $this->createAdminUser();

        $data = [
            'judul' => 'Pengumuman Baru',
            'isi' => 'Ini isi pengumuman',
            'kategori' => 'Umum',
            'status_aktif' => true,
        ];

        $response = $this->actingAs($admin)->post(route('announcements.store'), $data);

        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', [
            'judul' => 'Pengumuman Baru',
            'kategori' => 'Umum',
            'published_at' => null, // Was not published
        ]);
    }

    public function test_admin_can_publish_announcement()
    {
        $admin = $this->createAdminUser();

        $data = [
            'judul' => 'Pengumuman Penting',
            'isi' => 'Ini isi pengumuman',
            'kategori' => 'Akademik',
            'publish' => 1,
        ];

        $response = $this->actingAs($admin)->post(route('announcements.store'), $data);

        $this->assertDatabaseHas('announcements', [
            'judul' => 'Pengumuman Penting',
            'kategori' => 'Akademik',
        ]);

        $announcement = Announcement::where('judul', 'Pengumuman Penting')->first();
        $this->assertNotNull($announcement->published_at);
        $this->assertTrue($announcement->status_aktif);
    }

    public function test_student_can_view_published_global_announcement()
    {
        $student = $this->createStudentUser();
        $admin = $this->createAdminUser();

        $announcement = Announcement::factory()->create([
            'user_id' => $admin->id,
            'status_aktif' => true,
            'published_at' => now()->subDay(),
            'target_role' => null,
        ]);

        $response = $this->actingAs($student)->get(route('announcements.show', $announcement));
        $response->assertStatus(200);
    }

    public function test_student_cannot_view_draft_announcement()
    {
        $student = $this->createStudentUser();
        $admin = $this->createAdminUser();

        $announcement = Announcement::factory()->create([
            'user_id' => $admin->id,
            'status_aktif' => false,
            'published_at' => null,
        ]);

        $response = $this->actingAs($student)->get(route('announcements.show', $announcement));
        $response->assertStatus(403);
    }
}
