<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Forum;
use App\Models\PenugasanMengajar;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ForumTest extends TestCase
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

    public function test_tutor_can_create_forum_for_their_penugasan(): void
    {
        $tutorUser = User::factory()->create();
        $tutorUser->assignRole('tutor');
        $tutor = Tutor::factory()->create(['user_id' => $tutorUser->id]);

        $penugasan = PenugasanMengajar::factory()->create(['tutor_id' => $tutor->id]);

        $response = $this->actingAs($tutorUser)->post(route('forum.store'), [
            'penugasan_mengajar_id' => $penugasan->id,
            'judul' => 'Diskusi Bab 1',
            'isi' => 'Isi diskusi tes',
            'status_aktif' => 1,
        ]);

        $response->assertRedirect(route('forum.index'));
        $this->assertDatabaseHas('forums', [
            'penugasan_mengajar_id' => $penugasan->id,
            'judul' => 'Diskusi Bab 1',
        ]);
    }

    public function test_peserta_didik_can_view_forums_of_their_classroom(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        $classroom = Classroom::factory()->create();
        Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $classroom->id,
        ]);

        $penugasan = PenugasanMengajar::factory()->create(['classroom_id' => $classroom->id]);
        $forum = Forum::factory()->create(['penugasan_mengajar_id' => $penugasan->id]);

        $response = $this->actingAs($studentUser)->get(route('forum.index'));

        $response->assertStatus(200);
        $response->assertSee($forum->judul);
    }

    public function test_peserta_didik_cannot_view_forums_of_other_classroom(): void
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
        $forum = Forum::factory()->create(['penugasan_mengajar_id' => $penugasan->id]);

        $response = $this->actingAs($studentUser)->get(route('forum.index'));

        $response->assertStatus(200);
        $response->assertDontSee($forum->judul);
    }

    public function test_peserta_didik_can_comment_on_forum(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('peserta-didik');
        $classroom = Classroom::factory()->create();
        Student::factory()->create([
            'user_id' => $studentUser->id,
            'rombel_id' => $classroom->id,
        ]);

        $penugasan = PenugasanMengajar::factory()->create(['classroom_id' => $classroom->id]);
        $forum = Forum::factory()->create(['penugasan_mengajar_id' => $penugasan->id]);

        $response = $this->actingAs($studentUser)->post(route('forum.comments.store', $forum), [
            'isi' => 'Ini komentar dari siswa',
        ]);

        $response->assertRedirect(route('forum.show', $forum));
        $this->assertDatabaseHas('forum_comments', [
            'forum_id' => $forum->id,
            'user_id' => $studentUser->id,
            'isi' => 'Ini komentar dari siswa',
        ]);
    }

    public function test_tutor_can_delete_their_forum(): void
    {
        $tutorUser = User::factory()->create();
        $tutorUser->assignRole('tutor');
        $tutor = Tutor::factory()->create(['user_id' => $tutorUser->id]);

        $penugasan = PenugasanMengajar::factory()->create(['tutor_id' => $tutor->id]);
        $forum = Forum::factory()->create([
            'penugasan_mengajar_id' => $penugasan->id,
            'user_id' => $tutorUser->id,
        ]);

        $response = $this->actingAs($tutorUser)->delete(route('forum.destroy', $forum));

        $response->assertRedirect(route('forum.index'));
        $this->assertDatabaseMissing('forums', ['id' => $forum->id]);
    }
}
