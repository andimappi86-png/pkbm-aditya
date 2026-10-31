<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\PenugasanMengajar;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CombinedDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function makeDualRoleTutorWali(): array
    {
        $user = User::factory()->create();
        $user->assignRole('tutor');
        $user->assignRole('wali-kelas');

        $subject = Subject::factory()->create(['nama' => 'Matematika']);

        $tutor = Tutor::factory()->create(['user_id' => $user->id]);
        $tutor->subjects()->attach($subject);

        $classroom = Classroom::factory()->create([
            'nama_rombel' => 'Paket C - Kelas Malam',
            'wali_kelas_id' => $user->id,
        ]);

        $penugasan = PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
        ]);

        // Student in classroom binaan
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');

        $student = Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $classroom->id,
        ]);

        $material = Material::factory()->create([
            'penugasan_mengajar_id' => $penugasan->id,
            'judul' => 'Materi Bilangan Berpangkat',
        ]);

        return [
            'user' => $user,
            'tutor' => $tutor,
            'classroom' => $classroom,
            'student' => $student,
            'subject' => $subject,
            'penugasan' => $penugasan,
            'material' => $material,
            'studentUser' => $studentUser,
        ];
    }

    public function test_dual_role_user_gets_combined_dashboard(): void
    {
        $data = $this->makeDualRoleTutorWali();
        $user = $data['user'];

        $this->actingAs($user);

        $response = $this->get(route('dashboard.combined'));
        $response->assertStatus(200);

        // Verify combined view is rendered (not redirected to exclusive dashboard)
        $response->assertViewIs('dashboards.combined');

        // Verify data present
        $response->assertSee('Selamat datang, '.$user->name);
        $response->assertSee('Tanggung Jawab Pengajaran (Tutor)');
        $response->assertSee('Tanggung Jawab Binaan (Wali Kelas)');
        $response->assertSee($data['classroom']->nama_rombel);
    }

    public function test_dual_role_user_sees_unified_sidebar_menu_without_duplicates(): void
    {
        $data = $this->makeDualRoleTutorWali();
        $user = $data['user'];

        $this->actingAs($user);

        $response = $this->get(route('dashboard.combined'));
        $response->assertStatus(200);

        // Verify combined sidebar sections appear
        $response->assertSee('Penugasan & Pembelajaran', false);
        $response->assertSee('Tanggung Jawab Wali Kelas');

        // Verify single sidebar (not duplicate @role blocks)
        // Scope counting to the sidebar only (main content also links these routes)
        $content = $response->getContent();
        $asideStart = strpos($content, '<aside');
        $asideEnd = strpos($content, '</aside>');
        $this->assertNotFalse($asideStart);
        $this->assertNotFalse($asideEnd);
        $sidebar = substr($content, $asideStart, $asideEnd - $asideStart);

        $materiCount = substr_count($sidebar, route('materi.index'));
        $this->assertEquals(1, $materiCount, 'Materi link should appear once in combined sidebar');

        // Verify combined sections don't repeat identical link URLs
        $absensiCount = substr_count($sidebar, route('absensi.index'));
        $this->assertEquals(1, $absensiCount, 'Absensi link should appear once in combined sidebar');

        $rekapCount = substr_count($sidebar, route('rekap.index'));
        $this->assertEquals(1, $rekapCount, 'Rekap link should appear once in combined sidebar');
    }

    public function test_tutor_only_user_gets_tutor_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('tutor');

        $tutor = Tutor::factory()->create(['user_id' => $user->id]);

        $subject = Subject::factory()->create(['nama' => 'Fisika']);
        $tutor->subjects()->attach($subject);

        $classroom = Classroom::factory()->create(['nama_rombel' => 'Paket B']);

        $penugasan = PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard.tutor'));
        $response->assertStatus(200);
        $response->assertViewIs('dashboards.tutor');

        // Should NOT see combined sections
        $response->assertDontSee('Tanggung Jawab Binaan (Wali Kelas)');
    }

    public function test_wali_kelas_only_user_gets_walikelas_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('wali-kelas');

        $classroom = Classroom::factory()->create(['wali_kelas_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard.walikelas'));
        $response->assertStatus(200);
        $response->assertViewIs('dashboards.walikelas');

        // Should NOT see tutor sections
        $response->assertDontSee('Tanggung Jawab Pengajaran (Tutor)');
    }

    public function test_operator_kepala_ketua_yayasan_peserta_didik_no_regression(): void
    {
        // Operator
        $operator = User::factory()->create();
        $operator->assignRole('operator');
        $this->actingAs($operator);
        $opResponse = $this->get(route($operator->dashboardRoute()));
        $opResponse->assertStatus(200);
        $this->assertEquals('operator.dashboard', $operator->dashboardRoute());

        // Kepala PKBM
        $kepala = User::factory()->create();
        $kepala->assignRole('kepala-pkbm');
        $this->actingAs($kepala);
        $kepalaResponse = $this->get(route($kepala->dashboardRoute()));
        $kepalaResponse->assertStatus(200);
        $this->assertEquals('dashboard.kepala', $kepala->dashboardRoute());

        // Ketua Yayasan
        $ketua = User::factory()->create();
        $ketua->assignRole('ketua-yayasan');
        $this->actingAs($ketua);
        $ketuaResponse = $this->get(route($ketua->dashboardRoute()));
        $ketuaResponse->assertStatus(200);
        $this->assertEquals('dashboard.yayasan', $ketua->dashboardRoute());

        // Peserta Didik
        $siswa = User::factory()->create();
        $siswa->assignRole('peserta-didik');
        $this->actingAs($siswa);
        $siswaResponse = $this->get(route($siswa->dashboardRoute()));
        $siswaResponse->assertStatus(200);
        $this->assertEquals('dashboard.siswa', $siswa->dashboardRoute());
    }

    public function test_dual_role_user_only_sees_assigned_teaching_and_binaan_data(): void
    {
        $data = $this->makeDualRoleTutorWali();
        $user = $data['user'];

        // Create a classroom NOT binaan by this user, with another tutor
        $otherClassroom = Classroom::factory()->create(['nama_rombel' => 'Paket C - Kelas Siang']);
        $otherTutor = Tutor::factory()->create();
        $otherSubject = Subject::factory()->create(['nama' => 'Bahasa Indonesia']);
        $otherPenugasan = PenugasanMengajar::factory()->create([
            'tutor_id' => $otherTutor->id,
            'classroom_id' => $otherClassroom->id,
            'subject_id' => $otherSubject->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard.combined'));
        $response->assertStatus(200);

        // Should see assigned classroom
        $response->assertSee($data['classroom']->nama_rombel);

        // Should NOT see non-assigned classroom
        $response->assertDontSee('Paket C - Kelas Siang');

        // Should see assigned subject
        $response->assertSee('Matematika');

        // Should NOT see non-assigned subject
        $response->assertDontSee('Bahasa Indonesia');

        // Assert view data reflects only assigned data
        $response->assertViewHas('myClassrooms', function ($classrooms) use ($data) {
            return $classrooms->count() === 1
                && $classrooms->first()->id === $data['classroom']->id;
        });

        $response->assertViewHas('myClasses', function ($classes) use ($data) {
            return $classes->count() === 1
                && $classes->first()->id === $data['classroom']->id;
        });

        $response->assertViewHas('mySubjects', function ($subjects) {
            return $subjects->count() === 1
                && $subjects->first()->nama === 'Matematika';
        });
    }

    public function test_dual_role_direct_url_access_enforces_server_authorization(): void
    {
        $data = $this->makeDualRoleTutorWali();
        $user = $data['user'];

        $this->actingAs($user);

        // Direct URL to exclusive dashboard should not 500, should work with authorized role
        $tutorResponse = $this->get(route('dashboard.tutor'));
        $tutorResponse->assertStatus(200);

        $walikelasResponse = $this->get(route('dashboard.walikelas'));
        $walikelasResponse->assertStatus(200);

        // Combined should work
        $combinedResponse = $this->get(route('dashboard.combined'));
        $combinedResponse->assertStatus(200);
    }

    public function test_dual_role_no_redirect_loop_or_500(): void
    {
        $data = $this->makeDualRoleTutorWali();
        $user = $data['user'];

        $this->actingAs($user);

        // Hit dashboard route (index) — should redirect to combined, not loop
        $response = $this->get(route('dashboard'));
        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard.combined'));

        // Combined should 200
        $combined = $this->get(route('dashboard.combined'));
        $combined->assertStatus(200);
    }

    public function test_combined_dashboard_does_not_leak_other_rombel_students(): void
    {
        $data = $this->makeDualRoleTutorWali();
        $user = $data['user'];

        // Create another classroom binaan by another wali kelas with students
        $otherWaliUser = User::factory()->create();
        $otherWaliUser->assignRole('wali-kelas');
        $otherClassroom = Classroom::factory()->create(['wali_kelas_id' => $otherWaliUser->id]);

        $otherStudentUser = User::factory()->create();
        $otherStudentUser->assignRole('peserta-didik');
        $otherStudent = Student::factory()->create([
            'user_id' => $otherStudentUser->id,
            'rombel_id' => $otherClassroom->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard.combined'));
        $response->assertStatus(200);

        // Ensure view data myClassrooms does not include other classroom
        $response->assertViewHas('myClassrooms', function ($classrooms) use ($otherClassroom) {
            return $classrooms->pluck('id')->doesntContain($otherClassroom->id);
        });

        // Ensure tutorStats total_siswa_ajar does not include other classroom students
        $response->assertViewHas('tutorStats', function ($stats) {
            return $stats['total_siswa_ajar'] === 1; // Only 1 student in assigned classroom
        });

        // Ensure waliStats total_siswa_binaan does not include other classroom students
        $response->assertViewHas('waliStats', function ($stats) {
            return $stats['total_siswa_binaan'] === 1; // Only 1 student in binaan classroom
        });
    }

    public function test_dual_role_dashboard_route_returns_combined_route_name(): void
    {
        $user = User::factory()->create();
        $user->assignRole('tutor');
        $user->assignRole('wali-kelas');

        $this->assertEquals('dashboard.combined', $user->dashboardRoute());

        // Tutor only
        $tutorUser = User::factory()->create();
        $tutorUser->assignRole('tutor');
        $this->assertEquals('dashboard.tutor', $tutorUser->dashboardRoute());

        // Wali kelas only
        $waliUser = User::factory()->create();
        $waliUser->assignRole('wali-kelas');
        $this->assertEquals('dashboard.walikelas', $waliUser->dashboardRoute());
    }

    public function test_combined_dashboard_renders_without_error_when_no_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('tutor');
        $user->assignRole('wali-kelas');

        // No Tutor record, no Classroom binaan
        $this->actingAs($user);

        $response = $this->get(route('dashboard.combined'));
        $response->assertStatus(200);
        $response->assertSee('Belum Ada Kelas Mengajar');
        $response->assertSee('Belum Ada Rombel Binaan');
    }

    public function test_announcements_respect_dual_role_and_classroom_scope(): void
    {
        $data = $this->makeDualRoleTutorWali();
        $user = $data['user'];

        // Announcement targeting tutor role
        $announcementTutor = Announcement::factory()->create([
            'judul' => 'Rapat Tutor',
            'target_role' => 'tutor',
            'status_aktif' => true,
            'published_at' => now()->subDay(),
        ]);

        // Announcement targeting wali-kelas role
        $announcementWali = Announcement::factory()->create([
            'judul' => 'Rapat Wali Kelas',
            'target_role' => 'wali-kelas',
            'status_aktif' => true,
            'published_at' => now()->subDay(),
        ]);

        // Announcement targeting specific classroom
        $announcementRomobel = Announcement::factory()->create([
            'judul' => 'Ujian Paket C',
            'target_rombel' => $data['classroom']->id,
            'status_aktif' => true,
            'published_at' => now()->subDay(),
        ]);

        // Announcement targeting general (no target)
        $announcementUmum = Announcement::factory()->create([
            'judul' => 'Libur Nasional',
            'target_role' => null,
            'target_rombel' => null,
            'status_aktif' => true,
            'published_at' => now()->subDay(),
        ]);

        // Announcement targeting unrelated classroom
        $unrelatedClassroom = Classroom::factory()->create(['nama_rombel' => 'Paket A']);
        $announcementUnrelated = Announcement::factory()->create([
            'judul' => 'Kegiatan Paket A',
            'target_rombel' => $unrelatedClassroom->id,
            'status_aktif' => true,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard.combined'));
        $response->assertStatus(200);

        $response->assertSee('Rapat Tutor');
        $response->assertSee('Rapat Wali Kelas');
        $response->assertSee('Ujian Paket C');
        $response->assertSee('Libur Nasional');

        // Should NOT see announcement targeting unrelated classroom
        $response->assertDontSee('Kegiatan Paket A');
    }
}
