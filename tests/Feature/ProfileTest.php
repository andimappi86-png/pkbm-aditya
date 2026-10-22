<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_profile_summary_shows_student_nis(): void
    {
        $user = User::factory()->create();
        Student::factory()->create([
            'user_id' => $user->id,
            'nis' => '2024001',
        ]);

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSee('NIS')
            ->assertSee('2024001');
    }

    public function test_profile_page_uses_indonesian_labels_and_separate_forms(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk()
            ->assertSee('Profil Saya')
            ->assertSee('Informasi Profil')
            ->assertSee('Keamanan Akun')
            ->assertSee('Nama Lengkap')
            ->assertSee('Password Saat Ini')
            ->assertSee('Konfirmasi Password Baru')
            ->assertSee('Simpan Perubahan')
            ->assertSee('Perbarui Password')
            ->assertDontSee('Profile Information')
            ->assertDontSee('Update Password');
    }

    public function test_phone_number_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '081234567890',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $this->assertSame('081234567890', $user->refresh()->phone);
    }

    public function test_email_already_used_by_another_user_is_rejected(): void
    {
        User::factory()->create(['email' => 'terpakai@example.com']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/profile')->patch('/profile', [
            'name' => $user->name,
            'email' => 'terpakai@example.com',
        ]);

        $response->assertSessionHasErrors('email')->assertRedirect('/profile');

        $this->assertNotSame('terpakai@example.com', $user->refresh()->email);
    }

    public function test_profile_page_is_available_for_every_role(): void
    {
        $roles = [
            'operator',
            'kepala-pkbm',
            'ketua-yayasan',
            'wali-kelas',
            'tutor',
            'peserta-didik',
            'calon-peserta',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            $user = User::factory()->create();
            $user->assignRole($roleName);

            $this->actingAs($user)
                ->get('/profile')
                ->assertOk();
        }
    }
}
