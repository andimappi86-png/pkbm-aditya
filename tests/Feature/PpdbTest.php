<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Ppdb;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\RoleCalonPesertaSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpdbTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(RoleCalonPesertaSeeder::class);
    }

    private function createAdminUser()
    {
        $user = User::factory()->create();
        $user->assignRole('kepala-pkbm');

        return $user;
    }

    private function createCalonUser()
    {
        $user = User::factory()->create();
        $user->assignRole('calon-peserta');

        return $user;
    }

    public function test_calon_peserta_can_view_registration_form()
    {
        $user = $this->createCalonUser();

        // Need active program and academic year
        Program::factory()->create(['status_aktif' => true]);
        AcademicYear::factory()->create(['status_aktif' => true]);

        $response = $this->actingAs($user)->get(route('ppdb.create'));

        $response->assertStatus(200);
        $response->assertViewIs('ppdb.create');
    }

    public function test_calon_peserta_can_submit_registration()
    {
        $user = $this->createCalonUser();

        $program = Program::factory()->create(['status_aktif' => true]);
        $academicYear = AcademicYear::factory()->create(['status_aktif' => true]);

        $data = [
            'program_id' => $program->id,
            'tahun_ajaran_id' => $academicYear->id,
            'nik' => '1234567890123456',
            'nisn' => '1234567890',
            'nama_lengkap' => 'Budi Santoso',
            'nama_panggilan' => 'Budi',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Mamuju',
            'tanggal_lahir' => '2010-05-15',
            'agama' => 'Islam',
            'alamat' => 'Jl. Poros Mamuju',
            'kabupaten' => 'Mamuju',
            'provinsi' => 'Sulawesi Barat',
            'no_hp' => '081234567890',
            'email' => 'budi.santoso@example.com',
            'nama_ayah' => 'Ahmad',
            'nama_ibu' => 'Siti',
        ];

        $response = $this->actingAs($user)->post(route('ppdb.store'), $data);

        $ppdb = Ppdb::where('nik', '1234567890123456')->first();

        $this->assertNotNull($ppdb);
        $this->assertEquals($user->id, $ppdb->user_id);
        $response->assertRedirect(route('ppdb.show', $ppdb));
    }

    public function test_calon_peserta_can_view_own_registration()
    {
        $user = $this->createCalonUser();
        $ppdb = Ppdb::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('ppdb.show', $ppdb));

        $response->assertStatus(200);
        $response->assertViewIs('ppdb.show');
    }

    public function test_calon_peserta_cannot_view_others_registration()
    {
        $user1 = $this->createCalonUser();
        $user2 = $this->createCalonUser();
        $ppdb = Ppdb::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->get(route('ppdb.show', $ppdb));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_all_registrations()
    {
        $admin = $this->createAdminUser();
        Ppdb::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('ppdb.index'));

        $response->assertStatus(200);
        $response->assertViewIs('ppdb.index');
        $response->assertViewHas('ppdbs');
    }

    public function test_admin_can_verify_registration()
    {
        $admin = $this->createAdminUser();
        $ppdb = Ppdb::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($admin)->post(route('ppdb.verify', $ppdb), [
            'status' => 'diverifikasi',
            'catatan_verifikasi' => 'Berkas lengkap',
        ]);

        $response->assertRedirect(route('ppdb.show', $ppdb));

        $ppdb->refresh();
        $this->assertEquals('diverifikasi', $ppdb->status);
        $this->assertEquals('Berkas lengkap', $ppdb->catatan_verifikasi);
        $this->assertEquals($admin->id, $ppdb->verified_by);
    }
}
