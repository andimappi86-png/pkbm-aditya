<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_is_reachable_for_guests(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('PKBM Aditya', false)
            ->assertSee('Masuk Sistem')
            ->assertSee(route('login'), false);
    }

    public function test_homepage_lists_active_programs_and_public_statistics(): void
    {
        $program = Program::factory()->create([
            'kode' => 'PB',
            'nama' => 'Paket B Setara SMP',
            'status_aktif' => true,
        ]);

        Program::factory()->create(['kode' => 'PZ', 'nama' => 'Program Nonaktif', 'status_aktif' => false]);

        Student::factory()->count(3)->create(['status' => 'aktif']);
        Student::factory()->create(['status' => 'lulus']);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee($program->nama)
            ->assertDontSee('Program Nonaktif')
            ->assertSee('data-count-to="3"', false);
    }

    public function test_homepage_only_displays_announcements_intended_for_the_public(): void
    {
        $public = Announcement::factory()->create([
            'judul' => 'Pengumuman Terbuka Untuk Umum',
            'status_aktif' => true,
            'published_at' => now()->subDay(),
        ]);

        $targeted = Announcement::factory()->create([
            'judul' => 'Pengumuman Khusus Tutor',
            'target_role' => 'tutor',
            'status_aktif' => true,
            'published_at' => now()->subDay(),
        ]);

        $draft = Announcement::factory()->create([
            'judul' => 'Pengumuman Masih Nonaktif',
            'status_aktif' => false,
            'published_at' => now()->subDay(),
        ]);

        $scheduled = Announcement::factory()->create([
            'judul' => 'Pengumuman Terjadwal Mendatang',
            'status_aktif' => true,
            'published_at' => now()->addWeek(),
        ]);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee($public->judul)
            ->assertDontSee($targeted->judul)
            ->assertDontSee($draft->judul)
            ->assertDontSee($scheduled->judul);
    }

    public function test_homepage_shows_an_empty_state_when_no_public_announcement_exists(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('Belum ada pengumuman publik');
    }
}
