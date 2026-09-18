<?php

namespace Tests\Feature;

use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TutorTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('permission:cache-reset');
        $this->seed(RolePermissionSeeder::class);
    }

    /** @test */
    public function test_tutor_can_access_dashboard()
    {
        $this->actingAs($this->seedUserWithPermission('tutors.view'));

        $response = $this->get('/dashboard/tutor');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_tutor_can_view_index()
    {
        $this->actingAs($this->seedUserWithPermission('tutors.view'));

        $response = $this->get(route('tutors.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_tutor_can_create()
    {
        $this->actingAs($this->seedUserWithPermission('tutors.create'));

        $response = $this->post(route('tutors.store'), [
            'nomor_induk' => 'TUTOR-001',
            'nama_lengkap' => $this->faker->name(),
            'jenis_kelamin' => 'Laki-laki',
            'status_tutor' => 'aktif',
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function test_tutor_can_show()
    {
        $tutor = $this->seedTutor();

        $this->actingAs($this->seedUserWithPermission('tutors.view'));

        $response = $this->get(route('tutors.show', $tutor->id));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_tutor_can_edit_get()
    {
        $tutor = $this->seedTutor();

        $this->actingAs($this->seedUserWithPermission('tutors.update'));

        $response = $this->get(route('tutors.edit', $tutor->id));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_tutor_can_update()
    {
        $tutor = $this->seedTutor();

        $this->actingAs($this->seedUserWithPermission('tutors.update'));

        $response = $this->put(route('tutors.update', $tutor->id), [
            'nama_lengkap' => 'Nama Tutor Updated',
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function test_tutor_can_delete()
    {
        $tutor = $this->seedTutor();

        $this->actingAs($this->seedUserWithPermission('tutors.delete'));

        $response = $this->delete(route('tutors.destroy', $tutor->id));

        $response->assertRedirect();
    }

    /** @test */
    public function test_tutor_search_and_filter()
    {
        $this->seedTutor(['nama_lengkap' => 'Test Tutor A', 'status_tutor' => 'aktif']);
        $this->seedTutor(['nama_lengkap' => 'Test Tutor B', 'status_tutor' => 'aktif']);

        $this->actingAs($this->seedUserWithPermission('tutors.view'));

        $response = $this->get(route('tutors.index').'?search=Test%20Tutor%20A');

        $response->assertStatus(200);
        $response->assertSee('Test Tutor A');
    }

    /** @test */
    public function test_tutor_filter_by_status()
    {
        $this->seedTutor(['status_tutor' => 'aktif']);
        $this->seedTutor(['status_tutor' => 'tidak_aktif']);

        $this->actingAs($this->seedUserWithPermission('tutors.view'));

        $response = $this->get(route('tutors.index').'?status_tutor=aktif');

        $response->assertStatus(200);
        $response->assertSee('Aktif');
    }

    /** @test */
    public function test_tutor_pagination()
    {
        Tutor::factory()->count(35)->create();

        $this->actingAs($this->seedUserWithPermission('tutors.view'));

        $response = $this->get(route('tutors.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_tutor_unique_nomor_induk_on_create()
    {
        $this->seedTutor(['nomor_induk' => 'TUTOR-UNIQUE']);

        $this->actingAs($this->seedUserWithPermission('tutors.create'));

        $response = $this->post(route('tutors.store'), [
            'nomor_induk' => 'TUTOR-UNIQUE',
            'nama_lengkap' => $this->faker->name(),
            'jenis_kelamin' => 'Laki-laki',
            'status_tutor' => 'aktif',
        ]);

        $response->assertSessionHasErrors('nomor_induk');
    }

    /** @test */
    public function test_tutor_unique_nik_nullable()
    {
        $this->seedTutor(['nik' => '76000000000001']);

        $this->actingAs($this->seedUserWithPermission('tutors.create'));

        $response = $this->post(route('tutors.store'), [
            'nomor_induk' => $this->faker->unique()->numerify('TUTOR-####'),
            'nik' => '76000000000001',
            'nama_lengkap' => $this->faker->name(),
            'jenis_kelamin' => 'Laki-laki',
            'status_tutor' => 'aktif',
        ]);

        $response->assertSessionHasErrors('nik');
    }

    protected function seedUserWithPermission(string $permission): User
    {
        $user = User::firstOrCreate(
            ['email' => 'test@test.com'],
            fn () => [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        $user->assignRole('tutor');

        foreach ($user->permissions ?? [] as $existing) {
            if ($existing->name === $permission) {
                return $user;
            }
        }

        $user->givePermissionTo($permission);

        return $user;
    }

    protected function seedUserWithoutPermission(string $permission): User
    {
        $user = User::firstOrCreate(
            ['email' => 'test2@test.com'],
            fn () => [
                'name' => 'Test User 2',
                'password' => bcrypt('password'),
            ]
        );

        $user->assignRole('tutor');

        return $user;
    }

    protected function seedTutor(array $overrides = []): Tutor
    {
        $tutor = Tutor::factory()->create($overrides);

        return $tutor;
    }
}
