<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        Artisan::call('permission:cache-reset');

        // Define all permissions
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Users Management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.manage',

            // Students
            'students.view',
            'students.create',
            'students.update',
            'students.delete',
            'students.manage',

            // Tutors
            'tutors.view',
            'tutors.create',
            'tutors.update',
            'tutors.delete',
            'tutors.manage',

            // Classrooms
            'classrooms.view',
            'classrooms.create',
            'classrooms.update',
            'classrooms.delete',
            'classrooms.manage',

            // Programs
            'programs.view',
            'programs.manage',

            // Academic Years
            'academic-years.view',
            'academic-years.manage',

            // Semesters
            'semesters.view',
            'semesters.manage',

            // Subjects
            'subjects.view',
            'subjects.create',
            'subjects.update',
            'subjects.delete',
            'subjects.manage',

            // Schedules
            'schedules.view',
            'schedules.manage',

            // Attendance
            'attendance.view',
            'attendance.manage',

            // Grades
            'grades.view',
            'grades.manage',

            // Materials
            'materials.view',
            'materials.manage',

            // Assignments
            'assignments.view',
            'assignments.manage',
            'assignments.submit',

            // Quizzes
            'quizzes.view',
            'quizzes.manage',
            'quizzes.take',

            // Exams
            'exams.view',
            'exams.manage',
            'exams.take',

            // Announcements
            'announcements.view',
            'announcements.manage',

            // Documents
            'documents.view',
            'documents.manage',
            'documents.upload',
            'documents.verify',
            'documents.download',
            'documents.delete',

            // Letters
            'letters.view',
            'letters.manage',
            'letters.create',
            'letters.print',

            // Reports
            'reports.view',
            'reports.manage',

            // PPDB / Admissions
            'admissions.view',
            'admissions.manage',

            // Settings
            'settings.view',
            'settings.manage',

            // Audit
            'audit.view',

            // Operator Specific
            'operator.import',
        ];

        // Create permissions (idempotent)
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info('✅ Permissions created/verified');

        // Create roles and assign permissions
        $this->createOperatorRole();
        $this->createKepalaRole();
        $this->createKetuaYayasanRole();
        $this->createWaliKelasRole();
        $this->createTutorRole();
        $this->createPesertaDidikRole();

        $this->command->info('✅ All roles and permissions configured');
    }

    /**
     * Operator - Full access and Security Testing
     */
    private function createOperatorRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);

        $permissions = [
            'dashboard.view',
            'users.view', 'users.create', 'users.update', 'users.delete', 'users.manage',
            'students.view', 'students.create', 'students.update', 'students.delete', 'students.manage',
            'tutors.view', 'tutors.create', 'tutors.update', 'tutors.delete', 'tutors.manage',
            'classrooms.view', 'classrooms.create', 'classrooms.update', 'classrooms.delete', 'classrooms.manage',
            'programs.view', 'programs.manage',
            'academic-years.view', 'academic-years.manage',
            'semesters.view', 'semesters.manage',
            'subjects.view', 'subjects.create', 'subjects.update', 'subjects.delete', 'subjects.manage',
            'schedules.view', 'schedules.manage',
            'attendance.view', 'attendance.manage',
            'grades.view', 'grades.manage',
            'materials.view', 'materials.manage',
            'assignments.view', 'assignments.manage', 'assignments.submit',
            'quizzes.view', 'quizzes.manage', 'quizzes.take',
            'exams.view', 'exams.manage', 'exams.take',
            'announcements.view', 'announcements.manage',
            'documents.view', 'documents.manage', 'documents.upload', 'documents.verify', 'documents.download', 'documents.delete',
            'letters.view', 'letters.manage', 'letters.create', 'letters.print',
            'reports.view', 'reports.manage',
            'admissions.view', 'admissions.manage',
            'settings.view', 'settings.manage',
            'audit.view',
            'operator.import',
        ];

        $role->syncPermissions($permissions);
        $this->command->info('✅ Operator role configured');
    }

    /**
     * Kepala PKBM - Full operational control
     */
    private function createKepalaRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'kepala-pkbm', 'guard_name' => 'web']);

        $permissions = [
            'dashboard.view',
            'users.view', 'users.create', 'users.update', 'users.delete', 'users.manage',
            'students.view', 'students.create', 'students.update', 'students.delete', 'students.manage',
            'tutors.view', 'tutors.create', 'tutors.update', 'tutors.delete', 'tutors.manage',
            'classrooms.view', 'classrooms.create', 'classrooms.update', 'classrooms.delete', 'classrooms.manage',
            'programs.view', 'programs.manage',
            'academic-years.view', 'academic-years.manage',
            'semesters.view', 'semesters.manage',
            'subjects.view', 'subjects.create', 'subjects.update', 'subjects.delete', 'subjects.manage',
            'subjects.create',
            'subjects.update',
            'subjects.delete',
            'schedules.view', 'schedules.manage',
            'attendance.view', 'attendance.manage',
            'grades.view', 'grades.manage',
            'materials.view', 'materials.manage',
            'assignments.view', 'assignments.manage',
            'quizzes.view', 'quizzes.manage',
            'exams.view', 'exams.manage',
            'announcements.view', 'announcements.manage',
            'documents.view', 'documents.manage', 'documents.upload', 'documents.verify', 'documents.download', 'documents.delete',
            'letters.view', 'letters.manage', 'letters.create', 'letters.print',
            'reports.view', 'reports.manage',
            'admissions.view', 'admissions.manage',
            'settings.view', 'settings.manage',
            'audit.view',
        ];

        $role->syncPermissions($permissions);
        $this->command->info('✅ Kepala PKBM role configured');
    }

    /**
     * Ketua Yayasan - Monitoring and oversight (read-only focus)
     */
    private function createKetuaYayasanRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'ketua-yayasan', 'guard_name' => 'web']);

        $permissions = [
            'dashboard.view',
            'students.view',
            'tutors.view',
            'classrooms.view',
            'programs.view',
            'academic-years.view',
            'semesters.view',
            'subjects.view',
            'schedules.view',
            'attendance.view',
            'grades.view',
            'materials.view',
            'assignments.view',
            'quizzes.view',
            'exams.view',
            'announcements.view',
            'documents.view',
            'letters.view',
            'reports.view',
            'admissions.view',
            'audit.view',
        ];

        $role->syncPermissions($permissions);
        $this->command->info('✅ Ketua Yayasan role configured');
    }

    /**
     * Wali Kelas - Classroom management (with data scoping)
     */
    private function createWaliKelasRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'wali-kelas', 'guard_name' => 'web']);

        $permissions = [
            'dashboard.view',
            'students.view',
            'classrooms.view',
            'schedules.view',
            'attendance.view',
            'attendance.manage',
            'grades.view',
            'materials.view',
            'assignments.view',
            'quizzes.view',
            'exams.view',
            'announcements.view',
            'announcements.manage',
            'reports.view',
        ];

        $role->syncPermissions($permissions);
        $this->command->info('✅ Wali Kelas role configured');
    }

    /**
     * Tutor - Teaching and assessment (with subject/class scoping)
     */
    private function createTutorRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'tutor', 'guard_name' => 'web']);

        $permissions = [
            'dashboard.view',
            'students.view',
            'classrooms.view',
            'schedules.view',
            'attendance.view',
            'attendance.manage',
            'grades.view',
            'grades.manage',
            'materials.view',
            'materials.manage',
            'assignments.view',
            'assignments.manage',
            'quizzes.view',
            'quizzes.manage',
            'exams.view',
            'exams.manage',
            'announcements.view',
        ];

        $role->syncPermissions($permissions);
    }

    /**
     * Peserta Didik - Student view (own data only)
     */
    private function createPesertaDidikRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'peserta-didik', 'guard_name' => 'web']);

        $permissions = [
            'dashboard.view',
            'schedules.view',
            'attendance.view',
            'grades.view',
            'materials.view',
            'assignments.view',
            'assignments.submit',
            'quizzes.view',
            'quizzes.take',
            'exams.view',
            'exams.take',
            'announcements.view',
            'documents.view',
            'documents.upload',
            'documents.download',
            'letters.view',
        ];

        $role->syncPermissions($permissions);
        $this->command->info('✅ Peserta Didik role configured');
    }
}
