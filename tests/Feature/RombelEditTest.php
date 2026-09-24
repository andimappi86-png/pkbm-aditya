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

class RombelEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'kepala-pkbm']);
        Role::firstOrCreate(['name' => 'wali-kelas']);
        $p_create = Permission::firstOrCreate(['name' => 'classrooms.create']);
        $p_update = Permission::firstOrCreate(['name' => 'classrooms.update']);
        $role->givePermissionTo([$p_create, $p_update]);

        $this->user = User::factory()->create();
        $this->user->assignRole('kepala-pkbm');

        $this->program = Program::factory()->create(['status_aktif' => true]);
        $this->academicYear = AcademicYear::factory()->create(['status_aktif' => true]);

        $this->waliKelasValid = User::factory()->create(['name' => 'Wali Kelas Unik']);
        $this->waliKelasValid->assignRole('wali-kelas');

        $this->waliKelasDuplikat1 = User::factory()->create(['name' => 'Wali Kelas Duplikat']);
        $this->waliKelasDuplikat1->assignRole('wali-kelas');
        $this->waliKelasDuplikat2 = User::factory()->create(['name' => 'Wali Kelas Duplikat']);
        $this->waliKelasDuplikat2->assignRole('wali-kelas');

        $this->classroom = Classroom::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);
    }

    public function test_create_valid_wali_kelas()
    {
        $response = $this->actingAs($this->user)->post(route('rombel.store'), [
            'nama_rombel' => 'Rombel Test 1',
            'kode_rombel' => 'R-01',
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'wali_kelas_name' => 'Wali Kelas Unik',
            'status_aktif' => true,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('rombel.index'));
        $this->assertDatabaseHas('classrooms', [
            'nama_rombel' => 'Rombel Test 1',
            'wali_kelas_id' => $this->waliKelasValid->id,
        ]);
    }

    public function test_create_invalid_wali_kelas()
    {
        $response = $this->actingAs($this->user)->post(route('rombel.store'), [
            'nama_rombel' => 'Rombel Test 2',
            'kode_rombel' => 'R-02',
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'wali_kelas_name' => 'Tidak Ada Nama Ini',
            'status_aktif' => true,
        ]);

        $response->assertSessionHasErrors(['wali_kelas_name' => 'Wali kelas tidak ditemukan.']);
    }

    public function test_create_duplicate_wali_kelas()
    {
        $response = $this->actingAs($this->user)->post(route('rombel.store'), [
            'nama_rombel' => 'Rombel Test 3',
            'kode_rombel' => 'R-03',
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'wali_kelas_name' => 'Wali Kelas Duplikat',
            'status_aktif' => true,
        ]);

        $response->assertSessionHasErrors(['wali_kelas_name' => 'Nama wali kelas tidak unik.']);
    }

    public function test_update_valid_wali_kelas()
    {
        $response = $this->actingAs($this->user)->put(route('rombel.update', $this->classroom->id), [
            'nama_rombel' => 'Rombel Edited',
            'kode_rombel' => $this->classroom->kode_rombel,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'wali_kelas_name' => 'Wali Kelas Unik',
            'status_aktif' => true,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('rombel.index'));
        $this->assertDatabaseHas('classrooms', [
            'id' => $this->classroom->id,
            'nama_rombel' => 'Rombel Edited',
            'wali_kelas_id' => $this->waliKelasValid->id,
        ]);
    }

    public function test_update_invalid_wali_kelas()
    {
        $response = $this->actingAs($this->user)->put(route('rombel.update', $this->classroom->id), [
            'nama_rombel' => 'Rombel Edited 2',
            'kode_rombel' => $this->classroom->kode_rombel,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'wali_kelas_name' => 'Tidak Ada Nama Ini',
            'status_aktif' => true,
        ]);

        $response->assertSessionHasErrors(['wali_kelas_name' => 'Wali kelas tidak ditemukan.']);

        $this->assertDatabaseMissing('classrooms', [
            'nama_rombel' => 'Rombel Edited 2',
        ]);
    }

    public function test_update_empty_wali_kelas()
    {
        // First set a valid one
        $this->classroom->update(['wali_kelas_id' => $this->waliKelasValid->id]);

        $response = $this->actingAs($this->user)->put(route('rombel.update', $this->classroom->id), [
            'nama_rombel' => 'Rombel Edited 3',
            'kode_rombel' => $this->classroom->kode_rombel,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'wali_kelas_name' => '', // Empty!
            'status_aktif' => true,
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('classrooms', [
            'id' => $this->classroom->id,
            'wali_kelas_id' => null,
        ]);
    }
}
