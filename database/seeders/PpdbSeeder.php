<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Ppdb;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PpdbSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = Program::where('status_aktif', true)->get();
        $academicYear = AcademicYear::where('status_aktif', true)->first();

        if ($programs->isEmpty() || ! $academicYear) {
            $this->command->info('⚠️ Program atau Tahun Ajaran aktif tidak ditemukan. Skip PPDB seeding.');

            return;
        }

        // Create a prospective student user
        $calonUser = User::firstOrCreate(
            ['email' => 'calon@example.test'],
            [
                'name' => 'Calon Peserta Test',
                'phone' => '081234567891',
                'status' => 'active',
                'password' => Hash::make('Calon12345!'),
                'email_verified_at' => now(),
            ]
        );

        $calonUser->assignRole('calon-peserta');

        // Create sample PPDB records
        $ppdbs = [
            [
                'user_id' => $calonUser->id,
                'program_id' => $programs->first()->id,
                'tahun_ajaran_id' => $academicYear->id,
                'nik' => '1234567890123456',
                'nisn' => '1234567890',
                'nama_lengkap' => 'Budi Santoso',
                'nama_panggilan' => 'Budi',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Mamuju',
                'tanggal_lahir' => '2010-05-15',
                'agama' => 'Islam',
                'alamat' => 'Jl. Poros Mamuju No. 123',
                'rt' => '003',
                'rw' => '005',
                'kelurahan' => 'Sumare',
                'kecamatan' => 'Mamuju',
                'kabupaten' => 'Mamuju',
                'provinsi' => 'Sulawesi Barat',
                'no_hp' => '081234567890',
                'email' => 'budi.santoso@example.com',
                'nama_ayah' => 'Ahmad Santoso',
                'pekerjaan_ayah' => 'Petani',
                'no_hp_ayah' => '081234567891',
                'nama_ibu' => 'Siti Aminah',
                'pekerjaan_ibu' => 'Ibu Rumah Tangga',
                'no_hp_ibu' => '081234567892',
                'status' => 'pending',
            ],
            [
                'program_id' => $programs->first()->id,
                'tahun_ajaran_id' => $academicYear->id,
                'nik' => '1234567890123457',
                'nisn' => '1234567891',
                'nama_lengkap' => 'Siti Rahayu',
                'nama_panggilan' => 'Siti',
                'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Mamuju',
                'tanggal_lahir' => '2011-08-20',
                'agama' => 'Islam',
                'alamat' => 'Jl. Trans Sulawesi Km 5',
                'rt' => '001',
                'rw' => '002',
                'kelurahan' => 'Tommo',
                'kecamatan' => 'Mamuju',
                'kabupaten' => 'Mamuju',
                'provinsi' => 'Sulawesi Barat',
                'no_hp' => '082234567890',
                'email' => 'siti.rahayu@example.com',
                'nama_ayah' => 'Andi Rahman',
                'pekerjaan_ayah' => 'Pedagang',
                'no_hp_ayah' => '082234567891',
                'nama_ibu' => 'Fatima',
                'pekerjaan_ibu' => 'PNS',
                'no_hp_ibu' => '082234567892',
                'status' => 'diterima',
            ],
            [
                'program_id' => $programs->count() > 1 ? $programs[1]->id : $programs->first()->id,
                'tahun_ajaran_id' => $academicYear->id,
                'nik' => '1234567890123458',
                'nisn' => '1234567892',
                'nama_lengkap' => 'Andi Pratama',
                'nama_panggilan' => 'Andi',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Polewali',
                'tanggal_lahir' => '2009-12-10',
                'agama' => 'Kristen',
                'alamat' => 'Jl. Ahmad Yani No. 45',
                'rt' => '002',
                'rw' => '004',
                'kelurahan' => 'Rangas',
                'kecamatan' => 'Polewali',
                'kabupaten' => 'Polewali Mandar',
                'provinsi' => 'Sulawesi Barat',
                'no_hp' => '083234567890',
                'email' => 'andi.pratama@example.com',
                'nama_ayah' => 'Yusuf Pratama',
                'pekerjaan_ayah' => 'Wiraswasta',
                'no_hp_ayah' => '083234567891',
                'nama_ibu' => 'Maria S',
                'pekerjaan_ibu' => 'Guru',
                'no_hp_ibu' => '083234567892',
                'status' => 'diverifikasi',
            ],
            [
                'program_id' => $programs->count() > 2 ? $programs[2]->id : $programs->first()->id,
                'tahun_ajaran_id' => $academicYear->id,
                'nik' => '1234567890123459',
                'nisn' => '1234567893',
                'nama_lengkap' => 'Dewi Lestari',
                'nama_panggilan' => 'Dewi',
                'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Mamuju',
                'tanggal_lahir' => '2010-03-25',
                'agama' => 'Islam',
                'alamat' => 'Jl. Sudirman No. 78',
                'rt' => '004',
                'rw' => '006',
                'kelurahan' => 'Tobadak',
                'kecamatan' => 'Mamuju',
                'kabupaten' => 'Mamuju',
                'provinsi' => 'Sulawesi Barat',
                'no_hp' => '084234567890',
                'email' => 'dewi.lestari@example.com',
                'nama_ayah' => 'Budi Lestari',
                'pekerjaan_ayah' => 'Buruh',
                'no_hp_ayah' => '084234567891',
                'nama_ibu' => 'Anita',
                'pekerjaan_ibu' => 'Tidak Bekerja',
                'no_hp_ibu' => '084234567892',
                'status' => 'ditolak',
                'catatan_verifikasi' => 'Dokumen tidak lengkap, ijazah tidak valid.',
            ],
        ];

        foreach ($ppdbs as $index => $data) {
            $year = date('Y');
            $count = $index + 1;
            $data['nomor_pendaftaran'] = 'PPDB-'.$year.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);

            if (! isset($data['user_id'])) {
                // For other mock users
                $tempUser = User::firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['nama_lengkap'],
                        'status' => 'active',
                        'password' => Hash::make('Password123!'),
                        'email_verified_at' => now(),
                    ]
                );
                $tempUser->assignRole('calon-peserta');
                $data['user_id'] = $tempUser->id;
            }

            Ppdb::firstOrCreate(
                ['nik' => $data['nik']],
                $data
            );
        }

        $this->command->info('✅ PPDB sample data seeded');
    }
}
