<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\PenugasanMengajar;
use App\Models\Student;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $penugasan = PenugasanMengajar::first();
        if (! $penugasan) {
            return;
        }

        $students = Student::where('rombel_id', $penugasan->classroom_id)->get();

        foreach ($students as $student) {
            Grade::updateOrCreate(
                [
                    'penugasan_mengajar_id' => $penugasan->id,
                    'student_id' => $student->id,
                ],
                [
                    'nilai_tugas' => 85,
                    'nilai_uts' => 80,
                    'nilai_uas' => 90,
                    'nilai_akhir' => 85.5,
                    'catatan' => 'Sangat baik dalam memahami materi.',
                ]
            );
        }
    }
}
