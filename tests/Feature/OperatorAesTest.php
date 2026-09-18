<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AesGcmService;
use Database\Seeders\DemoUsersSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorAesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(DemoUsersSeeder::class);
    }

    private function getOperator()
    {
        return User::where('email', 'operator@pkbm-aditya.test')->first();
    }

    public function test_operator_login_page_renders()
    {
        $response = $this->get(route('operator.login.form'));
        $response->assertStatus(200);
        $response->assertViewIs('operator.login');
    }

    public function test_operator_can_login()
    {
        $response = $this->post(route('operator.login'), [
            'email' => 'operator@pkbm-aditya.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('operator.dashboard'));
        $this->assertAuthenticatedAs($this->getOperator());
    }

    public function test_non_operator_cannot_login()
    {
        $siswa = User::where('email', 'siswa@pkbm-aditya.test')->first();

        $response = $this->post(route('operator.login'), [
            'email' => 'siswa@pkbm-aditya.test',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_operator_dashboard_renders()
    {
        $operator = $this->getOperator();
        $response = $this->actingAs($operator)->get(route('operator.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('operator.dashboard');
    }

    public function test_security_page_renders()
    {
        $operator = $this->getOperator();
        $response = $this->actingAs($operator)->get(route('operator.security'));

        $response->assertStatus(200);
        $response->assertViewIs('operator.security');
    }

    public function test_aes_encrypt_decrypt()
    {
        $service = app(AesGcmService::class);
        $plaintext = 'Secret Plaintext Data 123';

        $encrypted = $service->encrypt($plaintext);

        $this->assertIsString($encrypted);
        $this->assertNotEquals($plaintext, $encrypted);

        $decrypted = $service->decrypt($encrypted);
        $this->assertEquals($plaintext, $decrypted);
    }

    public function test_aes_decrypt_tampered_data_fails()
    {
        $service = app(AesGcmService::class);
        $plaintext = 'Secret Plaintext Data 123';

        $encrypted = $service->encrypt($plaintext);

        // Tamper payload (change base64 string)
        $tampered = substr_replace($encrypted, 'a', 10, 1);

        $this->expectException(\RuntimeException::class);
        $service->decrypt($tampered);
    }

    public function test_avalanche_calculation()
    {
        $service = app(AesGcmService::class);
        $plaintextA = 'Hello World';
        $plaintextB = 'Hello world';

        $result = $service->calculateAvalanche($plaintextA, $plaintextB);

        $this->assertArrayHasKey('total_bits', $result);
        $this->assertArrayHasKey('changed_bits', $result);
        $this->assertArrayHasKey('percentage', $result);
        $this->assertGreaterThan(0, $result['changed_bits']);
    }

    public function test_api_encrypt()
    {
        $operator = $this->getOperator();
        $response = $this->actingAs($operator)->postJson(route('operator.encrypt'), [
            'plaintext' => 'Test',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['encrypted', 'time_ms']);
    }
}
