<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Material;
use App\Models\PenugasanMengajar;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaterialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'kepala-pkbm']);
        Role::firstOrCreate(['name' => 'tutor']);
        Role::firstOrCreate(['name' => 'peserta-didik']);
        Role::firstOrCreate(['name' => 'wali-kelas']);

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_tutor_can_create_material_for_their_penugasan(): void
    {
        Storage::fake('public');

        $tutorUser = User::factory()->create();
        $tutorUser->assignRole('tutor');
        $tutor = Tutor::factory()->create(['user_id' => $tutorUser->id]);

        $penugasan = PenugasanMengajar::factory()->create(['tutor_id' => $tutor->id]);

        $response = $this->actingAs($tutorUser)->post(route('materi.store'), [
            'penugasan_mengajar_id' => $penugasan->id,
            'judul' => 'Materi Tes',
            'tipe' => 'pdf',
            'file_path' => UploadedFile::fake()->create('dokumen.pdf', 100),
            'status_aktif' => 1,
        ]);

        $response->assertRedirect(route('materi.index'));
        $this->assertDatabaseHas('materials', [
            'penugasan_mengajar_id' => $penugasan->id,
            'judul' => 'Materi Tes',
        ]);

        $material = Material::where('judul', 'Materi Tes')->first();
        Storage::disk('public')->assertExists($material->file_path);
    }

    public function test_peserta_didik_can_view_materials_of_their_classroom(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        $classroom = Classroom::factory()->create();
        Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $classroom->id,
        ]);

        $penugasan = PenugasanMengajar::factory()->create(['classroom_id' => $classroom->id]);
        $material = Material::factory()->create(['penugasan_mengajar_id' => $penugasan->id]);

        $response = $this->actingAs($studentUser)->get(route('materi.index'));

        $response->assertStatus(200);
        $response->assertSee($material->judul);
    }

    public function test_peserta_didik_cannot_view_materials_of_other_classroom(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        $myClassroom = Classroom::factory()->create();
        $otherClassroom = Classroom::factory()->create();

        Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $myClassroom->id,
        ]);

        $penugasan = PenugasanMengajar::factory()->create(['classroom_id' => $otherClassroom->id]);
        $material = Material::factory()->create(['penugasan_mengajar_id' => $penugasan->id]);

        $response = $this->actingAs($studentUser)->get(route('materi.index'));

        $response->assertStatus(200);
        $response->assertDontSee($material->judul);
    }

    public function test_tutor_can_delete_their_material(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('dokumen.pdf', 100);
        $path = $file->store('materials', 'public');

        $tutorUser = User::factory()->create();
        $tutorUser->assignRole('tutor');
        $tutor = Tutor::factory()->create(['user_id' => $tutorUser->id]);

        $penugasan = PenugasanMengajar::factory()->create(['tutor_id' => $tutor->id]);
        $material = Material::factory()->create([
            'penugasan_mengajar_id' => $penugasan->id,
            'file_path' => $path,
        ]);

        $response = $this->actingAs($tutorUser)->delete(route('materi.destroy', $material));

        $response->assertRedirect(route('materi.index'));
        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
        Storage::disk('public')->assertMissing($path);
    }
}
