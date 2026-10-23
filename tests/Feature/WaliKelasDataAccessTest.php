<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Forum;
use App\Models\Grade;
use App\Models\Material;
use App\Models\PenugasanMengajar;
use App\Models\Program;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WaliKelasDataAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $waliKelasOnly;

    protected User $tutorOnly;

    protected User $dualRoleUser;

    protected User $studentUser;

    protected User $operatorUser;

    protected Classroom $classroomA;

    protected Classroom $classroomB;

    protected Tutor $tutorA;

    protected Tutor $tutorB;

    protected PenugasanMengajar $penugasanA;

    protected PenugasanMengajar $penugasanB;

    protected Student $studentA;

    protected Student $studentB;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Subject $subject;

    protected Program $program;

    protected function setUp(): void
    {
        parent::setUp();

        $roles = ['operator', 'kepala-pkbm', 'ketua-yayasan', 'wali-kelas', 'tutor', 'peserta-didik'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $permissions = [
            'grades.view', 'grades.manage', 'reports.view', 'materials.view', 'materials.manage',
            'announcements.view', 'announcements.manage', 'dashboard.view',
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        Role::findByName('operator')->givePermissionTo(Permission::all());
        Role::findByName('kepala-pkbm')->givePermissionTo(['grades.view', 'reports.view', 'materials.view', 'announcements.view', 'dashboard.view']);
        Role::findByName('ketua-yayasan')->givePermissionTo(['grades.view', 'reports.view', 'materials.view', 'announcements.view', 'dashboard.view']);
        Role::findByName('wali-kelas')->givePermissionTo(['grades.view', 'reports.view', 'materials.view', 'announcements.view', 'dashboard.view']);
        Role::findByName('tutor')->givePermissionTo(['grades.view', 'grades.manage', 'materials.view', 'materials.manage', 'announcements.view', 'announcements.manage', 'dashboard.view']);
        Role::findByName('peserta-didik')->givePermissionTo(['grades.view', 'materials.view', 'announcements.view', 'dashboard.view']);

        $this->academicYear = AcademicYear::factory()->create(['status_aktif' => true]);
        $this->semester = Semester::factory()->create(['status_aktif' => true, 'tahun_ajaran_id' => $this->academicYear->id]);
        $this->subject = Subject::factory()->create();
        $this->program = Program::factory()->create();

        // Create Classroom A with wali-kelas only user
        $this->waliKelasOnly = User::factory()->create();
        $this->waliKelasOnly->assignRole('wali-kelas');

        $this->classroomA = Classroom::factory()->create([
            'wali_kelas_id' => $this->waliKelasOnly->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'program_id' => $this->program->id,
            'status_aktif' => true,
        ]);

        // Create Classroom B with dual-role user (tutor + wali-kelas)
        $this->dualRoleUser = User::factory()->create();
        $this->dualRoleUser->assignRole('tutor');
        $this->dualRoleUser->assignRole('wali-kelas');

        $this->tutorA = Tutor::factory()->create(['user_id' => $this->dualRoleUser->id]);

        $this->classroomB = Classroom::factory()->create([
            'wali_kelas_id' => $this->dualRoleUser->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'program_id' => $this->program->id,
            'status_aktif' => true,
        ]);

        // Create tutor-only user for Classroom C
        $this->tutorOnly = User::factory()->create();
        $this->tutorOnly->assignRole('tutor');
        $this->tutorB = Tutor::factory()->create(['user_id' => $this->tutorOnly->id]);

        $classroomC = Classroom::factory()->create([
            'wali_kelas_id' => User::factory()->create()->id, // Different wali-kelas
            'tahun_ajaran_id' => $this->academicYear->id,
            'program_id' => $this->program->id,
            'status_aktif' => true,
        ]);

        // Penugasan for Classroom A (tutorA teaches, waliKelasOnly is wali)
        $this->penugasanA = PenugasanMengajar::factory()->create([
            'tutor_id' => $this->tutorA->id,
            'classroom_id' => $this->classroomA->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'status_aktif' => true,
        ]);

        // Penugasan for Classroom B (tutorB teaches, dualRoleUser is wali)
        $this->penugasanB = PenugasanMengajar::factory()->create([
            'tutor_id' => $this->tutorB->id,
            'classroom_id' => $this->classroomB->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'status_aktif' => true,
        ]);

        // Penugasan for Classroom C (tutorOnly teaches)
        $penugasanC = PenugasanMengajar::factory()->create([
            'tutor_id' => $this->tutorB->id,
            'classroom_id' => $classroomC->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'status_aktif' => true,
        ]);

        // Students in Classroom A
        $this->studentA = Student::factory()->create([
            'rombel_id' => $this->classroomA->id,
            'status' => 'aktif',
        ]);

        // Students in Classroom B
        $this->studentB = Student::factory()->create([
            'rombel_id' => $this->classroomB->id,
            'status' => 'aktif',
        ]);

        // Student in Classroom C
        $studentC = Student::factory()->create([
            'rombel_id' => $classroomC->id,
            'status' => 'aktif',
        ]);

        // Student user
        $this->studentUser = User::factory()->create();
        $this->studentUser->assignRole('peserta-didik');
        $this->studentA->update(['user_id' => $this->studentUser->id]);

        // Operator user
        $this->operatorUser = User::factory()->create();
        $this->operatorUser->assignRole('operator');
    }

    // ==================== GRADES TESTS ====================

    public function test_wali_kelas_only_can_view_grades_in_own_rombel()
    {
        Grade::factory()->create([
            'student_id' => $this->studentA->id,
            'penugasan_mengajar_id' => $this->penugasanA->id,
        ]);
        Grade::factory()->create([
            'student_id' => $this->studentB->id,
            'penugasan_mengajar_id' => $this->penugasanB->id,
        ]);

        $response = $this->actingAs($this->waliKelasOnly)->get(route('nilai.index'));
        $response->assertStatus(200);
        $response->assertSee($this->studentA->nama_lengkap);
        $response->assertDontSee($this->studentB->nama_lengkap);
    }

    public function test_tutor_only_can_view_grades_in_own_penugasan()
    {
        Grade::factory()->create([
            'student_id' => $this->studentA->id,
            'penugasan_mengajar_id' => $this->penugasanA->id,
        ]);
        Grade::factory()->create([
            'student_id' => $this->studentB->id,
            'penugasan_mengajar_id' => $this->penugasanB->id,
        ]);

        $response = $this->actingAs($this->tutorOnly)->get(route('nilai.index'));
        $response->assertStatus(200);
        $response->assertSee($this->studentB->nama_lengkap);
        $response->assertDontSee($this->studentA->nama_lengkap);
    }

    public function test_dual_role_tutor_wali_kelas_can_view_grades_in_both_rombel()
    {
        Grade::factory()->create([
            'student_id' => $this->studentA->id,
            'penugasan_mengajar_id' => $this->penugasanA->id,
        ]);
        Grade::factory()->create([
            'student_id' => $this->studentB->id,
            'penugasan_mengajar_id' => $this->penugasanB->id,
        ]);

        $response = $this->actingAs($this->dualRoleUser)->get(route('nilai.index'));
        $response->assertStatus(200);
        $response->assertSee($this->studentA->nama_lengkap); // From classroom A (tutor teaches)
        $response->assertSee($this->studentB->nama_lengkap); // From classroom B (wali kelas)
    }

    public function test_wali_kelas_cannot_view_grades_in_other_rombel()
    {
        Grade::factory()->create([
            'student_id' => $this->studentB->id,
            'penugasan_mengajar_id' => $this->penugasanB->id,
        ]);

        $response = $this->actingAs($this->waliKelasOnly)->get(route('nilai.index'));
        $response->assertStatus(200);
        $response->assertDontSee($this->studentB->nama_lengkap);
    }

    public function test_student_can_only_view_own_grades()
    {
        Grade::factory()->create([
            'student_id' => $this->studentA->id,
            'penugasan_mengajar_id' => $this->penugasanA->id,
        ]);

        $response = $this->actingAs($this->studentUser)->get(route('nilai.index'));
        $response->assertStatus(200);
        $response->assertSee($this->studentA->nama_lengkap);
    }

    // ==================== RAPOR TESTS ====================

    public function test_wali_kelas_only_can_view_rapor_in_own_rombel()
    {
        $response = $this->actingAs($this->waliKelasOnly)->get(route('rapor.index'));
        $response->assertStatus(200);
        $response->assertSee($this->studentA->nama_lengkap);
        $response->assertDontSee($this->studentB->nama_lengkap);
    }

    public function test_dual_role_tutor_wali_kelas_can_view_rapor_in_both_rombel()
    {
        $response = $this->actingAs($this->dualRoleUser)->get(route('rapor.index'));
        $response->assertStatus(200);
        $response->assertSee($this->studentA->nama_lengkap); // Classroom A (tutor teaches)
        $response->assertSee($this->studentB->nama_lengkap); // Classroom B (wali kelas)
    }

    public function test_wali_kelas_cannot_access_rapor_show_of_other_rombel()
    {
        $response = $this->actingAs($this->waliKelasOnly)->get(route('rapor.show', $this->studentB));
        $response->assertStatus(403);
    }

    public function test_dual_role_can_access_rapor_show_of_both_rombel()
    {
        $response = $this->actingAs($this->dualRoleUser)->get(route('rapor.show', $this->studentA));
        $response->assertStatus(200);

        $response = $this->actingAs($this->dualRoleUser)->get(route('rapor.show', $this->studentB));
        $response->assertStatus(200);
    }

    // ==================== REKAP AKADEMIK TESTS ====================

    public function test_wali_kelas_only_can_view_rekap_in_own_rombel()
    {
        $response = $this->actingAs($this->waliKelasOnly)->get(route('rekap.index'));
        $response->assertStatus(200);
        $response->assertSee($this->studentA->nama_lengkap);
        $response->assertDontSee($this->studentB->nama_lengkap);
    }

    public function test_dual_role_tutor_wali_kelas_can_view_rekap_in_both_rombel()
    {
        $response = $this->actingAs($this->dualRoleUser)->get(route('rekap.index'));
        $response->assertStatus(200);
        $response->assertSee($this->studentA->nama_lengkap); // Classroom A (tutor teaches)
        $response->assertSee($this->studentB->nama_lengkap); // Classroom B (wali kelas)
    }

    public function test_wali_kelas_cannot_access_rekap_show_of_other_rombel()
    {
        $response = $this->actingAs($this->waliKelasOnly)->get(route('rekap.show', $this->studentB));
        $response->assertStatus(403);
    }

    public function test_dual_role_can_access_rekap_show_of_both_rombel()
    {
        $response = $this->actingAs($this->dualRoleUser)->get(route('rekap.show', $this->studentA));
        $response->assertStatus(200);

        $response = $this->actingAs($this->dualRoleUser)->get(route('rekap.show', $this->studentB));
        $response->assertStatus(200);
    }

    // ==================== MATERIALS TESTS ====================

    public function test_wali_kelas_only_can_view_materials_in_own_rombel()
    {
        Material::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanA->id,
            'judul' => 'Materi Kelas A',
            'status_aktif' => true,
        ]);
        Material::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanB->id,
            'judul' => 'Materi Kelas B',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->waliKelasOnly)->get(route('materi.index'));
        $response->assertStatus(200);
        $response->assertSee('Materi Kelas A');
        $response->assertDontSee('Materi Kelas B');
    }

    public function test_tutor_only_can_view_materials_in_own_penugasan()
    {
        Material::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanA->id,
            'judul' => 'Materi Kelas A',
            'status_aktif' => true,
        ]);
        Material::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanB->id,
            'judul' => 'Materi Kelas B',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->tutorOnly)->get(route('materi.index'));
        $response->assertStatus(200);
        $response->assertSee('Materi Kelas B'); // tutorB teaches in classroom B
        $response->assertDontSee('Materi Kelas A');
    }

    public function test_dual_role_tutor_wali_kelas_can_view_materials_in_both()
    {
        Material::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanA->id,
            'judul' => 'Materi Kelas A',
            'status_aktif' => true,
        ]);
        Material::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanB->id,
            'judul' => 'Materi Kelas B',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->dualRoleUser)->get(route('materi.index'));
        $response->assertStatus(200);
        $response->assertSee('Materi Kelas A'); // tutor teaches in classroom A
        $response->assertSee('Materi Kelas B'); // wali kelas of classroom B
    }

    public function test_wali_kelas_cannot_access_material_show_of_other_rombel()
    {
        $material = Material::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanB->id,
            'judul' => 'Materi Kelas B',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->waliKelasOnly)->get(route('materi.show', $material));
        $response->assertStatus(403);
    }

    public function test_dual_role_can_access_material_show_of_both()
    {
        $materialA = Material::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanA->id,
            'judul' => 'Materi Kelas A',
            'status_aktif' => true,
        ]);
        $materialB = Material::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanB->id,
            'judul' => 'Materi Kelas B',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->dualRoleUser)->get(route('materi.show', $materialA));
        $response->assertStatus(200);

        $response = $this->actingAs($this->dualRoleUser)->get(route('materi.show', $materialB));
        $response->assertStatus(200);
    }

    // ==================== FORUM TESTS ====================

    public function test_wali_kelas_only_can_view_forum_in_own_rombel()
    {
        $forumA = Forum::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanA->id,
            'judul' => 'Forum Kelas A',
        ]);
        $forumB = Forum::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanB->id,
            'judul' => 'Forum Kelas B',
        ]);

        $response = $this->actingAs($this->waliKelasOnly)->get(route('forum.index'));
        $response->assertStatus(200);
        $response->assertSee('Forum Kelas A');
        $response->assertDontSee('Forum Kelas B');
    }

    public function test_dual_role_tutor_wali_kelas_can_view_forum_in_both()
    {
        $forumA = Forum::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanA->id,
            'judul' => 'Forum Kelas A',
        ]);
        $forumB = Forum::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanB->id,
            'judul' => 'Forum Kelas B',
        ]);

        $response = $this->actingAs($this->dualRoleUser)->get(route('forum.index'));
        $response->assertStatus(200);
        $response->assertSee('Forum Kelas A'); // tutor teaches
        $response->assertSee('Forum Kelas B'); // wali kelas
    }

    public function test_wali_kelas_cannot_access_forum_show_of_other_rombel()
    {
        $forumB = Forum::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanB->id,
            'judul' => 'Forum Kelas B',
        ]);

        $response = $this->actingAs($this->waliKelasOnly)->get(route('forum.show', $forumB));
        $response->assertStatus(403);
    }

    public function test_dual_role_can_access_forum_show_of_both()
    {
        $forumA = Forum::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanA->id,
            'judul' => 'Forum Kelas A',
        ]);
        $forumB = Forum::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanB->id,
            'judul' => 'Forum Kelas B',
        ]);

        $response = $this->actingAs($this->dualRoleUser)->get(route('forum.show', $forumA));
        $response->assertStatus(200);

        $response = $this->actingAs($this->dualRoleUser)->get(route('forum.show', $forumB));
        $response->assertStatus(200);
    }

    // ==================== OTHER ROLES PRESERVED ====================

    public function test_operator_can_view_all_grades()
    {
        Grade::factory()->create([
            'student_id' => $this->studentA->id,
            'penugasan_mengajar_id' => $this->penugasanA->id,
        ]);
        Grade::factory()->create([
            'student_id' => $this->studentB->id,
            'penugasan_mengajar_id' => $this->penugasanB->id,
        ]);

        $response = $this->actingAs($this->operatorUser)->get(route('nilai.index'));
        $response->assertStatus(200);
        $response->assertSee($this->studentA->nama_lengkap);
        $response->assertSee($this->studentB->nama_lengkap);
    }

    public function test_operator_can_view_all_rapor()
    {
        $response = $this->actingAs($this->operatorUser)->get(route('rapor.index'));
        $response->assertStatus(200);
        $response->assertSee($this->studentA->nama_lengkap);
        $response->assertSee($this->studentB->nama_lengkap);
    }

    public function test_operator_can_view_all_rekap()
    {
        $response = $this->actingAs($this->operatorUser)->get(route('rekap.index'));
        $response->assertStatus(200);
        $response->assertSee($this->studentA->nama_lengkap);
        $response->assertSee($this->studentB->nama_lengkap);
    }

    public function test_operator_can_view_all_materials()
    {
        Material::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanA->id,
            'judul' => 'Materi Kelas A',
            'status_aktif' => true,
        ]);
        Material::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanB->id,
            'judul' => 'Materi Kelas B',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->operatorUser)->get(route('materi.index'));
        $response->assertStatus(200);
        $response->assertSee('Materi Kelas A');
        $response->assertSee('Materi Kelas B');
    }

    public function test_operator_can_view_all_forums()
    {
        $forumA = Forum::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanA->id,
            'judul' => 'Forum Kelas A',
        ]);
        $forumB = Forum::factory()->create([
            'penugasan_mengajar_id' => $this->penugasanB->id,
            'judul' => 'Forum Kelas B',
        ]);

        $response = $this->actingAs($this->operatorUser)->get(route('forum.index'));
        $response->assertStatus(200);
        $response->assertSee('Forum Kelas A');
        $response->assertSee('Forum Kelas B');
    }
}
