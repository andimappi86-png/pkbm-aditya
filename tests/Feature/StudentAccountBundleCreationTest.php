<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

/**
 * Jalur B — tambah siswa baru sekaligus membuat akun login.
 */
class StudentAccountBundleCreationTest extends TestCase
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

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ahmad Fauzan',
            'email' => 'ahmad@test.example.com',
            'phone' => '081234567890',
            'status' => 'active',
            'student_status' => 'aktif',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nis' => '20260001',
            'nisn' => '0071234567',
            'nik' => '7601010101010001',
            'jenis_kelamin' => 'Laki-laki',
            'tanggal_lahir' => '2010-05-12',
            'alamat' => 'Jl. Pendidikan No. 1, Mamuju',
            'program_id' => $this->program->id,
            'tahun_ajaran_id' => $this->academicYear->id,
            'rombel_id' => $this->classroom->id,
        ], $overrides);
    }

    /** @test */
    public function test_student_profile_and_account_are_created_together(): void
    {
        $response = $this->actingAs($this->operator)->post(route('users.store-with-student'), $this->payload());

        $user = User::where('email', 'ahmad@test.example.com')->first();
        $this->assertNotNull($user, 'Akun login seharusnya dibuat.');

        $response->assertRedirect(route('users.show', $user));
        $response->assertSessionHas('success');

        $this->assertTrue($user->hasRole('peserta-didik'));

        $student = Student::where('nis', '20260001')->first();
        $this->assertNotNull($student, 'Profil siswa seharusnya dibuat.');
        $this->assertSame('Ahmad Fauzan', $student->nama_lengkap);
        $this->assertSame($user->id, $student->user_id);
        $this->assertSame($student->id, $user->fresh()->student->id);
    }

    /** @test */
    public function test_role_cannot_be_escalated_through_bundle_form(): void
    {
        $this->actingAs($this->operator)
            ->post(route('users.store-with-student'), $this->payload(['role' => 'operator']))
            ->assertRedirect();

        $user = User::where('email', 'ahmad@test.example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('peserta-didik'));
        $this->assertFalse($user->hasRole('operator'));
    }

    /** @test */
    public function test_bundle_form_requires_student_identity_fields(): void
    {
        $response = $this->actingAs($this->operator)->post(route('users.store-with-student'), $this->payload([
            'nis' => '',
            'nik' => '',
            'alamat' => '',
        ]));

        $response->assertSessionHasErrors(['nis', 'nik', 'alamat']);
        $this->assertDatabaseCount('students', 0);
        $this->assertDatabaseMissing('users', ['email' => 'ahmad@test.example.com']);
    }

    /** @test */
    public function test_duplicate_nis_is_rejected_without_partial_data(): void
    {
        Student::factory()->create(['nis' => '20260001', 'user_id' => null]);

        $response = $this->actingAs($this->operator)->post(route('users.store-with-student'), $this->payload());

        $response->assertSessionHasErrors('nis');
        $this->assertDatabaseCount('students', 1);
        $this->assertDatabaseMissing('users', ['email' => 'ahmad@test.example.com']);
    }

    /** @test */
    public function test_failed_account_creation_leaves_no_student_profile(): void
    {
        Event::listen('eloquent.creating: '.User::class, function (): void {
            throw new RuntimeException('Simulasi kegagalan penyimpanan akun.');
        });

        try {
            $this->actingAs($this->operator)->post(route('users.store-with-student'), $this->payload());
        } catch (RuntimeException) {
            // Kegagalan disengaja: transaksi harus dibatalkan seluruhnya.
        }

        $this->assertDatabaseCount('students', 0);
        $this->assertDatabaseMissing('users', ['email' => 'ahmad@test.example.com']);
    }

    /** @test */
    public function test_user_without_permission_cannot_use_bundle_form(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('users.view');

        // Aplikasi mengalihkan 403 ke dashboard dengan pesan error (lihat
        // bootstrap/app.php), jadi penolakan diverifikasi lewat redirect + error.
        $this->actingAs($viewer)
            ->post(route('users.store-with-student'), $this->payload())
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('users', ['email' => 'ahmad@test.example.com']);
        $this->assertDatabaseCount('students', 0);
    }

    /** @test */
    public function test_new_student_pathway_is_hidden_without_students_create_permission(): void
    {
        $manager = User::factory()->create();
        $manager->givePermissionTo('users.manage');

        $this->actingAs($manager)
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('Akun Anda tidak memiliki izin menambah data peserta didik.')
            ->assertDontSee(route('users.store-with-student'), false);
    }

    /** @test */
    public function test_new_student_pathway_explains_missing_master_data(): void
    {
        Classroom::query()->delete();
        Program::query()->delete();
        AcademicYear::query()->delete();

        $this->actingAs($this->operator)
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('Data dasar belum lengkap')
            ->assertDontSee(route('users.store-with-student'), false);
    }
}
