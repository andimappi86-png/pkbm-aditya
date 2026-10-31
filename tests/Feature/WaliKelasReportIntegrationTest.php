<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\PenugasanMengajar;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WaliKelasReportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $waliKelas;

    protected User $otherWaliKelas;

    protected Classroom $classroom;

    protected Classroom $otherClassroom;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Program $program;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['operator', 'wali-kelas', 'tutor', 'peserta-didik'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        foreach (['dashboard.view', 'reports.view', 'grades.view', 'materials.view', 'announcements.view'] as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        Role::findByName('wali-kelas')->givePermissionTo(['dashboard.view', 'reports.view', 'grades.view', 'materials.view', 'announcements.view']);

        $this->academicYear = AcademicYear::factory()->create(['status_aktif' => true]);
        $this->semester = Semester::factory()->create(['status_aktif' => true, 'tahun_ajaran_id' => $this->academicYear->id]);
        $this->program = Program::factory()->create();

        $this->waliKelas = User::factory()->create(['name' => 'Wali Kelas Utama']);
        $this->waliKelas->assignRole('wali-kelas');

        $this->classroom = Classroom::factory()->create([
            'nama_rombel' => 'Kelas X Paket C',
            'wali_kelas_id' => $this->waliKelas->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'program_id' => $this->program->id,
            'status_aktif' => true,
        ]);

        $this->otherWaliKelas = User::factory()->create(['name' => 'Wali Kelas Lain']);
        $this->otherWaliKelas->assignRole('wali-kelas');

        $this->otherClassroom = Classroom::factory()->create([
            'nama_rombel' => 'Kelas XI Paket C',
            'wali_kelas_id' => $this->otherWaliKelas->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'program_id' => $this->program->id,
            'status_aktif' => true,
        ]);
    }

    public function test_wali_kelas_dashboard_reflects_updated_student_count_when_student_added(): void
    {
        // Awal: 0 siswa
        $response = $this->actingAs($this->waliKelas)->get(route('dashboard.walikelas'));
        $response->assertOk();
        $response->assertSee('0 Siswa Terdaftar');
        $response->assertSee('Detail Anggota (0)');

        // Tambah siswa ke rombel
        $student = Student::factory()->create([
            'nama_lengkap' => 'Budi Santoso',
            'rombel_id' => $this->classroom->id,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        // Dashboard Wali Kelas harus langsung mencerminkan data terbaru dari DB
        $responseAfterAdd = $this->actingAs($this->waliKelas)->get(route('dashboard.walikelas'));
        $responseAfterAdd->assertOk();
        $responseAfterAdd->assertSee('1 Siswa Terdaftar');
        $responseAfterAdd->assertSee('Detail Anggota (1)');
        $responseAfterAdd->assertSee(route('rombel.show', $this->classroom));
        $responseAfterAdd->assertSee(route('rekap.index', ['classroom_id' => $this->classroom->id]));
    }

    public function test_wali_kelas_dashboard_reflects_decreased_student_count_when_student_removed(): void
    {
        $student = Student::factory()->create([
            'nama_lengkap' => 'Siti Nurhaliza',
            'rombel_id' => $this->classroom->id,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        $response = $this->actingAs($this->waliKelas)->get(route('dashboard.walikelas'));
        $response->assertSee('1 Siswa Terdaftar');

        // Siswa dikeluarkan dari rombel
        $student->update([
            'rombel_id' => null,
        ]);

        $responseAfterRemove = $this->actingAs($this->waliKelas)->get(route('dashboard.walikelas'));
        $responseAfterRemove->assertOk();
        $responseAfterRemove->assertSee('0 Siswa Terdaftar');
        $responseAfterRemove->assertSee('Detail Anggota (0)');
    }

    public function test_wali_kelas_dashboard_calculates_actual_attendance_without_dummy_data(): void
    {
        $student = Student::factory()->create([
            'nama_lengkap' => 'Ahmad Dahlan',
            'rombel_id' => $this->classroom->id,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        $subject = Subject::factory()->create();
        $tutor = Tutor::factory()->create();
        $penugasan = PenugasanMengajar::factory()->create([
            'classroom_id' => $this->classroom->id,
            'tutor_id' => $tutor->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
        $schedule = Schedule::factory()->create([
            'penugasan_mengajar_id' => $penugasan->id,
        ]);

        // Awal: belum ada presensi
        $response = $this->actingAs($this->waliKelas)->get(route('dashboard.walikelas'));
        $response->assertOk();
        $response->assertSee('0%');
        $response->assertSee('Belum ada pencatatan presensi pada kelas binaan Anda');

        // Catat presensi aktual: 2 Hadir, 1 Izin, 1 Alpa (Total 4, Hadir+Izin = 3/4 = 75%)
        Attendance::create(['student_id' => $student->id, 'schedule_id' => $schedule->id, 'tanggal' => now()->subDays(3)->toDateString(), 'status' => 'hadir']);
        Attendance::create(['student_id' => $student->id, 'schedule_id' => $schedule->id, 'tanggal' => now()->subDays(2)->toDateString(), 'status' => 'hadir']);
        Attendance::create(['student_id' => $student->id, 'schedule_id' => $schedule->id, 'tanggal' => now()->subDays(1)->toDateString(), 'status' => 'izin']);
        Attendance::create(['student_id' => $student->id, 'schedule_id' => $schedule->id, 'tanggal' => now()->toDateString(), 'status' => 'alpa']);

        $responseWithAttendance = $this->actingAs($this->waliKelas)->get(route('dashboard.walikelas'));
        $responseWithAttendance->assertOk();
        $responseWithAttendance->assertSee('75%');
        $responseWithAttendance->assertDontSee('Belum ada pencatatan presensi pada kelas binaan Anda');
    }

    public function test_rekap_akademik_report_shows_newly_assigned_student_and_links_to_rombel_show(): void
    {
        $student = Student::factory()->create([
            'nama_lengkap' => 'Rina Gunawan',
            'nis' => '12345678',
            'rombel_id' => $this->classroom->id,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        $response = $this->actingAs($this->waliKelas)->get(route('rekap.index'));
        $response->assertOk();
        $response->assertSee('Rina Gunawan');
        $response->assertSee('12345678');
        $response->assertSee($this->classroom->nama_rombel);
        $response->assertSee(route('rombel.show', $this->classroom));

        // Filter per rombel
        $responseFilter = $this->actingAs($this->waliKelas)->get(route('rekap.index', ['classroom_id' => $this->classroom->id]));
        $responseFilter->assertOk();
        $responseFilter->assertSee('Laporan Kelas: '.$this->classroom->nama_rombel);
        $responseFilter->assertSee('Detail Anggota Kelas (1)');
    }

    public function test_rekap_akademik_report_excludes_unassigned_or_removed_student(): void
    {
        $student = Student::factory()->create([
            'nama_lengkap' => 'Doni Setiawan',
            'rombel_id' => $this->classroom->id,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        $response = $this->actingAs($this->waliKelas)->get(route('rekap.index'));
        $response->assertSee('Doni Setiawan');

        // Keluarkan siswa dari rombel
        $student->update(['rombel_id' => null]);

        $responseAfter = $this->actingAs($this->waliKelas)->get(route('rekap.index'));
        $responseAfter->assertOk();
        $responseAfter->assertDontSee('Doni Setiawan');
    }

    public function test_wali_kelas_cannot_see_other_classroom_in_filter_dropdown_and_cannot_access_other_students(): void
    {
        $otherStudent = Student::factory()->create([
            'nama_lengkap' => 'Siswa Kelas Lain',
            'rombel_id' => $this->otherClassroom->id,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        $response = $this->actingAs($this->waliKelas)->get(route('rekap.index'));
        $response->assertOk();
        // Dropdown hanya menampilkan kelas binaan
        $response->assertSee($this->classroom->nama_rombel);
        $response->assertDontSee('option value="'.$this->otherClassroom->id.'"', false);
        $response->assertDontSee('Siswa Kelas Lain');

        // Akses langsung detail rapor siswa kelas lain harus 403 Forbidden
        $detailResponse = $this->actingAs($this->waliKelas)->get(route('rekap.show', $otherStudent));
        $detailResponse->assertForbidden();
    }

    public function test_rapor_report_shows_student_membership_and_links_to_rombel_show(): void
    {
        $student = Student::factory()->create([
            'nama_lengkap' => 'Eko Prasetyo',
            'nis' => '87654321',
            'rombel_id' => $this->classroom->id,
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
        ]);

        $response = $this->actingAs($this->waliKelas)->get(route('rapor.index'));
        $response->assertOk();
        $response->assertSee('Eko Prasetyo');
        $response->assertSee($this->classroom->nama_rombel);
        $response->assertSee(route('rombel.show', $this->classroom));

        // Buka detail rapor siswa
        $showResponse = $this->actingAs($this->waliKelas)->get(route('rapor.show', $student));
        $showResponse->assertOk();
        $showResponse->assertSee('Eko Prasetyo');
        $showResponse->assertSee($this->classroom->nama_rombel);
        $showResponse->assertSee(route('rombel.show', $this->classroom));
    }
}
