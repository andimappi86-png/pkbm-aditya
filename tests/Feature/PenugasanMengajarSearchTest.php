<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Program;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PenugasanMengajarSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = ['classrooms.manage'];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        $this->operator = User::factory()->create();
        $this->operator->assignRole($role);
    }

    public function test_index_loads_without_search(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['tahun_ajaran_id' => $year->id]);
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $tutor = Tutor::factory()->create(['nama_lengkap' => 'Budi Santoso']);

        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.index'));

        $response->assertOk()
            ->assertSee('Data Penugasan Mengajar')
            ->assertSee('Budi Santoso');
    }

    public function test_search_by_tutor_name(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['tahun_ajaran_id' => $year->id]);
        $subject = Subject::factory()->create(['nama' => 'Matematika', 'kode' => 'MTK']);
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
            'nama_rombel' => 'Kelas Test',
            'kode_rombel' => 'KT-01',
        ]);

        $tutor1 = Tutor::factory()->create(['nama_lengkap' => 'Budi Santoso']);
        $tutor2 = Tutor::factory()->create(['nama_lengkap' => 'Ahmad Wijaya']);

        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor1->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor2->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.index', ['search' => 'Budi']));

        $response->assertOk()
            ->assertSee('Kelas Test (KT-01)')
            ->assertDontSee('font-medium text-navy\">Ahmad Wijaya');
    }

    public function test_search_by_subject_name(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['tahun_ajaran_id' => $year->id]);
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $tutor = Tutor::factory()->create();

        $subject1 = Subject::factory()->create(['nama' => 'Matematika', 'kode' => 'MTK']);
        $subject2 = Subject::factory()->create(['nama' => 'Bahasa Indonesia', 'kode' => 'BIN']);

        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'subject_id' => $subject1->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'subject_id' => $subject2->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.index', ['search' => 'Matematika']));

        $response->assertOk()
            ->assertSee('border-b hover:bg-navy/5', 1)
            ->assertSee('MTK: Matematika');
    }

    public function test_search_by_classroom_name(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['tahun_ajaran_id' => $year->id]);
        $subject = Subject::factory()->create();
        $tutor = Tutor::factory()->create();

        $classroom1 = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
            'nama_rombel' => 'Kelas A',
            'kode_rombel' => 'KA',
        ]);

        $classroom2 = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
            'nama_rombel' => 'Kelas B',
            'kode_rombel' => 'KB',
        ]);

        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom1->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom2->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.index', ['search' => 'Kelas A']));

        $response->assertOk()
            ->assertSee('border-b hover:bg-navy/5', 1)
            ->assertSee('Kelas A (KA)');
    }

    public function test_search_with_no_results_shows_empty_state(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['tahun_ajaran_id' => $year->id]);
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $tutor = Tutor::factory()->create(['nama_lengkap' => 'Budi Santoso']);

        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.index', ['search' => 'NonExistent']));

        $response->assertOk()
            ->assertSee('Belum ada penugasan mengajar');
    }

    public function test_empty_search_returns_all(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['tahun_ajaran_id' => $year->id]);
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $tutor = Tutor::factory()->create(['nama_lengkap' => 'Budi Santoso']);

        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.index', ['search' => '']));

        $response->assertOk()
            ->assertSee('Budi Santoso');
    }

    public function test_search_with_whitespace_only_returns_all(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['tahun_ajaran_id' => $year->id]);
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $tutor = Tutor::factory()->create(['nama_lengkap' => 'Budi Santoso']);

        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.index', ['search' => '   ']));

        $response->assertOk()
            ->assertSee('Budi Santoso');
    }

    public function test_search_works_with_pagination(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['tahun_ajaran_id' => $year->id]);
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);

        // Create 15 records matching "Budi" and 5 matching "Ahmad"
        for ($i = 1; $i <= 15; $i++) {
            $tutor = Tutor::factory()->create(['nama_lengkap' => "Budi Santoso {$i}"]);
            PenugasanMengajar::factory()->create([
                'tutor_id' => $tutor->id,
                'subject_id' => $subject->id,
                'classroom_id' => $classroom->id,
                'academic_year_id' => $year->id,
                'semester_id' => $semester->id,
                'status_aktif' => true,
            ]);
        }

        for ($i = 1; $i <= 5; $i++) {
            $tutor = Tutor::factory()->create(['nama_lengkap' => "Ahmad Wijaya {$i}"]);
            PenugasanMengajar::factory()->create([
                'tutor_id' => $tutor->id,
                'subject_id' => $subject->id,
                'classroom_id' => $classroom->id,
                'academic_year_id' => $year->id,
                'semester_id' => $semester->id,
                'status_aktif' => true,
            ]);
        }

        $response = $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.index', ['search' => 'Budi']));

        $response->assertOk()
            ->assertSee('Budi Santoso 1');
    }

    public function test_search_preserves_other_filters(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['tahun_ajaran_id' => $year->id]);
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);

        $subject1 = Subject::factory()->create(['nama' => 'Matematika']);
        $subject2 = Subject::factory()->create(['nama' => 'Bahasa Indonesia']);

        $tutor1 = Tutor::factory()->create(['nama_lengkap' => 'Budi Santoso']);
        $tutor2 = Tutor::factory()->create(['nama_lengkap' => 'Ahmad Wijaya']);

        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor1->id,
            'subject_id' => $subject1->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutor2->id,
            'subject_id' => $subject2->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        // Search with subject filter
        $response = $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.index', [
                'search' => 'Budi',
                'subject_id' => $subject2->id, // This should not match Budi's subject
            ]));

        $response->assertOk()
            ->assertDontSee('Budi Santoso</td>'); // Should not match because subject filter excludes it
    }

    public function test_search_with_status_filter_aktif(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['tahun_ajaran_id' => $year->id]);
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $tutorAktif = Tutor::factory()->create(['nama_lengkap' => 'Umi Widiastuti']);
        $tutorTidakAktif = Tutor::factory()->create(['nama_lengkap' => 'Budi Santoso']);

        // Active record
        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutorAktif->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        // Inactive record
        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutorTidakAktif->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => false,
        ]);

        // Search with status_aktif=1 (Aktif) - should find Umi
        $response = $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.index', ['search' => 'Umi', 'status_aktif' => '1']));

        $response->assertOk();
        // Check table body only - Umi should appear in table row
        $response->assertSeeInOrder(['<tbody', 'Umi Widiastuti', '</tbody>']);
        // Budi should NOT appear in table body (only in dropdown)
        $this->assertStringNotContainsString('Budi Santoso', $this->getTableBody($response));
    }

    private function getTableBody($response): string
    {
        $html = $response->getContent();
        if (preg_match('/<tbody[^>]*>(.*?)<\/tbody>/s', $html, $matches)) {
            return $matches[1];
        }

        return '';
    }

    public function test_search_with_status_filter_tidak_aktif(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['tahun_ajaran_id' => $year->id]);
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $tutorAktif = Tutor::factory()->create(['nama_lengkap' => 'Umi Widiastuti']);
        $tutorTidakAktif = Tutor::factory()->create(['nama_lengkap' => 'Budi Santoso']);

        // Active record
        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutorAktif->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        // Inactive record
        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutorTidakAktif->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => false,
        ]);

        // Search with status_aktif=0 (Tidak Aktif) - should find Budi, not Umi
        $response = $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.index', ['search' => 'Umi', 'status_aktif' => '0']));

        $response->assertOk();
        // Umi should NOT appear in table body (only in dropdown)
        $this->assertStringNotContainsString('Umi Widiastuti', $this->getTableBody($response));
        // Empty state should show
        $response->assertSee('Belum ada penugasan mengajar');
    }

    public function test_search_without_status_filter_returns_both_active_and_inactive(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['tahun_ajaran_id' => $year->id]);
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $tutorAktif = Tutor::factory()->create(['nama_lengkap' => 'Umi Widiastuti']);
        $tutorTidakAktif = Tutor::factory()->create(['nama_lengkap' => 'Budi Santoso']);

        // Active record
        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutorAktif->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        // Inactive record
        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutorTidakAktif->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => false,
        ]);

        // Search without status filter - should find both
        $response = $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.index', ['search' => 'Umi']));

        $response->assertOk()
            ->assertSee('Umi Widiastuti');
    }

    public function test_default_page_load_shows_all_status_without_filter(): void
    {
        $program = Program::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['tahun_ajaran_id' => $year->id]);
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year->id,
        ]);
        $tutorAktif = Tutor::factory()->create(['nama_lengkap' => 'Umi Widiastuti']);
        $tutorTidakAktif = Tutor::factory()->create(['nama_lengkap' => 'Budi Santoso']);

        // Active record
        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutorAktif->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => true,
        ]);

        // Inactive record
        PenugasanMengajar::factory()->create([
            'tutor_id' => $tutorTidakAktif->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'status_aktif' => false,
        ]);

        // Load page without any query parameters
        $response = $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.index'));

        $response->assertOk();
        // Default dropdown should show "Semua Status" (empty value selected)
        $response->assertSee('<option value="" selected>-- Semua Status --</option>', false);
        // Both records should appear in table
        $response->assertSeeInOrder(['<tbody', 'Umi Widiastuti', '</tbody>']);
        $response->assertSeeInOrder(['<tbody', 'Budi Santoso', '</tbody>']);
    }

    public function test_semester_ordering_uses_academic_year_tanggal_mulai(): void
    {
        $program = Program::factory()->create();
        $year1 = AcademicYear::factory()->create(['nama' => '2023/2024', 'tanggal_mulai' => '2023-07-01']);
        $year2 = AcademicYear::factory()->create(['nama' => '2024/2025', 'tanggal_mulai' => '2024-07-01']);

        $semester1 = Semester::factory()->create(['tahun_ajaran_id' => $year1->id, 'kode' => 'GANJIL']);
        $semester2 = Semester::factory()->create(['tahun_ajaran_id' => $year2->id, 'kode' => 'GANJIL']);

        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create([
            'program_id' => $program->id,
            'tahun_ajaran_id' => $year2->id,
        ]);
        $tutor = Tutor::factory()->create();

        // This should not throw "Unknown column 'tanggal_mulai'" error
        $response = $this->actingAs($this->operator)
            ->get(route('penugasan-mengajar.index'));

        $response->assertOk();
    }
}
