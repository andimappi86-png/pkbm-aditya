<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ClassroomMemberManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;

    protected User $waliKelasA;

    protected User $waliKelasB;

    protected Program $program;

    protected AcademicYear $academicYear;

    protected Classroom $rombelA;

    protected Classroom $rombelB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);

        $this->program = Program::factory()->create(['status_aktif' => true, 'nama' => 'Paket C']);
        $this->academicYear = AcademicYear::factory()->create(['status_aktif' => true, 'nama' => '2026/2027']);

        $this->operator = User::factory()->create(['name' => 'Operator']);
        $this->operator->assignRole('operator');

        $this->waliKelasA = User::factory()->create(['name' => 'Wali Kelas A']);
        $this->waliKelasA->assignRole('wali-kelas');

        $this->waliKelasB = User::factory()->create(['name' => 'Wali Kelas B']);
        $this->waliKelasB->assignRole('wali-kelas');

        $this->rombelA = Classroom::factory()->create([
            'nama_rombel' => 'Kelas X Paket C - Rombel A',
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'wali_kelas_id' => $this->waliKelasA->id,
            'status_aktif' => true,
        ]);

        $this->rombelB = Classroom::factory()->create([
            'nama_rombel' => 'Kelas X Paket C - Rombel B',
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'wali_kelas_id' => $this->waliKelasB->id,
            'status_aktif' => true,
        ]);
    }

    /** 1. Input cepat berhasil menambahkan siswa ke kelas sendiri dan tidak membuat absensi otomatis */
    public function test_wali_kelas_can_quick_store_student_to_own_classroom(): void
    {
        $payload = [
            'nama_lengkap' => 'Muhammad Rizky',
            'nomor_identitas' => '20261001',
            'jenis_kelamin' => 'Laki-laki',
            'status_masuk' => 'Siswa Baru',
            'kontak' => '081234567890',
        ];

        $response = $this->actingAs($this->waliKelasA)
            ->post(route('rombel.students.quick-store', $this->rombelA), $payload);

        $response->assertRedirect(route('rombel.show', $this->rombelA));
        $response->assertSessionHas('success');

        $student = Student::where('nis', '20261001')->first();
        $this->assertNotNull($student);
        $this->assertEquals('Muhammad Rizky', $student->nama_lengkap);
        $this->assertEquals($this->rombelA->id, $student->rombel_id);
        $this->assertEquals('aktif', $student->status);
        $this->assertEquals('081234567890', $student->no_hp);

        // Pastikan tidak ada rekaman kehadiran dummy yang otomatis tercatat sebagai hadir
        $this->assertEquals(0, Attendance::where('student_id', $student->id)->count());
    }

    /** 2. Pemilihan siswa terdaftar tanpa kelas berhasil ditempatkan */
    public function test_wali_kelas_can_assign_unassigned_student_to_own_classroom(): void
    {
        $unassignedStudent = Student::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => null,
            'status' => 'aktif',
            'nama_lengkap' => 'Siti Nurhaliza',
        ]);

        $response = $this->actingAs($this->waliKelasA)
            ->post(route('rombel.students.assign', $this->rombelA), [
                'student_ids' => [$unassignedStudent->id],
            ]);

        $response->assertRedirect(route('rombel.show', $this->rombelA));
        $response->assertSessionHas('success');

        $this->assertEquals($this->rombelA->id, $unassignedStudent->fresh()->rombel_id);
        $this->assertEquals(0, Attendance::where('student_id', $unassignedStudent->id)->count());
    }

    /** 3. Preview tambah massal memvalidasi baris dan menandai error dengan tepat */
    public function test_bulk_preview_validates_rows_and_flags_errors_correctly(): void
    {
        // Siswa yang sudah ada di database untuk tes duplikasi
        Student::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'nis' => 'NIS-EXIST-01',
            'status' => 'aktif',
        ]);

        $rawData = implode("\n", [
            'Budi Santoso, 20261002, L, 0811111111, Baru', // Valid
            'Dewi Sartika, NIS-EXIST-01, P, 0822222222, Pindahan', // Error: NIS sudah ada di DB
            'Joko Anwar, 20261003, X, 0833333333, Baru', // Error: Jenis kelamin tidak valid
            'Andi Pratama, 20261004, L, 0844444444, Baru', // Valid
            'Andi Clone, 20261004, L, 0855555555, Baru', // Error: Duplikat internal 20261004
        ]);

        $response = $this->actingAs($this->waliKelasA)
            ->postJson(route('rombel.students.bulk-preview', $this->rombelA), [
                'raw_data' => $rawData,
            ]);

        $response->assertOk();
        $response->assertJson([
            'total_rows' => 5,
            'valid_count' => 2,
            'invalid_count' => 3,
        ]);

        $rows = $response->json('rows');
        $this->assertTrue($rows[0]['is_valid']);
        $this->assertFalse($rows[1]['is_valid']);
        $this->assertStringContainsString('sudah terdaftar', $rows[1]['error']);
        $this->assertFalse($rows[2]['is_valid']);
        $this->assertStringContainsString('Jenis kelamin tidak valid', $rows[2]['error']);
        $this->assertTrue($rows[3]['is_valid']);
        $this->assertFalse($rows[4]['is_valid']);
        $this->assertStringContainsString('Duplikat dalam daftar', $rows[4]['error']);
    }

    /** 4. Simpan massal hanya menyimpan baris yang valid dan tidak menyimpan baris invalid secara diam-diam */
    public function test_bulk_store_only_saves_valid_rows(): void
    {
        Student::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'nis' => 'NIS-DUPLIKAT',
            'status' => 'aktif',
        ]);

        $rawData = implode("\n", [
            'Siswa Valid Satu, NIS-VALID-01, L, 0811111111, Baru',
            'Siswa Duplikat, NIS-DUPLIKAT, P, 0822222222, Baru',
            'Siswa Valid Dua, NIS-VALID-02, P, 0833333333, Pindahan',
        ]);

        $response = $this->actingAs($this->waliKelasA)
            ->post(route('rombel.students.bulk-store', $this->rombelA), [
                'raw_data' => $rawData,
            ]);

        $response->assertRedirect(route('rombel.show', $this->rombelA));
        $response->assertSessionHas('success');

        // Pastikan hanya 2 siswa valid yang tersimpan
        $s1 = Student::where('nis', 'NIS-VALID-01')->first();
        $s2 = Student::where('nis', 'NIS-VALID-02')->first();
        $this->assertNotNull($s1);
        $this->assertNotNull($s2);
        $this->assertEquals($this->rombelA->id, $s1->rombel_id);
        $this->assertEquals($this->rombelA->id, $s2->rombel_id);

        // Pastikan siswa duplikat tidak bertambah
        $this->assertEquals(1, Student::where('nis', 'NIS-DUPLIKAT')->count());
        $this->assertDatabaseMissing('students', ['nama_lengkap' => 'Siswa Duplikat']);
    }

    /** 5. Validasi duplikasi identitas pada input cepat ditolak */
    public function test_quick_store_rejects_duplicate_identity(): void
    {
        Student::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'nis' => 'IDENTIK-001',
            'nik' => '7601011501900001',
            'status' => 'aktif',
        ]);

        // Coba input cepat dengan NIS yang sama
        $responseNis = $this->actingAs($this->waliKelasA)
            ->post(route('rombel.students.quick-store', $this->rombelA), [
                'nama_lengkap' => 'Siswa Kembar NIS',
                'nomor_identitas' => 'IDENTIK-001',
                'jenis_kelamin' => 'Laki-laki',
            ]);

        $responseNis->assertSessionHasErrors('nomor_identitas');

        // Coba input cepat dengan NIK 16 digit yang sama
        $responseNik = $this->actingAs($this->waliKelasA)
            ->post(route('rombel.students.quick-store', $this->rombelA), [
                'nama_lengkap' => 'Siswa Kembar NIK',
                'nomor_identitas' => '7601011501900001',
                'jenis_kelamin' => 'Perempuan',
            ]);

        $responseNik->assertSessionHasErrors('nomor_identitas');
    }

    /** 6. Pembatasan akses lintas kelas: Wali kelas A ditolak mengelola rombel B */
    public function test_cross_classroom_isolation_prevents_wali_kelas_from_managing_other_classroom(): void
    {
        // Wali Kelas A mencoba menambahkan siswa ke Rombel B milik Wali Kelas B via Web & JSON
        $responseAdd = $this->actingAs($this->waliKelasA)
            ->post(route('rombel.students.quick-store', $this->rombelB), [
                'nama_lengkap' => 'Siswa Bajakan',
                'nomor_identitas' => 'BAJAK-001',
                'jenis_kelamin' => 'Laki-laki',
            ]);

        // Sesuai bootstrap/app.php: 403 pada web me-redirect ke dashboardRoute() dengan session error
        $this->assertTrue(
            $responseAdd->status() === 403
            || ($responseAdd->isRedirect() && session('error') !== null)
        );

        $responseAddJson = $this->actingAs($this->waliKelasA)
            ->postJson(route('rombel.students.quick-store', $this->rombelB), [
                'nama_lengkap' => 'Siswa Bajakan JSON',
                'nomor_identitas' => 'BAJAK-001-J',
                'jenis_kelamin' => 'Laki-laki',
            ]);
        $responseAddJson->assertForbidden();

        // Wali Kelas A mencoba assign siswa ke Rombel B
        $student = Student::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => null,
            'status' => 'aktif',
        ]);
        $responseAssign = $this->actingAs($this->waliKelasA)
            ->post(route('rombel.students.assign', $this->rombelB), [
                'student_ids' => [$student->id],
            ]);

        $this->assertTrue(
            $responseAssign->status() === 403
            || ($responseAssign->isRedirect() && session('error') !== null)
        );

        // Wali Kelas A mencoba mengeluarkan siswa dari Rombel B
        $studentB = Student::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $this->rombelB->id,
            'status' => 'aktif',
        ]);
        $responseRemove = $this->actingAs($this->waliKelasA)
            ->post(route('rombel.students.remove', [$this->rombelB, $studentB]));

        $this->assertTrue(
            $responseRemove->status() === 403
            || ($responseRemove->isRedirect() && session('error') !== null)
        );

        $responseRemoveJson = $this->actingAs($this->waliKelasA)
            ->postJson(route('rombel.students.remove', [$this->rombelB, $studentB]));
        $responseRemoveJson->assertForbidden();
    }

    /** 7. Wali kelas dapat mengeluarkan siswa dari rombelnya sendiri */
    public function test_wali_kelas_can_remove_student_from_own_classroom(): void
    {
        $student = Student::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $this->rombelA->id,
            'status' => 'aktif',
            'nama_lengkap' => 'Siswa Mau Dikeluarkan',
        ]);

        $response = $this->actingAs($this->waliKelasA)
            ->post(route('rombel.students.remove', [$this->rombelA, $student]));

        $response->assertRedirect(route('rombel.show', $this->rombelA));
        $response->assertSessionHas('success');

        // Status rombel_id menjadi null namun record siswa tetap ada
        $this->assertNull($student->fresh()->rombel_id);
        $this->assertDatabaseHas('students', ['id' => $student->id, 'rombel_id' => null]);
    }

    /** 8. Siswa yang baru ditambahkan muncul pada halaman detail kelas dan halaman absensi */
    public function test_newly_added_student_appears_in_classroom_show_and_attendance_page(): void
    {
        // Tambahkan siswa via quick-store
        $this->actingAs($this->waliKelasA)
            ->post(route('rombel.students.quick-store', $this->rombelA), [
                'nama_lengkap' => 'Bintang Pratama',
                'nomor_identitas' => 'NIS-BINTANG-99',
                'jenis_kelamin' => 'Laki-laki',
            ]);

        $student = Student::where('nis', 'NIS-BINTANG-99')->firstOrFail();

        // 1. Muncul di halaman rombel.show
        $showResponse = $this->actingAs($this->waliKelasA)
            ->get(route('rombel.show', $this->rombelA));

        $showResponse->assertOk();
        $showResponse->assertSee('Bintang Pratama');
        $showResponse->assertSee('NIS-BINTANG-99');

        // 2. Muncul di halaman absensi untuk rombel ini
        $attendanceResponse = $this->actingAs($this->waliKelasA)
            ->get(route('absensi.index', ['rombel_id' => $this->rombelA->id]));

        $attendanceResponse->assertOk();
        $attendanceResponse->assertSee('Bintang Pratama');

        // 3. Pastikan belum tercatat absensi apapun (jangan otomatis hadir)
        $this->assertEquals(0, Attendance::where('student_id', $student->id)->count());
    }
}
