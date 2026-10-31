<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\QuizAiGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuizAiGenerationTest extends TestCase
{
    use RefreshDatabase;

    private User $tutor;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles & permissions
        $role = Role::firstOrCreate(['name' => 'tutor', 'guard_name' => 'web']);
        $perm = Permission::firstOrCreate(['name' => 'quizzes.manage', 'guard_name' => 'web']);
        $role->givePermissionTo($perm);

        $this->tutor = User::factory()->create();
        $this->tutor->assignRole('tutor');
    }

    // ─── Service unit tests ───────────────────────────────────────────

    public function test_service_throws_when_api_key_missing(): void
    {
        config(['services.openai.key' => null]);
        $service = new QuizAiGeneratorService;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('OPENAI_API_KEY belum dikonfigurasi');

        $service->generate([
            'topic' => 'Persamaan linear satu variabel dalam aljabar dasar',
            'count' => 5,
            'type' => 'multiple_choice',
            'difficulty' => 'medium',
        ]);
    }

    public function test_service_returns_questions_on_successful_api_response(): void
    {
        config(['services.openai.key' => 'sk-test-fake-key']);

        $fakePayload = [
            'questions' => [
                [
                    'type' => 'multiple_choice',
                    'text' => 'Berapakah nilai x jika 2x = 10?',
                    'points' => 10,
                    'options' => ['3', '4', '5', '6'],
                    'correctIndex' => 2,
                    'correctAnswer' => null,
                    'explanation' => 'Karena 2 × 5 = 10.',
                ],
            ],
        ];

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => json_encode($fakePayload)]],
                ],
            ], 200),
        ]);

        $service = new QuizAiGeneratorService;
        $result = $service->generate([
            'topic' => 'Persamaan linear satu variabel dalam aljabar dasar',
            'count' => 5,
            'type' => 'multiple_choice',
            'difficulty' => 'medium',
        ]);

        $this->assertArrayHasKey('questions', $result);
        $this->assertCount(1, $result['questions']);
        $this->assertSame('Berapakah nilai x jika 2x = 10?', $result['questions'][0]['text']);
    }

    public function test_service_throws_on_api_error_response(): void
    {
        config(['services.openai.key' => 'sk-test-fake-key']);

        Http::fake([
            'api.openai.com/*' => Http::response(
                ['error' => ['message' => 'Insufficient quota.']],
                429
            ),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Insufficient quota.');

        (new QuizAiGeneratorService)->generate([
            'topic' => 'Persamaan linear satu variabel',
            'count' => 5,
            'type' => 'multiple_choice',
            'difficulty' => 'medium',
        ]);
    }

    // ─── HTTP endpoint tests ──────────────────────────────────────────

    public function test_ai_generate_endpoint_requires_authentication(): void
    {
        $response = $this->postJson(route('quiz.ai.generate'), [
            'topic' => 'Matematika dasar',
            'count' => 5,
            'type' => 'multiple_choice',
            'difficulty' => 'medium',
        ]);

        $response->assertStatus(401);
    }

    public function test_ai_generate_endpoint_validates_input(): void
    {
        $response = $this->actingAs($this->tutor)
            ->postJson(route('quiz.ai.generate'), [
                'topic' => 'ab', // terlalu pendek
                'count' => 3,   // tidak valid (harus 5,10,15,20)
                'type' => 'unknown_type', // tidak valid
                'difficulty' => 'extreme', // tidak valid
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['topic', 'count', 'type', 'difficulty']);
    }

    public function test_ai_generate_endpoint_returns_questions_on_success(): void
    {
        config(['services.openai.key' => 'sk-test-fake-key']);

        $fakePayload = [
            'questions' => array_map(fn ($i) => [
                'type' => 'multiple_choice',
                'text' => "Soal nomor {$i}",
                'points' => 10,
                'options' => ['A', 'B', 'C', 'D'],
                'correctIndex' => 0,
                'correctAnswer' => null,
                'explanation' => 'Penjelasan singkat.',
            ], range(1, 5)),
        ];

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => json_encode($fakePayload)]],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->tutor)
            ->postJson(route('quiz.ai.generate'), [
                'topic' => 'Persamaan linear satu variabel dalam matematika SMP',
                'count' => 5,
                'type' => 'multiple_choice',
                'difficulty' => 'medium',
            ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(5, 'questions');
    }

    public function test_ai_generate_endpoint_supports_essay_and_mixed_types(): void
    {
        config(['services.openai.key' => '«redacted:sk-…»']);

        $fakePayload = [
            'questions' => [
                [
                    'type' => 'essay',
                    'text' => 'Jelaskan perbedaan fotosintesis dan kemosintesis!',
                    'points' => 10,
                    'options' => [],
                    'correctIndex' => null,
                    'correctAnswer' => 'Fotosintesis menggunakan cahaya, kemosintesis menggunakan energi kimiawi.',
                    'explanation' => 'Kunci konsep ada pada sumber energi.',
                    'rubric' => 'Poin konsep energi (5), sumber bahan (5).',
                ],
                [
                    'type' => 'multiple_choice',
                    'text' => 'Organel tempat terjadinya fotosintesis adalah...',
                    'points' => 10,
                    'options' => ['Kloroplas', 'Mitokondria', 'Ribosom', 'Nukleus'],
                    'correctIndex' => 0,
                    'correctAnswer' => null,
                    'explanation' => 'Kloroplas mengandung klorofil.',
                    'rubric' => null,
                ],
                [
                    'type' => 'true_false',
                    'text' => 'Reaksi terang fotosintesis terjadi di stroma.',
                    'points' => 10,
                    'options' => ['Benar', 'Salah'],
                    'correctIndex' => 1,
                    'correctAnswer' => null,
                    'explanation' => 'Reaksi terang terjadi di tilakoid.',
                    'rubric' => null,
                ],
            ],
        ];

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => json_encode($fakePayload)]],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->tutor)
            ->postJson(route('quiz.ai.generate'), [
                'topic' => 'Metabolisme dan proses fotosintesis pada tumbuhan hijau',
                'count' => 5,
                'type' => 'mixed',
                'difficulty' => 'hard',
            ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'questions')
            ->assertJsonPath('questions.0.type', 'essay')
            ->assertJsonPath('questions.0.rubric', 'Poin konsep energi (5), sumber bahan (5).')
            ->assertJsonPath('questions.1.type', 'multiple_choice')
            ->assertJsonPath('questions.2.type', 'true_false');
    }

    public function test_ai_generate_endpoint_sends_correct_request_to_openai(): void
    {
        config(['services.openai.key' => '«redacted:sk-…»']);

        $fakePayload = [
            'questions' => [
                [
                    'type' => 'true_false',
                    'text' => 'Hutan hujan tropis memiliki kanopi yang rapat.',
                    'points' => 10,
                    'options' => ['Benar', 'Salah'],
                    'correctIndex' => 0,
                    'explanation' => 'Benar karena tutupan pohon tinggi.',
                ],
            ],
        ];

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => json_encode($fakePayload)]]],
            ], 200),
        ]);

        $this->actingAs($this->tutor)
            ->postJson(route('quiz.ai.generate'), [
                'topic' => 'Ekosistem hutan hujan tropis dan rantai makanan',
                'count' => 10,
                'type' => 'true_false',
                'difficulty' => 'hard',
            ]);

        Http::assertSent(function (ClientRequest $request) {
            $body = $request->data();

            return str_contains($request->url(), 'openai.com') &&
                str_contains($body['messages'][1]['content'] ?? '', 'true_false') &&
                str_contains($body['messages'][1]['content'] ?? '', 'HOTS');
        });
    }
}
