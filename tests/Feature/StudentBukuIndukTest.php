<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentBukuIndukTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_full_buku_induk_persistence_and_encryption(): void
    {
        $operator = User::factory()->create();
        $operator->assignRole('operator');

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $payload = [
            'nis' => 'NIS12345',
            'nisn' => '9876543210',
            'nik' => '3201011234560001',
            'nama_lengkap' => 'Budi Setiawan',
            'nama_panggilan' => 'Budi',
            'jenis_kelamin' => 'Laki-laki',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-05-12',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'alamat' => 'Jl. Merdeka No. 10',
            'rt' => '01',
            'rw' => '05',
            'kelurahan' => 'Menteng',
            'kecamatan' => 'Menteng',
            'kabupaten' => 'Jakarta Pusat',
            'provinsi' => 'DKI Jakarta',
            'no_hp' => '081234567890',
            'email' => 'budi@example.com',
            'program_id' => $program->id,
            'tahun_ajaran_id' => $academicYear->id,
            'status' => 'aktif',
            // Buku Induk fields
            'nomor_akta_lahir' => 'AKTA-998877',
            'nomor_kk' => '3201019988770001',
            'urutan_anak' => 2,
            'titik_koordinat' => '-6.200, 106.816',
            'jenis_tempat_tinggal' => 'Milik Sendiri',
            'alat_transportasi' => 'Jalan Kaki',
            'jarak_rumah' => '1 KM',
            'kebutuhan_khusus' => 'Tidak Ada',
            'berat_badan' => 45.5,
            'tinggi_badan' => 155.0,
            'lingkar_kepala' => 54.0,
            'kps_status' => true,
            'nomor_kps' => 'KPS-123',
            'pkh_status' => false,
            'kip_status' => true,
            'nama_kip' => 'Budi KIP',
            'kks_nomor' => 'KKS-456',
            'pip_kelayakan' => true,
            'pip_alasan' => 'Kurang Mampu',
            'bank_nama' => 'BRI',
            'rekening_nomor' => '1234567890',
            'rekening_nama' => 'Budi Setiawan',
            'nomor_peserta_un' => 'UN-001',
            'ijazah_nomor' => 'IJZ-001',
            'skhun_nomor' => 'SKH-001',
            'catatan' => 'Catatan khusus siswa',
            'parent_details' => [
                'ayah' => [
                    'relationship_type' => 'ayah',
                    'name' => 'Ayah Budi',
                    'nik' => '3201011111110001',
                    'birth_year' => 1975,
                    'education' => 'SMA',
                    'occupation' => 'Wiraswasta',
                    'monthly_income' => 3000000,
                    'phone' => '081111111111',
                ],
                'ibu' => [
                    'relationship_type' => 'ibu',
                    'name' => 'Ibu Budi',
                    'nik' => '3201012222220002',
                    'birth_year' => 1980,
                    'education' => 'SMP',
                    'occupation' => 'Ibu Rumah Tangga',
                    'monthly_income' => 0,
                    'phone' => '082222222222',
                ],
            ],
        ];

        $response = $this->actingAs($operator)->post(route('peserta-didik.store'), $payload);
        $response->assertRedirect(route('peserta-didik.index'));

        $student = Student::where('nis', 'NIS12345')->first();
        $this->assertNotNull($student);
        $this->assertEquals('Budi Setiawan', $student->nama_lengkap);
        $this->assertEquals('AKTA-998877', $student->nomor_akta_lahir);
        $this->assertEquals(2, $student->urutan_anak);
        $this->assertTrue($student->kps_status);
        $this->assertEquals(45.5, $student->berat_badan);

        // Verify parent details
        $this->assertCount(2, $student->parentDetails);
        $ayah = $student->parentDetails()->where('relationship_type', 'ayah')->first();
        $this->assertNotNull($ayah);
        $this->assertEquals('Ayah Budi', $ayah->name);
        $this->assertEquals('3201011111110001', $ayah->nik);
    }
}
