<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAccountLinkTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    private User $kepala;

    private User $tutorUser;

    private Tutor $tutor;

    private User $waliUser;

    private User $ketua;

    private User $siswaUser;

    private Student $studentA;

    private Student $studentB;

    private Classroom $classroomA;

    private Classroom $classroomB;

    private Program $program;

    private AcademicYear $academicYear;

    private PenugasanMengajar $penugasanA;

    private PenugasanMengajar $penugasanB;

    private Assignment $assignmentA;

    private Assignment $assignmentB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        // Roles
        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->kepala = User::factory()->create();
        $this->kepala->assignRole('kepala-pkbm');

        $this->tutorUser = User::factory()->create();
        $this->tutorUser->assignRole('tutor');
        $this->tutor = Tutor::factory()->create(['user_id' => $this->tutorUser->id]);

        $this->waliUser = User::factory()->create();
        $this->waliUser->assignRole('wali-kelas');

        $this->ketua = User::factory()->create();
        $this->ketua->assignRole('ketua-yayasan');

        $this->siswaUser = User::factory()->create();
        $this->siswaUser->assignRole('peserta-didik');

        // Master data
        $this->program = Program::factory()->create(['status_aktif' => true]);
        $this->academicYear = AcademicYear::factory()->create(['status_aktif' => true]);

        $this->classroomA = Classroom::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'status_aktif' => true,
        ]);
        $this->classroomB = Classroom::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'status_aktif' => true,
        ]);

        // Penugasan & assignments
        $subject = Subject::factory()->create();

        $this->penugasanA = PenugasanMengajar::factory()->create([
            'tutor_id' => $this->tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $this->classroomA->id,
            'status_aktif' => true,
        ]);
        $this->penugasanB = PenugasanMengajar::factory()->create([
            'tutor_id' => $this->tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $this->classroomB->id,
            'status_aktif' => true,
        ]);

        $this->assignmentA = Assignment::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanA->id,
            'is_active' => true,
            'judul' => 'Tugas Kelas A',
        ]);
        $this->assignmentB = Assignment::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanB->id,
            'is_active' => true,
            'judul' => 'Tugas Kelas B',
        ]);

        // Students (unlinked initially)
        $this->studentA = Student::factory()->create([
            'rombel_id' => $this->classroomA->id,
            'status' => 'aktif',
        ]);
        $this->studentB = Student::factory()->create([
            'rombel_id' => $this->classroomB->id,
            'status' => 'aktif',
        ]);
    }

    public function test_operator_can_view_account_link_page(): void
    {
        $response = $this->actingAs($this->operator)->get(route('peserta-didik.link-accounts'));

        $response->assertOk()
            ->assertViewIs('students.account_links')
            ->assertSee($this->studentA->nama_lengkap)
            ->assertSee($this->siswaUser->name);
    }

    public function test_kepala_pkbm_can_view_account_link_page(): void
    {
        $response = $this->actingAs($this->kepala)->get(route('peserta-didik.link-accounts'));

        $response->assertOk();
    }

    public function test_tutor_cannot_access_account_link_page(): void
    {
        $response = $this->actingAs($this->tutorUser)->get(route('peserta-didik.link-accounts'));

        $response->assertRedirect(route('dashboard.tutor'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');
    }

    public function test_wali_kelas_cannot_access_account_link_page(): void
    {
        $response = $this->actingAs($this->waliUser)->get(route('peserta-didik.link-accounts'));

        $response->assertRedirect(route('dashboard.walikelas'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');
    }

    public function test_ketua_yayasan_cannot_access_account_link_page(): void
    {
        $response = $this->actingAs($this->ketua)->get(route('peserta-didik.link-accounts'));

        $response->assertRedirect(route('dashboard.yayasan'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');
    }

    public function test_siswa_cannot_access_account_link_page(): void
    {
        $response = $this->actingAs($this->siswaUser)->get(route('peserta-didik.link-accounts'));

        $response->assertRedirect(route('dashboard.siswa'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('peserta-didik.link-accounts'));

        $response->assertRedirect(route('login'));
    }

    public function test_operator_can_link_student_to_account(): void
    {
        $this->assertNull($this->studentA->user_id);

        $response = $this->actingAs($this->operator)->post(route('peserta-didik.link-accounts.store'), [
            'student_id' => $this->studentA->id,
            'user_id' => $this->siswaUser->id,
        ]);

        $response->assertRedirect(route('peserta-didik.link-accounts'))
            ->assertSessionHas('success', 'Akun '.$this->siswaUser->name.' berhasil ditautkan ke peserta didik '.$this->studentA->nama_lengkap.'.');

        $this->assertDatabaseHas('students', [
            'id' => $this->studentA->id,
            'user_id' => $this->siswaUser->id,
        ]);
    }

    public function test_store_validates_missing_fields(): void
    {
        $response = $this->actingAs($this->operator)->post(route('peserta-didik.link-accounts.store'), []);

        $response->assertRedirect()
            ->assertSessionHasErrors(['student_id', 'user_id']);
    }

    public function test_store_rejects_account_without_peserta_didik_role(): void
    {
        // tutorUser has role 'tutor' (not 'peserta-didik')
        $response = $this->actingAs($this->operator)->post(route('peserta-didik.link-accounts.store'), [
            'student_id' => $this->studentA->id,
            'user_id' => $this->tutorUser->id,
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['user_id' => 'Akun '.$this->tutorUser->name.' tidak memiliki role Peserta Didik.']);

        $this->assertDatabaseMissing('students', ['id' => $this->studentA->id, 'user_id' => $this->tutorUser->id]);
    }

    public function test_store_rejects_student_already_linked(): void
    {
        // Link studentA first
        $this->studentA->forceFill(['user_id' => $this->siswaUser->id])->save();

        $anotherUser = User::factory()->create();
        $anotherUser->assignRole('peserta-didik');

        $response = $this->actingAs($this->operator)->post(route('peserta-didik.link-accounts.store'), [
            'student_id' => $this->studentA->id,
            'user_id' => $anotherUser->id,
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['student_id' => 'Peserta didik '.$this->studentA->nama_lengkap.' sudah terhubung dengan akun lain.']);
    }

    public function test_store_rejects_account_already_linked(): void
    {
        $student2 = Student::factory()->create([
            'rombel_id' => $this->classroomA->id,
            'status' => 'aktif',
        ]);
        $student2->forceFill(['user_id' => $this->siswaUser->id])->save();

        $student3 = Student::factory()->create([
            'rombel_id' => $this->classroomB->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->operator)->post(route('peserta-didik.link-accounts.store'), [
            'student_id' => $student3->id,
            'user_id' => $this->siswaUser->id,
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['user_id' => 'Akun '.$this->siswaUser->name.' sudah terhubung dengan peserta didik lain.']);
    }

    public function test_unlink_clears_user_id(): void
    {
        $this->studentA->forceFill(['user_id' => $this->siswaUser->id])->save();

        $response = $this->actingAs($this->operator)->delete(route('peserta-didik.link-accounts.destroy', $this->studentA->id));

        $response->assertRedirect(route('peserta-didik.link-accounts'))
            ->assertSessionHas('success', 'Tautan akun '.$this->siswaUser->name.' berhasil dilepas dari peserta didik '.$this->studentA->nama_lengkap.'.');

        $this->assertDatabaseMissing('students', ['id' => $this->studentA->id, 'user_id' => $this->siswaUser->id]);
        $this->assertNull($this->studentA->fresh()->user_id);
    }

    public function test_unlink_rejected_when_not_linked(): void
    {
        $response = $this->actingAs($this->operator)->delete(route('peserta-didik.link-accounts.destroy', $this->studentA->id));

        $response->assertRedirect()
            ->assertSessionHas('error', 'Peserta didik '.$this->studentA->nama_lengkap.' belum memiliki akun yang terhubung.');
    }

    public function test_unique_index_prevents_duplicate_user_id(): void
    {
        $this->studentA->forceFill(['user_id' => $this->siswaUser->id])->save();

        $student2 = Student::factory()->create([
            'rombel_id' => $this->classroomA->id,
            'status' => 'aktif',
        ]);

        // Attempt direct model save violating unique index
        $this->expectException(QueryException::class);
        $student2->forceFill(['user_id' => $this->siswaUser->id])->save();
    }

    public function test_search_filters_students_and_accounts(): void
    {
        $studentUnique = Student::factory()->create([
            'nama_lengkap' => 'UniqueNamaSiswa',
            'nis' => 'NISUNIQUE',
            'rombel_id' => $this->classroomA->id,
        ]);
        $userUnique = User::factory()->create();
        $userUnique->assignRole('peserta-didik');

        $response = $this->actingAs($this->operator)->get(route('peserta-didik.link-accounts', ['search' => 'UniqueNamaSiswa']));

        $response->assertOk()
            ->assertSee('UniqueNamaSiswa')
            ->assertSee('NISUNIQUE')
            ->assertDontSee($this->studentA->nama_lengkap);
    }

    public function test_after_linking_student_sees_assignments_of_own_classroom(): void
    {
        // Link studentA to siswaUser
        $this->actingAs($this->operator)->post(route('peserta-didik.link-accounts.store'), [
            'student_id' => $this->studentA->id,
            'user_id' => $this->siswaUser->id,
        ]);

        // Link studentB to another user
        $otherUser = User::factory()->create();
        $otherUser->assignRole('peserta-didik');
        $this->actingAs($this->operator)->post(route('peserta-didik.link-accounts.store'), [
            'student_id' => $this->studentB->id,
            'user_id' => $otherUser->id,
        ]);

        // Student A opens tugas
        $response = $this->actingAs($this->siswaUser)->get(route('tugas.index'));

        $response->assertOk()
            ->assertSee('Tugas Kelas A')
            ->assertDontSee('Tugas Kelas B');
    }

    public function test_tutor_can_create_assignment_and_student_sees_it(): void
    {
        // Link studentA to siswaUser
        $this->actingAs($this->operator)->post(route('peserta-didik.link-accounts.store'), [
            'student_id' => $this->studentA->id,
            'user_id' => $this->siswaUser->id,
        ]);

        // Tutor creates new assignment in classroom A
        $newAssignment = Assignment::factory()->make([
            'penugasan_mengajar_id' => $this->penugasanA->id,
            'is_active' => true,
            'judul' => 'Tugas Baru Tutor',
            'type' => 'tugas',
            'deadline' => now()->addDays(5)->format('Y-m-d\TH:i'),
        ]);

        $response = $this->actingAs($this->tutorUser)->post(route('tugas.store'), $newAssignment->toArray());

        $response->assertRedirect(route('tugas.index'));

        // Student sees the new assignment
        $studentResponse = $this->actingAs($this->siswaUser)->get(route('tugas.index'));

        $studentResponse->assertOk()
            ->assertSee('Tugas Baru Tutor');
    }

    public function test_link_page_shows_correct_statistics(): void
    {
        $response = $this->actingAs($this->operator)->get(route('peserta-didik.link-accounts'));

        $response->assertOk()
            ->assertSee('Siswa Tanpa Akun')
            ->assertSee('Akun Tanpa Profil')
            ->assertSee('Sudah Tertaut')
            ->assertSee('2') // 2 students without account (studentA, studentB)
            ->assertSee('1') // 1 account without student (siswaUser)
            ->assertSee('0'); // 0 linked initially
    }

    public function test_statistics_match_queries(): void
    {
        $response = $this->actingAs($this->operator)->get(route('peserta-didik.link-accounts'));

        $response->assertOk();

        // Get stats from view
        $viewData = $response->viewData('stats');

        // Count from database using same logic as controller
        $studentsWithoutAccount = Student::whereNull('user_id')->count();
        $accountsWithoutStudent = User::role('peserta-didik')->where('status', 'active')->whereDoesntHave('student')->count();
        $studentsLinked = Student::whereNotNull('user_id')->count();

        $this->assertEquals($studentsWithoutAccount, $viewData['students_without_account']);
        $this->assertEquals($accountsWithoutStudent, $viewData['accounts_without_student']);
        $this->assertEquals($studentsLinked, $viewData['students_linked']);
    }

    public function test_linked_students_list_only_includes_students_with_user(): void
    {
        // Link studentA
        $this->studentA->forceFill(['user_id' => $this->siswaUser->id])->save();

        $anotherUser = User::factory()->create();
        $anotherUser->assignRole('peserta-didik');
        $this->studentB->forceFill(['user_id' => $anotherUser->id])->save();

        $response = $this->actingAs($this->operator)->get(route('peserta-didik.link-accounts'));

        $response->assertOk();

        $linkedStudents = $response->viewData('linkedStudents');
        $this->assertCount(2, $linkedStudents);

        foreach ($linkedStudents as $student) {
            $this->assertNotNull($student->user_id);
            $this->assertNotNull($student->user);
        }
    }

    public function test_search_does_not_change_global_statistics(): void
    {
        $uniqueStudent = Student::factory()->create([
            'nama_lengkap' => 'UniqueSearchStudent',
            'nis' => 'NISUNIQUE',
            'rombel_id' => $this->classroomA->id,
        ]);

        $response = $this->actingAs($this->operator)->get(route('peserta-didik.link-accounts', ['search' => 'UniqueSearchStudent']));

        $response->assertOk();

        $viewStats = $response->viewData('stats');

        // Global stats should still reflect all records
        $studentsWithoutAccount = Student::whereNull('user_id')->count();
        $accountsWithoutStudent = User::role('peserta-didik')->where('status', 'active')->whereDoesntHave('student')->count();
        $studentsLinked = Student::whereNotNull('user_id')->count();

        $this->assertEquals($studentsWithoutAccount, $viewStats['students_without_account']);
        $this->assertEquals($accountsWithoutStudent, $viewStats['accounts_without_student']);
        $this->assertEquals($studentsLinked, $viewStats['students_linked']);
    }

    public function test_pagination_does_not_change_global_statistics(): void
    {
        // Create more students to trigger pagination
        for ($i = 0; $i < 30; $i++) {
            Student::factory()->create([
                'rombel_id' => $this->classroomA->id,
                'program_id' => $this->program->id,
                'tahun_ajaran_id' => $this->academicYear->id,
                'status' => 'aktif',
                'nis' => 'NISPAGE'.$i,
                'nisn' => 'NISNPAGE'.$i,
                'email' => "page{$i}@example.com",
            ]);
        }

        $response = $this->actingAs($this->operator)->get(route('peserta-didik.link-accounts', ['students_page' => 2]));

        $response->assertOk();

        $viewStats = $response->viewData('stats');

        $studentsWithoutAccount = Student::whereNull('user_id')->count();
        $accountsWithoutStudent = User::role('peserta-didik')->where('status', 'active')->whereDoesntHave('student')->count();
        $studentsLinked = Student::whereNotNull('user_id')->count();

        $this->assertEquals($studentsWithoutAccount, $viewStats['students_without_account']);
        $this->assertEquals($accountsWithoutStudent, $viewStats['accounts_without_student']);
        $this->assertEquals($studentsLinked, $viewStats['students_linked']);
    }

    public function test_inactive_account_excluded_from_linkable_candidates_and_stats(): void
    {
        $inactiveUser = User::factory()->create(['status' => 'inactive']);
        $inactiveUser->assignRole('peserta-didik');

        $response = $this->actingAs($this->operator)->get(route('peserta-didik.link-accounts'));

        $response->assertOk();

        // Inactive user should not appear in candidates dropdown or account list
        $linkable = $response->viewData('linkableAccounts');
        $this->assertFalse($linkable->contains('id', $inactiveUser->id));

        $accounts = $response->viewData('accounts');
        $this->assertFalse($accounts->contains('id', $inactiveUser->id));

        // Stats must only count active accounts without student
        $viewStats = $response->viewData('stats');
        $this->assertEquals(1, $viewStats['accounts_without_student']); // only siswaUser
    }

    public function test_store_rejects_inactive_account_via_server_validation(): void
    {
        $inactiveUser = User::factory()->create(['status' => 'inactive']);
        $inactiveUser->assignRole('peserta-didik');

        $response = $this->actingAs($this->operator)->post(route('peserta-didik.link-accounts.store'), [
            'student_id' => $this->studentA->id,
            'user_id' => $inactiveUser->id,
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['user_id']);

        $this->assertDatabaseMissing('students', [
            'id' => $this->studentA->id,
            'user_id' => $inactiveUser->id,
        ]);
        $this->assertNull($this->studentA->fresh()->user_id);
    }

    public function test_deactivating_linked_user_does_not_unlink_student(): void
    {
        // Link studentA to siswaUser
        $this->studentA->forceFill(['user_id' => $this->siswaUser->id])->save();

        // Admin deactivates user
        $this->siswaUser->update(['status' => 'inactive']);

        // Link remains intact
        $this->assertEquals($this->siswaUser->id, $this->studentA->fresh()->user_id);
    }
}
