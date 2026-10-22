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
            'mode' => 'siswa',
            'student_id' => $student->id,
            'jenis_dokumen' => 'kk',
            'file' => $file,
        ]);

        $response->assertRedirect(route('documents.index'));
        $this->assertDatabaseHas('documents', [
            'student_id' => $student->id,
            'ppdb_id' => null,
            'jenis_dokumen' => 'kk',
        ]);
    }

    public function test_admin_can_upload_manual_document()
    {
        Storage::fake('public');
        $admin = $this->createAdminUser();

        $file = UploadedFile::fake()->create('sk_operasional.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('documents.store'), [
            'mode' => 'manual',
            'jenis_dokumen' => 'lainnya',
            'catatan' => 'SK Izin Operasional Lembaga No 123/2026',
            'file' => $file,
        ]);

        $response->assertRedirect(route('documents.index'));
        $this->assertDatabaseHas('documents', [
            'student_id' => null,
            'ppdb_id' => null,
            'jenis_dokumen' => 'lainnya',
            'catatan' => 'SK Izin Operasional Lembaga No 123/2026',
        ]);
    }

    public function test_validation_fails_for_mode_siswa_without_student_id()
    {
        $admin = $this->createAdminUser();
        $file = UploadedFile::fake()->create('doc.pdf', 100);

        $response = $this->actingAs($admin)->post(route('documents.store'), [
            'mode' => 'siswa',
            'jenis_dokumen' => 'kk',
            'file' => $file,
        ]);

        $response->assertSessionHasErrors(['student_id']);
    }

    public function test_manual_document_displayed_in_index_and_show()
    {
        $admin = $this->createAdminUser();
        $document = Document::factory()->create([
            'student_id' => null,
            'ppdb_id' => null,
            'jenis_dokumen' => 'lainnya',
            'catatan' => 'Arsip SK Lembaga',
        ]);

        // Check index
        $indexResponse = $this->actingAs($admin)->get(route('documents.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Arsip SK Lembaga');

        // Check show
        $showResponse = $this->actingAs($admin)->get(route('documents.show', $document));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Dokumen Manual / Umum');
        $showResponse->assertSee('Arsip SK Lembaga');
    }

    public function test_admin_can_verify_document()
    {
        $admin = $this->createAdminUser();
        $document = Document::factory()->create(['status_verifikasi' => 'pending']);

        $response = $this->actingAs($admin)->post(route('documents.verify', $document), [
            'status_verifikasi' => 'diverifikasi',
            'catatan' => 'OK',
        ]);

        $response->assertRedirect(route('documents.index'));
        $response->assertSessionHas('success', 'Status verifikasi dokumen berhasil diperbarui.');
        $document->refresh();
        $this->assertEquals('diverifikasi', $document->status_verifikasi);
        $this->assertEquals('OK', $document->catatan);
    }

    public function test_admin_can_verify_manual_document()
    {
        $admin = $this->createAdminUser();
        $document = Document::factory()->create([
            'student_id' => null,
            'ppdb_id' => null,
            'status_verifikasi' => 'pending',
            'catatan' => null,
        ]);

        $response = $this->actingAs($admin)->post(route('documents.verify', $document), [
            'status_verifikasi' => 'diverifikasi',
            'catatan' => 'Dokumen manual terverifikasi sah',
        ]);

        $response->assertRedirect(route('documents.index'));
        $response->assertSessionHas('success', 'Status verifikasi dokumen berhasil diperbarui.');
        $document->refresh();
        $this->assertEquals('diverifikasi', $document->status_verifikasi);
        $this->assertEquals('Dokumen manual terverifikasi sah', $document->catatan);
    }

    public function test_document_verification_fails_with_invalid_status()
    {
        $admin = $this->createAdminUser();
        $document = Document::factory()->create(['status_verifikasi' => 'pending']);

        $response = $this->actingAs($admin)->post(route('documents.verify', $document), [
            'status_verifikasi' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['status_verifikasi']);
        $document->refresh();
        $this->assertEquals('pending', $document->status_verifikasi);
    }

    public function test_unauthorized_user_cannot_verify_document()
    {
        $user = User::factory()->create();
        $user->assignRole('peserta-didik');
        $document = Document::factory()->create(['status_verifikasi' => 'pending']);

        $response = $this->actingAs($user)->post(route('documents.verify', $document), [
            'status_verifikasi' => 'diverifikasi',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $jsonResponse = $this->actingAs($user)->postJson(route('documents.verify', $document), [
            'status_verifikasi' => 'diverifikasi',
        ]);
        $jsonResponse->assertStatus(403);

        $document->refresh();
        $this->assertEquals('pending', $document->status_verifikasi);
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
