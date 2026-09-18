<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Tutor;
use Illuminate\Database\Seeder;

class PenugasanMengajarSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed if there's no existing data
        if (PenugasanMengajar::count() > 0) {
            return;
        }

        // Check if required tables have data
        $tutorCount = Tutor::count();
        $subjectCount = Subject::count();
        $classroomCount = Classroom::count();
        $academicYearCount = AcademicYear::count();
        $semesterCount = Semester::count();

        // Skip seeding if essential data is not available
        if ($tutorCount === 0 || $subjectCount === 0 || $classroomCount === 0 || $academicYearCount === 0 || $semesterCount === 0) {
            return;
        }

        $tutors = Tutor::take(3)->get();
        $subjects = Subject::take(3)->get();
        $classrooms = Classroom::take(3)->get();
        $academicYears = AcademicYear::take(2)->get();
        $semesters = Semester::take(2)->get();

        foreach ($tutors as $index => $tutor) {
            $subject = $subjects[$index % $subjects->count()];
            $classroom = $classrooms[$index % $classrooms->count()];
            $academicYear = $academicYears[$index % $academicYears->count()];
            $semester = $semesters[$index % $semesters->count()];

            PenugasanMengajar::create([
                'tutor_id' => $tutor->id,
                'subject_id' => $subject->id,
                'classroom_id' => $classroom->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'status_aktif' => true,
            ]);
        }
    }
}
