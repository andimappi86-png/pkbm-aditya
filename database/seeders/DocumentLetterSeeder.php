<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Letter;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentLetterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();

        if ($students->isEmpty()) {
            return;
        }

        // Create sample documents
        foreach ($students->take(3) as $student) {
            Document::create([
                'student_id' => $student->id,
                'jenis_dokumen' => 'kk',
                'nama_file' => 'kartu_keluarga_'.$student->nis.'.pdf',
                'file_path' => 'documents/sample_kk.pdf',
                'status_verifikasi' => 'diverifikasi',
                'uploaded_at' => now()->subDays(rand(1, 30)),
            ]);

            Document::create([
                'student_id' => $student->id,
                'jenis_dokumen' => 'akta_kelahiran',
                'nama_file' => 'akta_'.$student->nis.'.pdf',
                'file_path' => 'documents/sample_akta.pdf',
                'status_verifikasi' => 'pending',
                'uploaded_at' => now()->subDays(rand(1, 10)),
            ]);
        }

        // Create sample letters
        $adminUser = User::role('kepala-pkbm')->first();
        $penandatangan = $adminUser ? $adminUser->name : 'Kepala PKBM Aditya';
        $penandatanganId = $adminUser ? $adminUser->id : null;

        foreach ($students->take(2) as $index => $student) {
            $nomor = $index + 1;
            $year = date('Y');
            Letter::create([
                'student_id' => $student->id,
                'jenis_surat' => 'aktif_belajar',
                'nomor_surat' => "{$nomor}/PKBM-A/AKT/{$year}",
                'tanggal' => now()->subDays(rand(1, 15)),
                'tujuan' => 'Pengajuan Beasiswa Pendidikan',
                'isi' => "Adalah benar peserta didik pada Pusat Kegiatan Belajar Masyarakat (PKBM) Aditya Kabupaten Mamuju pada Tahun Ajaran {$year}/".($year + 1).".\n\nDemikian surat keterangan ini kami buat dengan sebenarnya agar dapat dipergunakan sebagaimana mestinya.",
                'penandatangan_id' => $penandatanganId,
                'penandatangan_nama' => $penandatangan,
            ]);
        }

        $this->command->info('✅ Document and Letter sample data seeded');
    }
}
