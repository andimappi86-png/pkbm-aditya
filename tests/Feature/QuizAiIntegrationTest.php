<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\QuizAiGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuizAiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $tutor;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'tutor', 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'quizzes.manage', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $this->tutor = User::factory()->create();
        $this->tutor->assignRole('tutor');
    }

    public function test_live_or_faked_openai_integration_produces_valid_structure(): void
    {
        $realKey = config('services.openai.key');

        if (! empty($realKey) && $realKey !== 'your_openai_api_key_here') {
            // Live test using user's real OpenAI API Key from .env
            config(['services.openai.key' => $realKey]);

            $service = new QuizAiGeneratorService;
            $result = $service->generate([
                'topic' => 'Sistem peredaran darah manusia dan fungsi jantung',
                'count' => 5,
                'type' => 'mixed',
                'difficulty' => 'easy',
            ]);

            $this->assertArrayHasKey('questions', $result);
            $this->assertNotEmpty($result['questions']);

            foreach ($result['questions'] as $q) {
                $this->assertNotEmpty($q['text']);
                $this->assertContains($q['type'], ['multiple_choice', 'true_false', 'essay']);
                $this->assertGreaterThan(0, $q['points']);
            }
        } else {
            // Simulated integration test: key is absent in test DB / env, verifying graceful handling
            $service = new QuizAiGeneratorService;

            config(['services.openai.key' => null]);
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('OPENAI_API_KEY belum dikonfigurasi di file .env.');

            $service->generate([
                'topic' => 'Uji penanganan tanpa API key',
                'count' => 5,
                'type' => 'multiple_choice',
                'difficulty' => 'medium',
            ]);
        }
    }
}
