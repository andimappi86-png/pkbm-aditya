<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Letter;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentLetterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function createAdminUser()
    {
        $user = User::factory()->create();
        $user->assignRole('kepala-pkbm');

        return $user;
    }

    public function test_admin_can_view_documents()
    {
        $admin = $this->createAdminUser();
        $response = $this->actingAs($admin)->get(route('documents.index'));
        $response->assertStatus(200);
        $response->assertViewIs('documents.index');
    }

    public function test_admin_can_upload_document()
    {
        Storage::fake('public');
        $admin = $this->createAdminUser();
        $student = Student::factory()->create();

        $file = UploadedFile::fake()->image('kk.jpg');

        $response = $this->actingAs($admin)->post(route('documents.store'), [
            'student_id' => $student->id,
            'jenis_dokumen' => 'kk',
            'file' => $file,
        ]);

        $response->assertRedirect(route('documents.index'));
        $this->assertDatabaseHas('documents', [
            'student_id' => $student->id,
            'jenis_dokumen' => 'kk',
        ]);
    }

    public function test_admin_can_verify_document()
    {
        $admin = $this->createAdminUser();
        $document = Document::factory()->create(['status_verifikasi' => 'pending']);

        $response = $this->actingAs($admin)->post(route('documents.verify', $document), [
            'status_verifikasi' => 'diverifikasi',
            'catatan' => 'OK',
        ]);

        $response->assertRedirect(route('documents.show', $document));
        $document->refresh();
        $this->assertEquals('diverifikasi', $document->status_verifikasi);
    }

    public function test_admin_can_create_letter()
    {
        $admin = $this->createAdminUser();
        $student = Student::factory()->create();

        $response = $this->actingAs($admin)->post(route('letters.store'), [
            'student_id' => $student->id,
            'jenis_surat' => 'aktif_belajar',
            'tanggal' => now()->format('Y-m-d'),
            'tujuan' => 'Beasiswa',
            'penandatangan_nama' => 'Admin PKBM',
            'isi' => 'Ini adalah isi surat keterangan.',
        ]);

        $response->assertRedirect(route('letters.index'));
        $this->assertDatabaseHas('letters', [
            'student_id' => $student->id,
            'jenis_surat' => 'aktif_belajar',
            'tujuan' => 'Beasiswa',
        ]);
    }

    public function test_admin_can_print_letter()
    {
        $admin = $this->createAdminUser();
        $letter = Letter::factory()->create();

        $response = $this->actingAs($admin)->get(route('letters.print', $letter));
        $response->assertStatus(200);
        $response->assertViewIs('letters.print');
    }
}
