<?php

namespace Database\Seeders;

use App\Models\Forum;
use App\Models\ForumComment;
use App\Models\PenugasanMengajar;
use App\Models\User;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        $penugasans = PenugasanMengajar::where('status_aktif', true)->get();
        $admin = User::first();

        foreach ($penugasans as $penugasan) {
            $forum = Forum::create([
                'penugasan_mengajar_id' => $penugasan->id,
                'user_id' => $penugasan->tutor->user_id ?? $admin->id,
                'judul' => 'Diskusi Pembelajaran: '.$penugasan->subject->nama,
                'isi' => 'Selamat datang di forum diskusi untuk mata pelajaran '.$penugasan->subject->nama.'. Silakan ajukan pertanyaan bila ada hal yang belum dipahami terkait materi.',
                'status_aktif' => true,
            ]);

            ForumComment::create([
                'forum_id' => $forum->id,
                'user_id' => $admin->id,
                'isi' => 'Semangat belajar semuanya!',
            ]);
        }
    }
}
