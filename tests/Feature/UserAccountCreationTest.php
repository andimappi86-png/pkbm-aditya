<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserAccountCreationTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    private Program $program;

    private AcademicYear $academicYear;

    private Classroom $classroom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');

        $this->program = Program::factory()->create(['status_aktif' => true]);
        $this->academicYear = AcademicYear::factory()->create(['status_aktif' => true]);
        $this->classroom = Classroom::factory()->create([
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'status_aktif' => true,
        ]);
    }

    // ------- peserta-didik role -------

    /** @test */
    public function test_create_form_marks_student_profile_as_required_for_peserta_didik(): void
    {
        Student::factory()->create(['rombel_id' => $this->classroom->id, 'status' => 'aktif', 'user_id' => null]);
        Tutor::factory()->create(['user_id' => null]);

        $response = $this->actingAs($this->operator)->get(route('users.create'));

        $response->assertOk()
            ->assertSee('Pilih Siswa')
            ->assertSee('Profil Tutor')
            ->assertSee('Wajib')
            ->assertSee("x-bind:required=\"role === 'peserta-didik'\"", false)
            ->assertSee("x-bind:disabled=\"role !== 'peserta-didik'\"", false)
            ->assertSee("x-bind:required=\"role === 'tutor' || role === 'wali-kelas'\"", false)
            ->assertSee("x-bind:disabled=\"role !== 'tutor' && role !== 'wali-kelas'\"", false)
            ->assertDontSee('Profil Terhubung (Opsional)')
            ->assertSee('Role ini tidak memerlukan profil siswa atau tutor.');
    }

    /** @test */
    public function test_validation_errors_appear_in_form_summary_and_on_the_field(): void
    {
        $response = $this->actingAs($this->operator)
            ->from(route('users.create'))
            ->followingRedirects()
            ->post(route('users.store'), [
                'name' => 'Budi Santoso',
                'email' => 'budi@test.example.com',
                'phone' => '08123456789',
                'status' => 'active',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'peserta-didik',
            ]);

        $response->assertOk()
            ->assertSee('Validasi Gagal')
            ->assertSee('Profil siswa wajib dipilih');
    }

    /** @test */
    public function test_peserta_didik_without_student_id_is_rejected(): void
    {
        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@test.example.com',
            'phone' => '08123456789',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'peserta-didik',
            // student_id sengaja tidak dikirim
        ]);

        $response->assertSessionHasErrors('student_id');
        $errors = session('errors');
        $this->assertStringContainsString('wajib dipilih', $errors->first('student_id'));
        $this->assertDatabaseMissing('users', ['email' => 'budi@test.example.com']);
    }

    /** @test */
    public function test_peserta_didik_with_valid_student_profile_succeeds(): void
    {
        $student = Student::factory()->create([
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
            'user_id' => null,
        ]);

        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@test.example.com',
            'phone' => '08123456789',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'peserta-didik',
            'student_id' => $student->id,
        ]);

        $response->assertRedirect(route('users.show', User::where('email', 'budi@test.example.com')->first()));
        $response->assertSessionHas('success');

        $user = User::where('email', 'budi@test.example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('peserta-didik'));
        $this->assertEquals($student->id, $user->student->id);
        $student->refresh();
        $this->assertEquals($user->id, $student->user_id);
    }

    /** @test */
    public function test_peserta_didik_cannot_link_already_linked_student(): void
    {
        $studentA = Student::factory()->create(['rombel_id' => $this->classroom->id, 'status' => 'aktif', 'user_id' => null]);
        $existingUser = User::factory()->create();
        $existingUser->assignRole('peserta-didik');
        $studentA->update(['user_id' => $existingUser->id]);

        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'New User',
            'email' => 'new@test.example.com',
            'phone' => '08111111111',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'peserta-didik',
            'student_id' => $studentA->id,
        ]);

        $response->assertSessionHasErrors('student_id');
        $errors = session('errors');
        $this->assertStringContainsString('tertaut ke akun lain', $errors->first('student_id'));
    }

    /** @test */
    public function test_student_dropdown_excludes_already_linked_students(): void
    {
        $studentA = Student::factory()->create(['rombel_id' => $this->classroom->id, 'status' => 'aktif', 'user_id' => null]);
        $studentB = Student::factory()->create(['rombel_id' => $this->classroom->id, 'status' => 'aktif', 'user_id' => null]);
        $linkedUser = User::factory()->create();
        $linkedUser->assignRole('peserta-didik');
        $studentB->update(['user_id' => $linkedUser->id]);

        $response = $this->actingAs($this->operator)->get(route('users.create'));

        $response->assertSee($studentA->nama_lengkap);
        $response->assertSee('NIS '.$studentA->nis);
        $response->assertDontSee($studentB->nama_lengkap);
    }

    /** @test */
    public function test_empty_student_dropdown_shows_notice_and_link_to_create(): void
    {
        // Tanpa membuat student
        $response = $this->actingAs($this->operator)->get(route('users.create'));

        $response->assertSee('Belum ada profil siswa yang bisa ditautkan');
        $response->assertSee('Tambah Peserta Didik');
        $response->assertSee(route('peserta-didik.create'));
    }

    // ------- tutor role -------

    /** @test */
    public function test_tutor_without_tutor_id_is_rejected(): void
    {
        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Siti Rahma',
            'email' => 'siti@test.example.com',
            'phone' => '08129876543',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'tutor',
            // tutor_id sengaja tidak dikirim
        ]);

        $response->assertSessionHasErrors('tutor_id');
        $errors = session('errors');
        $this->assertStringContainsString('wajib dipilih', $errors->first('tutor_id'));
    }

    /** @test */
    public function test_tutor_with_valid_tutor_profile_succeeds(): void
    {
        $tutor = Tutor::factory()->create(['user_id' => null]);

        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Siti Rahma',
            'email' => 'siti@test.example.com',
            'phone' => '08129876543',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'tutor',
            'tutor_id' => $tutor->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user = User::where('email', 'siti@test.example.com')->first();
        $this->assertTrue($user->hasRole('tutor'));
        $this->assertEquals($tutor->id, $user->tutor->id);
    }

    /** @test */
    public function test_tutor_cannot_link_already_linked_tutor(): void
    {
        $tutorA = Tutor::factory()->create(['user_id' => null]);
        $existingUser = User::factory()->create();
        $existingUser->assignRole('tutor');
        $tutorA->update(['user_id' => $existingUser->id]);

        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'New Tutor',
            'email' => 'newtutor@test.example.com',
            'phone' => '08111111111',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'tutor',
            'tutor_id' => $tutorA->id,
        ]);

        $response->assertSessionHasErrors('tutor_id');
        $errors = session('errors');
        $this->assertStringContainsString('tertaut ke akun lain', $errors->first('tutor_id'));
    }

    /** @test */
    public function test_tutor_dropdown_excludes_already_linked_tutors(): void
    {
        $tutorA = Tutor::factory()->create(['user_id' => null]);
        $tutorB = Tutor::factory()->create(['user_id' => null]);
        $linkedUser = User::factory()->create();
        $linkedUser->assignRole('tutor');
        $tutorB->update(['user_id' => $linkedUser->id]);

        $response = $this->actingAs($this->operator)->get(route('users.create'));

        $response->assertSee($tutorA->nama_lengkap);
        $response->assertSee('NIP '.$tutorA->nomor_induk);
        $response->assertDontSee($tutorB->nama_lengkap);
    }

    // ------- wali-kelas role -------

    /** @test */
    public function test_wali_kelas_without_tutor_id_is_rejected(): void
    {
        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Wali Kelas Baru',
            'email' => 'wali@test.example.com',
            'phone' => '08123334444',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'wali-kelas',
            // tutor_id sengaja tidak dikirim
        ]);

        $response->assertSessionHasErrors('tutor_id');
        $errors = session('errors');
        $this->assertStringContainsString('wajib dipilih', $errors->first('tutor_id'));
    }

    /** @test */
    public function test_wali_kelas_with_valid_tutor_profile_succeeds(): void
    {
        $tutor = Tutor::factory()->create(['user_id' => null]);

        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Wali Kelas Baru',
            'email' => 'wali@test.example.com',
            'phone' => '08123334444',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'wali-kelas',
            'tutor_id' => $tutor->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user = User::where('email', 'wali@test.example.com')->first();
        $this->assertTrue($user->hasRole('wali-kelas'));
        $this->assertEquals($tutor->id, $user->tutor->id);
    }

    /** @test */
    public function test_wali_kelas_cannot_link_already_linked_tutor(): void
    {
        $tutorA = Tutor::factory()->create(['user_id' => null]);
        $existingUser = User::factory()->create();
        $existingUser->assignRole('tutor');
        $tutorA->update(['user_id' => $existingUser->id]);

        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'New Wali Kelas',
            'email' => 'newwali@test.example.com',
            'phone' => '08111111111',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'wali-kelas',
            'tutor_id' => $tutorA->id,
        ]);

        $response->assertSessionHasErrors('tutor_id');
        $errors = session('errors');
        $this->assertStringContainsString('tertaut ke akun lain', $errors->first('tutor_id'));
    }

    // ------- other roles -------

    /** @test */
    public function test_operator_role_does_not_require_student_or_tutor_profile(): void
    {
        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Admin Baru',
            'email' => 'admin@test.example.com',
            'phone' => '08123334444',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'operator',
            // student_id/tutor_id tidak diisi
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user = User::where('email', 'admin@test.example.com')->first();
        $this->assertTrue($user->hasRole('operator'));
        $this->assertNull($user->student);
        $this->assertNull($user->tutor);
    }

    /** @test */
    public function test_kepala_pkbm_role_does_not_require_profile(): void
    {
        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Kepala PKBM',
            'email' => 'kepala@test.example.com',
            'phone' => '08123334444',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'kepala-pkbm',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $user = User::where('email', 'kepala@test.example.com')->first();
        $this->assertTrue($user->hasRole('kepala-pkbm'));
    }

    /** @test */
    public function test_submitting_student_id_for_operator_role_is_rejected(): void
    {
        $student = Student::factory()->create(['rombel_id' => $this->classroom->id, 'status' => 'aktif', 'user_id' => null]);

        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Admin Baru',
            'email' => 'admin2@test.example.com',
            'phone' => '08123334444',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'operator',
            'student_id' => $student->id,
        ]);

        $response->assertSessionHasErrors('student_id');
        $errors = session('errors');
        $this->assertStringContainsString('hanya diperbolehkan untuk role Peserta Didik', $errors->first('student_id'));
    }

    // ------- password confirmation -------

    /** @test */
    public function test_missing_password_confirmation_is_rejected_with_clear_message(): void
    {
        $student = Student::factory()->create(['rombel_id' => $this->classroom->id, 'status' => 'aktif', 'user_id' => null]);

        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@test.example.com',
            'phone' => '08123456789',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => '', // kosong
            'role' => 'peserta-didik',
            'student_id' => $student->id,
        ]);

        $response->assertSessionHasErrors('password_confirmation');
        $errors = session('errors');
        $this->assertStringContainsString('wajib diisi', $errors->first('password_confirmation'));
    }

    /** @test */
    public function test_mismatched_password_confirmation_is_rejected_with_clear_message(): void
    {
        $student = Student::factory()->create(['rombel_id' => $this->classroom->id, 'status' => 'aktif', 'user_id' => null]);

        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@test.example.com',
            'phone' => '08123456789',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'different456', // tidak cocok
            'role' => 'peserta-didik',
            'student_id' => $student->id,
        ]);

        $response->assertSessionHasErrors('password_confirmation');
        $errors = session('errors');
        $this->assertStringContainsString('tidak sama', $errors->first('password_confirmation'));
    }

    /** @test */
    public function test_short_password_is_rejected(): void
    {
        $student = Student::factory()->create(['rombel_id' => $this->classroom->id, 'status' => 'aktif', 'user_id' => null]);

        $response = $this->actingAs($this->operator)->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@test.example.com',
            'phone' => '08123456789',
            'status' => 'active',
            'password' => 'short',
            'password_confirmation' => 'short',
            'role' => 'peserta-didik',
            'student_id' => $student->id,
        ]);

        $response->assertSessionHasErrors('password');
        $errors = session('errors');
        $this->assertStringContainsString('minimal 8', $errors->first('password'));
    }

    // ------- perubahan role vs profil tertaut -------

    /** @test */
    public function test_role_change_is_rejected_while_student_profile_is_linked(): void
    {
        $siswa = User::factory()->create(['status' => 'active']);
        $siswa->assignRole('peserta-didik');
        $student = Student::factory()->create([
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
            'user_id' => $siswa->id,
        ]);

        $response = $this->actingAs($this->operator)->put(route('users.update', $siswa), [
            'name' => $siswa->name,
            'email' => $siswa->email,
            'status' => 'active',
            'role' => 'operator',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertStringContainsString('Penautan Akun', session('errors')->first('role'));

        $siswa->refresh();
        $this->assertTrue($siswa->hasRole('peserta-didik'));
        $this->assertFalse($siswa->hasRole('operator'));
        $this->assertSame($siswa->id, $student->fresh()->user_id);
    }

    /** @test */
    public function test_role_change_is_rejected_while_tutor_profile_is_linked(): void
    {
        $tutorUser = User::factory()->create(['status' => 'active']);
        $tutorUser->assignRole('tutor');
        $tutor = Tutor::factory()->create(['user_id' => $tutorUser->id]);

        // Coba ubah ke operator (bukan tutor/wali-kelas) - harus ditolak
        $response = $this->actingAs($this->operator)->put(route('users.update', $tutorUser), [
            'name' => $tutorUser->name,
            'email' => $tutorUser->email,
            'status' => 'active',
            'role' => 'operator',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertTrue($tutorUser->fresh()->hasRole('tutor'));
        $this->assertSame($tutorUser->id, $tutor->fresh()->user_id);
    }

    /** @test */
    public function test_role_change_is_allowed_after_profile_link_is_removed(): void
    {
        $siswa = User::factory()->create(['status' => 'active']);
        $siswa->assignRole('peserta-didik');
        Student::factory()->create([
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
            'user_id' => null,
        ]);

        $response = $this->actingAs($this->operator)->put(route('users.update', $siswa), [
            'name' => $siswa->name,
            'email' => $siswa->email,
            'status' => 'active',
            'role' => 'operator',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue($siswa->fresh()->hasRole('operator'));
    }

    /** @test */
    public function test_profile_fields_and_identity_changes_are_allowed_on_linked_account(): void
    {
        $siswa = User::factory()->create(['status' => 'active']);
        $siswa->assignRole('peserta-didik');
        Student::factory()->create([
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
            'user_id' => $siswa->id,
        ]);

        $response = $this->actingAs($this->operator)->put(route('users.update', $siswa), [
            'name' => 'Nama Baru',
            'email' => 'nama.baru@test.example.com',
            'phone' => '081200000000',
            'status' => 'inactive',
            'role' => 'peserta-didik',
        ]);

        $response->assertSessionHasNoErrors();
        $siswa->refresh();
        $this->assertSame('Nama Baru', $siswa->name);
        $this->assertSame('inactive', $siswa->status);
    }

    // ------- update role & profil -------

    /** @test */
    public function test_update_wali_kelas_without_tutor_id_is_rejected(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('operator');

        $response = $this->actingAs($this->operator)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'active',
            'role' => 'wali-kelas',
            // tutor_id sengaja tidak dikirim
        ]);

        $response->assertSessionHasErrors('tutor_id');
        $errors = session('errors');
        $this->assertStringContainsString('wajib dipilih', $errors->first('tutor_id'));
    }

    /** @test */
    public function test_update_wali_kelas_with_valid_tutor_profile_succeeds(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('operator');
        $tutor = Tutor::factory()->create(['user_id' => null]);

        $response = $this->actingAs($this->operator)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'active',
            'role' => 'wali-kelas',
            'tutor_id' => $tutor->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue($user->hasRole('wali-kelas'));
        $this->assertEquals($tutor->id, $user->tutor->id);
    }

    /** @test */
    public function test_update_tutor_without_tutor_id_is_rejected(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('operator');

        $response = $this->actingAs($this->operator)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'active',
            'role' => 'tutor',
            // tutor_id sengaja tidak dikirim
        ]);

        $response->assertSessionHasErrors('tutor_id');
        $errors = session('errors');
        $this->assertStringContainsString('wajib dipilih', $errors->first('tutor_id'));
    }

    /** @test */
    public function test_update_tutor_with_valid_tutor_profile_succeeds(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('operator');
        $tutor = Tutor::factory()->create(['user_id' => null]);

        $response = $this->actingAs($this->operator)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'active',
            'role' => 'tutor',
            'tutor_id' => $tutor->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue($user->hasRole('tutor'));
        $this->assertEquals($tutor->id, $user->tutor->id);
    }

    /** @test */
    public function test_update_peserta_didik_without_student_id_is_rejected(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('operator');

        $response = $this->actingAs($this->operator)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'active',
            'role' => 'peserta-didik',
            // student_id sengaja tidak dikirim
        ]);

        $response->assertSessionHasErrors('student_id');
        $errors = session('errors');
        $this->assertStringContainsString('wajib dipilih', $errors->first('student_id'));
    }

    /** @test */
    public function test_update_peserta_didik_with_valid_student_profile_succeeds(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('operator');
        $student = Student::factory()->create(['rombel_id' => $this->classroom->id, 'status' => 'aktif', 'user_id' => null]);

        $response = $this->actingAs($this->operator)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'active',
            'role' => 'peserta-didik',
            'student_id' => $student->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue($user->hasRole('peserta-didik'));
        $this->assertEquals($student->id, $user->student->id);
    }

    /** @test */
    public function test_update_cannot_link_already_linked_tutor(): void
    {
        $tutorA = Tutor::factory()->create(['user_id' => null]);
        $existingUser = User::factory()->create();
        $existingUser->assignRole('tutor');
        $tutorA->update(['user_id' => $existingUser->id]);

        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('operator');

        $response = $this->actingAs($this->operator)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'active',
            'role' => 'wali-kelas',
            'tutor_id' => $tutorA->id,
        ]);

        $response->assertSessionHasErrors('tutor_id');
        $errors = session('errors');
        $this->assertStringContainsString('tertaut ke akun lain', $errors->first('tutor_id'));
    }

    /** @test */
    public function test_update_cannot_link_already_linked_student(): void
    {
        $studentA = Student::factory()->create(['rombel_id' => $this->classroom->id, 'status' => 'aktif', 'user_id' => null]);
        $existingUser = User::factory()->create();
        $existingUser->assignRole('peserta-didik');
        $studentA->update(['user_id' => $existingUser->id]);

        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('operator');

        $response = $this->actingAs($this->operator)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'active',
            'role' => 'peserta-didik',
            'student_id' => $studentA->id,
        ]);

        $response->assertSessionHasErrors('student_id');
        $errors = session('errors');
        $this->assertStringContainsString('tertaut ke akun lain', $errors->first('student_id'));
    }

    /** @test */
    public function test_update_tutor_to_wali_kelas_keeps_same_tutor_profile(): void
    {
        // User sudah punya tutor profile, ganti role tutor -> wali-kelas
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('tutor');
        $tutor = Tutor::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->operator)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'active',
            'role' => 'wali-kelas',
            // tutor_id tidak perlu dikirim karena sudah ada
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue($user->hasRole('wali-kelas'));
        $this->assertEquals($tutor->id, $user->tutor->id);
    }

    /** @test */
    public function test_update_wali_kelas_to_tutor_keeps_same_tutor_profile(): void
    {
        // User sudah punya tutor profile, ganti role wali-kelas -> tutor
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('wali-kelas');
        $tutor = Tutor::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->operator)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'active',
            'role' => 'tutor',
            // tutor_id tidak perlu dikirim karena sudah ada
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue($user->hasRole('tutor'));
        $this->assertEquals($tutor->id, $user->tutor->id);
    }

    /** @test */
    public function test_update_identity_fields_without_role_change_works(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('peserta-didik');
        $student = Student::factory()->create(['rombel_id' => $this->classroom->id, 'status' => 'aktif', 'user_id' => $user->id]);

        $response = $this->actingAs($this->operator)->put(route('users.update', $user), [
            'name' => 'Nama Diperbarui',
            'email' => 'diperbarui@test.example.com',
            'phone' => '081200000000',
            'status' => 'inactive',
            'role' => 'peserta-didik',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Nama Diperbarui', $user->name);
        $this->assertSame('diperbarui@test.example.com', $user->email);
        $this->assertSame('inactive', $user->status);
        $this->assertTrue($user->hasRole('peserta-didik'));
        $this->assertSame($student->id, $user->student->id);
    }

    /** @test */
    public function test_update_operator_does_not_require_profile(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('peserta-didik');
        $student = Student::factory()->create(['rombel_id' => $this->classroom->id, 'status' => 'aktif', 'user_id' => $user->id]);

        // Ubah ke operator, profil siswa akan dilepaskan otomatis karena role tidak cocok
        $response = $this->actingAs($this->operator)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'active',
            'role' => 'operator',
        ]);

        // Harus ditolak karena profil siswa masih tertaut
        $response->assertSessionHasErrors('role');
        $this->assertStringContainsString('Penautan Akun', session('errors')->first('role'));
    }

    /** @test */
    public function test_update_submit_student_id_for_wali_kelas_is_rejected(): void
    {
        $student = Student::factory()->create(['rombel_id' => $this->classroom->id, 'status' => 'aktif', 'user_id' => null]);
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('operator');

        $response = $this->actingAs($this->operator)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'active',
            'role' => 'wali-kelas',
            'student_id' => $student->id,
        ]);

        $response->assertSessionHasErrors('student_id');
        $errors = session('errors');
        $this->assertStringContainsString('hanya diperbolehkan untuk role Peserta Didik', $errors->first('student_id'));
    }

    /** @test */
    public function test_update_submit_tutor_id_for_peserta_didik_is_rejected(): void
    {
        $tutor = Tutor::factory()->create(['user_id' => null]);
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('operator');

        $response = $this->actingAs($this->operator)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'active',
            'role' => 'peserta-didik',
            'tutor_id' => $tutor->id,
        ]);

        $response->assertSessionHasErrors('tutor_id');
        $errors = session('errors');
        $this->assertStringContainsString('hanya diperbolehkan untuk role Tutor dan Wali Kelas', $errors->first('tutor_id'));
    }

    // ------- konteks form dari alur Buku Induk -------

    /** @test */
    public function test_returning_from_student_registration_preselects_student_and_role(): void
    {
        $student = Student::factory()->create([
            'rombel_id' => $this->classroom->id,
            'status' => 'aktif',
            'user_id' => null,
        ]);

        $response = $this->actingAs($this->operator)->get(route('users.create', ['student' => $student->id]));

        $response->assertOk()
            ->assertSee('Peserta Didik')
            ->assertSee($student->nama_lengkap)
            ->assertSee('Dipilih');
    }

    /** @test */
    public function test_create_student_action_is_hidden_without_students_create_permission(): void
    {
        $manager = User::factory()->create();
        $manager->givePermissionTo('users.manage');

        $response = $this->actingAs($manager)->get(route('users.create'));

        $response->assertOk()
            ->assertDontSee('Tambah Peserta Didik')
            ->assertSee('belum memiliki izin menambah peserta didik');
    }
}
