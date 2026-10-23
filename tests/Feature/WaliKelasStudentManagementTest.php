<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WaliKelasStudentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;

    protected User $kepalaPkbm;

    protected User $waliKelas;

    protected User $otherWaliKelas;

    protected User $plainTutor;

    protected User $dualRoleUser;

    protected Program $programA;

    protected Program $programB;

    protected AcademicYear $academicYear;

    protected Classroom $rombelWali;

    protected Classroom $rombelOther;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);

        // Programs & Academic Year
        $this->programA = Program::factory()->create(['status_aktif' => true, 'nama' => 'Paket C']);
        $this->programB = Program::factory()->create(['status_aktif' => true, 'nama' => 'Paket B']);
        $this->academicYear = AcademicYear::factory()->create(['status_aktif' => true, 'nama' => '2026/2027']);

        // Users & Roles
        $this->operator = User::factory()->create(['name' => 'Operator User']);
        $this->operator->assignRole('operator');

        $this->kepalaPkbm = User::factory()->create(['name' => 'Kepala PKBM User']);
        $this->kepalaPkbm->assignRole('kepala-pkbm');

        $this->waliKelas = User::factory()->create(['name' => 'Wali Kelas 1']);
        $this->waliKelas->assignRole('wali-kelas');

        $this->otherWaliKelas = User::factory()->create(['name' => 'Wali Kelas 2']);
        $this->otherWaliKelas->assignRole('wali-kelas');

        $this->plainTutor = User::factory()->create(['name' => 'Plain Tutor']);
        $this->plainTutor->assignRole('tutor');
        Tutor::factory()->create(['user_id' => $this->plainTutor->id]);

        $this->dualRoleUser = User::factory()->create(['name' => 'Dual Role User']);
        $this->dualRoleUser->assignRole('tutor');
        $this->dualRoleUser->assignRole('wali-kelas');
        Tutor::factory()->create(['user_id' => $this->dualRoleUser->id]);

        // Classrooms
        $this->rombelWali = Classroom::factory()->create([
            'nama_rombel' => 'Kelas X Paket C',
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'wali_kelas_id' => $this->waliKelas->id,
            'status_aktif' => true,
        ]);

        $this->rombelOther = Classroom::factory()->create([
            'nama_rombel' => 'Kelas XI Paket C',
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'wali_kelas_id' => $this->otherWaliKelas->id,
            'status_aktif' => true,
        ]);
    }

    /** Skenario 1: Wali kelas berhasil tambah siswa ke rombel sendiri. */
    public function test_wali_kelas_can_add_student_to_own_classroom(): void
    {
        $payload = [
            'nis' => 'WK001',
            'nisn' => '0012345678',
            'nik' => '7601011501900001',
            'nama_lengkap' => 'Siswa Binaan Wali',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Jl. Merdeka No. 10 Mamuju',
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $this->rombelWali->id,
            'status' => 'aktif',
        ];

        $response = $this->actingAs($this->waliKelas)->post(route('peserta-didik.store'), $payload);

        $response->assertRedirect(route('peserta-didik.index'));
        $response->assertSessionHas('success');

        $student = Student::where('nis', 'WK001')->first();
        $this->assertNotNull($student);
        $this->assertEquals($this->rombelWali->id, $student->rombel_id);
        $this->assertEquals('7601011501900001', (string) $student->nik);
    }

    /** Skenario 2: Wali kelas berhasil memilih siswa yang sudah terdaftar via bulk assign ke rombel sendiri. */
    public function test_wali_kelas_can_bulk_assign_unplaced_student_to_own_classroom(): void
    {
        $student = Student::factory()->create([
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => null,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('peserta-didik.bulk-assign.process'), [
            'student_ids' => [$student->id],
            'rombel_id' => $this->rombelWali->id,
        ]);

        $response->assertRedirect(route('peserta-didik.bulk-assign'));
        $response->assertSessionHas('success');
        $this->assertEquals($this->rombelWali->id, $student->fresh()->rombel_id);
    }

    /** Skenario 3: Wali kelas ditolak ketika mencoba menggunakan ID rombel lain. */
    public function test_wali_kelas_is_rejected_when_using_other_classroom_id_on_create(): void
    {
        $payload = [
            'nis' => 'WK002',
            'nisn' => '0012345679',
            'nik' => '7601011501900002',
            'nama_lengkap' => 'Siswa Coba Selundup',
            'jenis_kelamin' => 'Perempuan',
            'alamat' => 'Jl. Diponegoro Mamuju',
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $this->rombelOther->id,
            'status' => 'aktif',
        ];

        $response = $this->actingAs($this->waliKelas)->post(route('peserta-didik.store'), $payload);

        $response->assertSessionHasErrors('rombel_id');
        $this->assertDatabaseMissing('students', ['nis' => 'WK002']);
    }

    /** Skenario 3b: Wali kelas ditolak di bulk assign saat memilih rombel milik wali lain. */
    public function test_wali_kelas_is_rejected_when_bulk_assign_to_other_classroom(): void
    {
        $student = Student::factory()->create([
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => null,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('peserta-didik.bulk-assign.process'), [
            'student_ids' => [$student->id],
            'rombel_id' => $this->rombelOther->id,
        ]);

        // Sesuai bootstrap/app.php: 403 me-redirect ke dashboardRoute() atau return 403
        $this->assertTrue(
            $response->status() === 403
            || ($response->isRedirect() && session('error') !== null)
        );
        $this->assertNull($student->fresh()->rombel_id);
    }

    /** Skenario 4: Tutor tanpa role wali-kelas tidak mendapatkan akses administratif. */
    public function test_plain_tutor_cannot_access_student_creation_or_bulk_assign(): void
    {
        $responseCreate = $this->actingAs($this->plainTutor)->get(route('peserta-didik.create'));
        $this->assertTrue(
            $responseCreate->status() === 403
            || ($responseCreate->isRedirect() && session('error') !== null)
        );

        $responseBulk = $this->actingAs($this->plainTutor)->get(route('peserta-didik.bulk-assign'));
        $this->assertTrue(
            $responseBulk->status() === 403
            || ($responseBulk->isRedirect() && session('error') !== null)
        );

        $responseStore = $this->actingAs($this->plainTutor)->post(route('peserta-didik.store'), [
            'nis' => 'TUT01',
            'nisn' => '0012345680',
            'nik' => '7601011501900003',
            'nama_lengkap' => 'Siswa Ilegal',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Mamuju',
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'status' => 'aktif',
        ]);
        $this->assertTrue(
            $responseStore->status() === 403
            || ($responseStore->isRedirect() && session('error') !== null)
        );
        $this->assertDatabaseMissing('students', ['nis' => 'TUT01']);
    }

    /** Skenario 5: Operator dan kepala PKBM tetap dapat menjalankan alur lama. */
    public function test_operator_and_kepala_pkbm_retain_full_administrative_flow(): void
    {
        // Operator tambah siswa tanpa rombel (opsional bagi operator)
        $operatorPayload = [
            'nis' => 'OP001',
            'nisn' => '0012345681',
            'nik' => '7601011501900004',
            'nama_lengkap' => 'Siswa Baru Operator',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Mamuju',
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => null,
            'status' => 'aktif',
        ];

        $response = $this->actingAs($this->operator)->post(route('peserta-didik.store'), $operatorPayload);
        $response->assertRedirect(route('peserta-didik.index'));
        $studentOp = Student::where('nis', 'OP001')->first();
        $this->assertNotNull($studentOp);
        $this->assertNull($studentOp->rombel_id);

        // Kepala PKBM dapat menempatkan ke rombel mana pun
        $responseBulk = $this->actingAs($this->kepalaPkbm)->post(route('peserta-didik.bulk-assign.process'), [
            'student_ids' => [$studentOp->id],
            'rombel_id' => $this->rombelOther->id,
        ]);
        $responseBulk->assertRedirect(route('peserta-didik.bulk-assign'));
        $this->assertEquals($this->rombelOther->id, $studentOp->fresh()->rombel_id);
    }

    /** Skenario 6: Data NIS/NISN/NIK duplikat ditangani dengan benar. */
    public function test_duplicate_nis_nisn_and_nik_are_rejected(): void
    {
        Student::factory()->create([
            'nis' => 'DUPL01',
            'nisn' => '1111111111',
            'nik' => '7601011501900010',
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $this->rombelWali->id,
            'status' => 'aktif',
        ]);

        // Coba masukkan NIK duplikat
        $payloadDupNik = [
            'nis' => 'DUPL02',
            'nisn' => '2222222222',
            'nik' => '7601011501900010',
            'nama_lengkap' => 'Siswa NIK Duplikat',
            'jenis_kelamin' => 'Perempuan',
            'alamat' => 'Mamuju',
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $this->rombelWali->id,
            'status' => 'aktif',
        ];

        $response = $this->actingAs($this->waliKelas)->post(route('peserta-didik.store'), $payloadDupNik);
        $response->assertSessionHasErrors('nik');
        $this->assertDatabaseMissing('students', ['nis' => 'DUPL02']);

        // Coba masukkan NIS duplikat
        $payloadDupNis = array_merge($payloadDupNik, [
            'nis' => 'DUPL01',
            'nik' => '7601011501900099',
        ]);
        $response2 = $this->actingAs($this->waliKelas)->post(route('peserta-didik.store'), $payloadDupNis);
        $response2->assertSessionHasErrors('nis');
    }

    /** Skenario 7: NIK siswa yang tidak berubah tidak ditolak secara keliru pada pembaruan profil. */
    public function test_student_retaining_own_nik_on_update_is_not_rejected(): void
    {
        $student = Student::factory()->create([
            'nis' => 'UP001',
            'nisn' => '3333333333',
            'nik' => '7601011501900020',
            'nama_lengkap' => 'Nama Lama',
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $this->rombelWali->id,
            'status' => 'aktif',
            'alamat' => 'Alamat Asli',
            'jenis_kelamin' => 'Laki-laki',
        ]);

        $updatePayload = [
            'nis' => 'UP001',
            'nisn' => '3333333333',
            'nik' => '7601011501900020', // NIK milik sendiri tetap
            'nama_lengkap' => 'Nama Baru Diperbarui',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Alamat Baru Diperbarui',
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $this->rombelWali->id,
            'status' => 'aktif',
        ];

        $response = $this->actingAs($this->waliKelas)->put(route('peserta-didik.update', $student), $updatePayload);

        $response->assertRedirect(route('peserta-didik.index'));
        $response->assertSessionHas('success');
        $this->assertEquals('Nama Baru Diperbarui', $student->fresh()->nama_lengkap);
    }

    /** Skenario 8: Siswa yang sudah terikat ke rombel lain tidak dipindahkan secara diam-diam. */
    public function test_student_already_assigned_to_another_rombel_is_not_moved_silently(): void
    {
        $student = Student::factory()->create([
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $this->rombelOther->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('peserta-didik.bulk-assign.process'), [
            'student_ids' => [$student->id],
            'rombel_id' => $this->rombelWali->id,
        ]);

        $response->assertRedirect(route('peserta-didik.bulk-assign'));
        // Siswa tetap pada rombel lamanya, tidak tertimpa
        $this->assertEquals($this->rombelOther->id, $student->fresh()->rombel_id);
    }

    /** Skenario 9: Bulk assignment tidak membuka daftar seluruh rombel kepada wali kelas. */
    public function test_bulk_assign_page_only_shows_wali_kelas_own_classrooms(): void
    {
        $response = $this->actingAs($this->waliKelas)->get(route('peserta-didik.bulk-assign'));
        $response->assertStatus(200);

        /** @var Collection<int, Classroom> $classrooms */
        $classrooms = $response->viewData('classrooms');
        $this->assertTrue($classrooms->contains('id', $this->rombelWali->id));
        $this->assertFalse($classrooms->contains('id', $this->rombelOther->id));
    }

    /** Skenario 10: Status siswa yang sensitif tidak dapat diubah oleh role yang tidak berwenang. */
    public function test_wali_kelas_cannot_set_sensitive_status_like_lulus_keluar_pindah(): void
    {
        $student = Student::factory()->create([
            'nis' => 'STAT01',
            'nik' => '7601011501900030',
            'nama_lengkap' => 'Siswa Status Uji',
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $this->rombelWali->id,
            'status' => 'aktif',
            'alamat' => 'Mamuju',
            'jenis_kelamin' => 'Laki-laki',
        ]);

        $payload = [
            'nis' => 'STAT01',
            'nik' => '7601011501900030',
            'nama_lengkap' => 'Siswa Status Uji',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Mamuju',
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $this->rombelWali->id,
            'status' => 'lulus', // Sensitif: hanya operator/kepala PKBM
        ];

        $response = $this->actingAs($this->waliKelas)->put(route('peserta-didik.update', $student), $payload);
        $response->assertSessionHasErrors('status');
        $this->assertEquals('aktif', $student->fresh()->status);

        // Operator diizinkan menetapkan status lulus
        $opResponse = $this->actingAs($this->operator)->put(route('peserta-didik.update', $student), $payload);
        $opResponse->assertSessionHasNoErrors();
        $this->assertEquals('lulus', $student->fresh()->status);
    }

    /** Skenario 11: Akun tutor sekaligus wali-kelas tetap memiliki akses tutor dan wali kelas. */
    public function test_dual_role_user_retains_both_tutor_and_wali_kelas_capabilities(): void
    {
        // Tetapkan dual role user sebagai wali kelas di rombel baru
        $dualClassroom = Classroom::factory()->create([
            'nama_rombel' => 'Kelas Dual Paket C',
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'wali_kelas_id' => $this->dualRoleUser->id,
            'status_aktif' => true,
        ]);

        // Dual role bisa akses dashboard combined
        $resDashboard = $this->actingAs($this->dualRoleUser)->get(route('dashboard.combined'));
        $resDashboard->assertStatus(200);

        // Dual role bisa tambah siswa ke rombel binaannya
        $payload = [
            'nis' => 'DUAL01',
            'nisn' => '9999999999',
            'nik' => '7601011501900040',
            'nama_lengkap' => 'Siswa Dual Role',
            'jenis_kelamin' => 'Laki-laki',
            'alamat' => 'Mamuju',
            'program_id' => $this->programA->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $dualClassroom->id,
            'status' => 'aktif',
        ];

        $resStore = $this->actingAs($this->dualRoleUser)->post(route('peserta-didik.store'), $payload);
        $resStore->assertRedirect(route('peserta-didik.index'));
        $this->assertDatabaseHas('students', ['nis' => 'DUAL01']);
    }

    /** Skenario 12: Halaman detail rombel menyediakan link Tambah Siswa dengan rombel_id terisi. */
    public function test_classroom_show_provides_add_student_link_and_form_prefills(): void
    {
        $responseShow = $this->actingAs($this->waliKelas)->get(route('rombel.show', $this->rombelWali));
        $responseShow->assertStatus(200);
        $responseShow->assertSee(route('peserta-didik.create', ['rombel_id' => $this->rombelWali->id]));
        $responseShow->assertSee(route('peserta-didik.bulk-assign', ['rombel_id' => $this->rombelWali->id]));

        $responseCreate = $this->actingAs($this->waliKelas)->get(route('peserta-didik.create', ['rombel_id' => $this->rombelWali->id]));
        $responseCreate->assertStatus(200);
        $this->assertEquals($this->rombelWali->id, $responseCreate->viewData('presetRombelId'));
    }
}
