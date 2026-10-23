<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_uploaded_document_is_stored_in_private_local_disk_and_not_in_public_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('kepala-pkbm');

        $student = Student::factory()->create();
        $file = UploadedFile::fake()->create('rapor.pdf', 200, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('documents.store'), [
            'mode' => 'siswa',
            'student_id' => $student->id,
            'jenis_dokumen' => 'rapor',
            'file' => $file,
        ]);

        $response->assertRedirect(route('documents.index'));

        $document = Document::where('student_id', $student->id)->first();
        $this->assertNotNull($document);

        // Verify stored in private local disk
        Storage::disk('local')->assertExists($document->file_path);
        // Verify NOT stored in public disk
        Storage::disk('public')->assertMissing($document->file_path);
    }

    public function test_document_cannot_be_retrieved_via_public_storage_symlink_url(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('kepala-pkbm');

        $student = Student::factory()->create();
        $file = UploadedFile::fake()->create('rapor_privat.pdf', 150, 'application/pdf');

        $this->actingAs($admin)->post(route('documents.store'), [
            'mode' => 'siswa',
            'student_id' => $student->id,
            'jenis_dokumen' => 'rapor',
            'file' => $file,
        ]);

        $document = Document::where('student_id', $student->id)->first();
        $this->assertNotNull($document);

        // Verify the file does not exist in the public web root directory
        $this->assertFalse(file_exists(public_path('storage/'.$document->file_path)));

        // Attempting to fetch directly without authorization returns either 404 or redirect to login (never 200)
        $publicUrl = '/storage/'.$document->file_path;
        $response = $this->get($publicUrl);
        $this->assertNotEquals(200, $response->getStatusCode());
    }

    public function test_authorized_admin_can_download_document(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create();
        $admin->assignRole('kepala-pkbm');

        $student = Student::factory()->create();
        $filePath = 'documents/sample_document.pdf';
        Storage::disk('local')->put($filePath, 'sample content 123');

        $document = Document::factory()->create([
            'student_id' => $student->id,
            'file_path' => $filePath,
            'nama_file' => 'sample_document.pdf',
        ]);

        $response = $this->actingAs($admin)->get(route('documents.download', $document));
        $response->assertStatus(200);
        $this->assertEquals('sample content 123', $response->streamedContent());
    }

    public function test_owner_student_can_download_own_document(): void
    {
        Storage::fake('local');

        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');

        $student = Student::factory()->create(['user_id' => $studentUser->id]);
        $filePath = 'documents/student_doc.pdf';
        Storage::disk('local')->put($filePath, 'student confidential doc');

        $document = Document::factory()->create([
            'student_id' => $student->id,
            'file_path' => $filePath,
            'nama_file' => 'student_doc.pdf',
        ]);

        $response = $this->actingAs($studentUser)->get(route('documents.download', $document));
        $response->assertStatus(200);
        $this->assertEquals('student confidential doc', $response->streamedContent());
    }

    public function test_unauthorized_student_cannot_download_another_students_document(): void
    {
        Storage::fake('local');

        // Student A
        $userA = User::factory()->create();
        $userA->assignRole('peserta-didik');
        $studentA = Student::factory()->create(['user_id' => $userA->id]);

        $filePath = 'documents/student_a_doc.pdf';
        Storage::disk('local')->put($filePath, 'student A confidential doc');

        $documentA = Document::factory()->create([
            'student_id' => $studentA->id,
            'file_path' => $filePath,
            'nama_file' => 'student_a_doc.pdf',
        ]);

        // Student B tries to download Student A's document
        /** @var User $userB */
        $userB = User::factory()->create();
        $userB->assignRole('peserta-didik');
        Student::factory()->create(['user_id' => $userB->id]);

        // Web request is intercepted by exception handler and redirected to student dashboard
        $response = $this->actingAs($userB)->get(route('documents.download', $documentA));
        $response->assertRedirect(route('dashboard.siswa'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');

        // API / JSON request gets 403 Forbidden
        $jsonResponse = $this->actingAs($userB)->getJson(route('documents.download', $documentA));
        $jsonResponse->assertStatus(403);
    }

    public function test_student_cannot_download_manual_institutional_document(): void
    {
        Storage::fake('local');

        /** @var User $userStudent */
        $userStudent = User::factory()->create();
        $userStudent->assignRole('peserta-didik');
        Student::factory()->create(['user_id' => $userStudent->id]);

        $filePath = 'documents/sk_rahasia.pdf';
        Storage::disk('local')->put($filePath, 'sk rahasia content');

        $manualDoc = Document::factory()->create([
            'student_id' => null,
            'ppdb_id' => null,
            'file_path' => $filePath,
            'nama_file' => 'sk_rahasia.pdf',
        ]);

        // Web request redirects to student dashboard with error
        $response = $this->actingAs($userStudent)->get(route('documents.download', $manualDoc));
        $response->assertRedirect(route('dashboard.siswa'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur tersebut.');

        // JSON request returns 403
        $jsonResponse = $this->actingAs($userStudent)->getJson(route('documents.download', $manualDoc));
        $jsonResponse->assertStatus(403);
    }

    public function test_unauthenticated_guest_is_redirected_from_download(): void
    {
        Storage::fake('local');

        $filePath = 'documents/guest_test.pdf';
        Storage::disk('local')->put($filePath, 'content');

        $document = Document::factory()->create(['file_path' => $filePath]);

        $response = $this->get(route('documents.download', $document));
        $response->assertRedirect(route('login'));
    }

    public function test_historical_document_on_public_disk_can_still_be_downloaded(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('kepala-pkbm');

        $student = Student::factory()->create();
        $filePath = 'documents/legacy_public_doc.pdf';

        // Stored only on public disk (pre-migration)
        Storage::disk('public')->put($filePath, 'legacy public content');

        $document = Document::factory()->create([
            'student_id' => $student->id,
            'file_path' => $filePath,
            'nama_file' => 'legacy_public_doc.pdf',
        ]);

        $this->assertEquals('public', $document->getStorageDisk());
        $this->assertTrue($document->fileExists());

        $response = $this->actingAs($admin)->get(route('documents.download', $document));
        $response->assertStatus(200);
        $this->assertEquals('legacy public content', $response->streamedContent());
    }

    public function test_destroy_deletes_file_from_local_and_public_disks(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('kepala-pkbm');

        $filePath = 'documents/to_delete.pdf';
        Storage::disk('local')->put($filePath, 'private content');
        Storage::disk('public')->put($filePath, 'leftover public content');

        $document = Document::factory()->create([
            'file_path' => $filePath,
        ]);

        $response = $this->actingAs($admin)->delete(route('documents.destroy', $document));
        $response->assertRedirect(route('documents.index'));

        Storage::disk('local')->assertMissing($filePath);
        Storage::disk('public')->assertMissing($filePath);
        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    public function test_secure_storage_command_migrates_public_files_with_hash_verification(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $filePath = 'documents/sample_legacy.pdf';
        $content = 'Sample confidential legacy document content';
        Storage::disk('public')->put($filePath, $content);

        Document::factory()->create([
            'file_path' => $filePath,
        ]);

        // Run command
        $this->artisan('documents:secure-storage')
            ->expectsOutputToContain('Starting document storage security migration')
            ->expectsOutputToContain('MIGRATED & UNLINKED FROM PUBLIC')
            ->assertSuccessful();

        // Local disk now has it
        Storage::disk('local')->assertExists($filePath);
        $this->assertEquals($content, Storage::disk('local')->get($filePath));

        // Public disk no longer has it
        Storage::disk('public')->assertMissing($filePath);
    }
}
