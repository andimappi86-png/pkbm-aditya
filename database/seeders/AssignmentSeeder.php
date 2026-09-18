<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\PenugasanMengajar;
use App\Models\Student;
use App\Models\Submission;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $penugasan = PenugasanMengajar::first();
        if (! $penugasan) {
            return;
        }

        $assignment = Assignment::create([
            'penugasan_mengajar_id' => $penugasan->id,
            'judul' => 'Tugas 1: Analisis Bacaan Teks',
            'deskripsi' => 'Kerjakan analisis isi bacaan pada modul bab 1.',
            'instruksi' => 'Jawablah pertanyaan secara ringkas dan padat. Boleh mengunggah file dokumen.',
            'deadline' => now()->addDays(7),
            'is_active' => true,
            'type' => 'tugas',
        ]);

        $quiz = Assignment::create([
            'penugasan_mengajar_id' => $penugasan->id,
            'judul' => 'Quiz 1: Uji Pemahaman Dasar',
            'deskripsi' => 'Quiz pengantar pemahaman materi.',
            'instruksi' => 'Kerjakan quiz dalam waktu 30 menit.',
            'deadline' => now()->addDays(3),
            'is_active' => true,
            'type' => 'quiz',
        ]);

        $student = Student::where('rombel_id', $penugasan->classroom_id)->first();
        if ($student) {
            Submission::create([
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
                'jawaban' => 'Berikut jawaban analisis bacaan saya.',
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);
        }
    }
}
