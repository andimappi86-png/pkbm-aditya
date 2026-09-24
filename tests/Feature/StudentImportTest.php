<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentImportTest extends TestCase
{
    use RefreshDatabase;

    protected $operator;
    protected $program;
    protected $academicYear;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'operator']);
        $permission = Permission::create(['name' => 'students.create']);
        $role->givePermissionTo($permission);

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->program = Program::create([
            'nama' => 'Paket C',
            'kode' => 'PKC',
            'status_aktif' => true,
        ]);

        $this->academicYear = AcademicYear::create([
            'nama' => '2026/2027',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'status_aktif' => true,
        ]);
    }

    public function test_csv_student_valid_preview()
    {
        $csvContent = "nis,nik,nama_lengkap,jenis_kelamin,alamat,program_id,tahun_ajaran_id,status\n"
            . "S001,1234567890123456,Siswa Test,Laki-laki,Mamuju,{$this->program->id},{$this->academicYear->id},aktif\n";

        $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

        $response = $this->actingAs($this->operator)
            ->post(route('peserta-didik.import.preview'), [
                'file' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertViewHas('results', function ($results) {
            return $results['success'] === 1 && $results['failed'] === 0;
        });
    }

    public function test_csv_campuran_siswa_tutor_rejected()
    {
        $csvContent = "nis,nik,nama_lengkap,jenis_kelamin,alamat,program_id,tahun_ajaran_id,status,tipe\n"
            . "S001,1234567890123456,Siswa Test,Laki-laki,Mamuju,{$this->program->id},{$this->academicYear->id},aktif,siswa\n"
            . "T001,6543210987654321,Tutor Test,Perempuan,Mamuju,{$this->program->id},{$this->academicYear->id},aktif,tutor\n";

        $file = UploadedFile::fake()->createWithContent('campuran.csv', $csvContent);

        $response = $this->actingAs($this->operator)
            ->post(route('peserta-didik.import.preview'), [
                'file' => $file,
            ]);

        $response->assertRedirect(route('peserta-didik.import'));
        $response->assertSessionHas('error', 'File mengandung data Tutor. Halaman ini hanya menerima Peserta Didik.');
    }

    public function test_invalid_nik_row()
    {
        $csvContent = "nis,nik,nama_lengkap,jenis_kelamin,alamat,program_id,tahun_ajaran_id,status\n"
            . "S001,1234,Siswa Invalid NIK,Laki-laki,Mamuju,{$this->program->id},{$this->academicYear->id},aktif\n";

        $file = UploadedFile::fake()->createWithContent('invalid_nik.csv', $csvContent);

        $response = $this->actingAs($this->operator)
            ->post(route('peserta-didik.import.preview'), [
                'file' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertViewHas('results', function ($results) {
            return $results['failed'] === 1;
        });
    }
}
