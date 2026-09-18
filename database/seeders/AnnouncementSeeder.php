<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('kepala-pkbm')->first();

        if (! $admin) {
            $this->command->info('Admin user not found. Skipping AnnouncementSeeder.');

            return;
        }

        // Global Announcement
        Announcement::create([
            'user_id' => $admin->id,
            'judul' => 'Pengumuman Libur Nasional',
            'isi' => 'Diberitahukan kepada seluruh elemen PKBM Aditya bahwa kegiatan belajar mengajar diliburkan pada tanggal merah.',
            'kategori' => 'Umum',
            'status_aktif' => true,
            'published_at' => now()->subDays(2),
        ]);

        // Role Targeted Announcement
        Announcement::create([
            'user_id' => $admin->id,
            'judul' => 'Rapat Evaluasi Tutor',
            'isi' => 'Rapat evaluasi akhir semester untuk seluruh tutor akan dilaksanakan minggu depan.',
            'kategori' => 'Akademik',
            'target_role' => 'tutor',
            'status_aktif' => true,
            'published_at' => now()->subDay(),
        ]);

        // Draft
        Announcement::create([
            'user_id' => $admin->id,
            'judul' => 'Draft Persiapan Ujian',
            'isi' => 'Masih dalam penyusunan jadwal ujian semester.',
            'kategori' => 'Akademik',
            'status_aktif' => false,
            'published_at' => null,
        ]);

        $this->command->info('✅ Announcements seeded');
    }
}
