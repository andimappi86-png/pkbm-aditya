<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Middleware\TrustProxies;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_security_headers_are_present_on_web_responses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com", $csp);
        $this->assertStringContainsString("style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com", $csp);
        $this->assertStringContainsString("font-src 'self' data: https://fonts.bunny.net https://fonts.gstatic.com", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("frame-src 'self' https://www.youtube.com", $csp);
        $this->assertStringNotContainsString("frame-src 'self' https://www.youtube.com https://youtube.com https://www.youtube-nocookie.com https:", $csp);
        $this->assertStringContainsString("media-src 'self'", $csp);
    }

    public function test_hsts_is_not_sent_on_local_or_insecure_requests(): void
    {
        // On default local/testing environment over HTTP, HSTS must never be sent
        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }

    public function test_hsts_is_sent_only_on_https_production_requests(): void
    {
        // In production over HTTPS
        $this->app['env'] = 'production';

        $response = $this->get('https://localhost/');

        $response->assertStatus(200);
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_https_redirect_enforced_when_force_https_enabled_in_production(): void
    {
        $this->app['env'] = 'production';
        config(['app.force_https' => true]);

        $response = $this->get('http://localhost/login');

        $response->assertRedirect('https://localhost/login');
    }

    public function test_untrusted_client_cannot_spoof_client_ip_or_https_proto(): void
    {
        // Untrusted proxy sends spoofed headers directly
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '198.51.100.10',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.195',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => 'malicious.com',
        ])->get('/');

        $response->assertStatus(200);

        // Access the current request instance from the container
        $request = app('request');

        // Client IP must be the socket address (198.51.100.10), NOT the spoofed X-Forwarded-For
        $this->assertEquals('198.51.100.10', $request->ip());

        // HTTPS proto must NOT be trusted from untrusted socket
        $this->assertFalse($request->isSecure());

        // Host must NOT be spoofed
        $this->assertNotEquals('malicious.com', $request->getHost());
    }

    public function test_trusted_proxy_correctly_honors_forwarded_headers_when_explicitly_configured(): void
    {
        $trustedProxyIp = '192.168.1.100';
        TrustProxies::at([$trustedProxyIp]);

        $response = $this->withServerVariables([
            'REMOTE_ADDR' => $trustedProxyIp,
            'HTTP_X_FORWARDED_FOR' => '203.0.113.195',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ])->get('/');

        $response->assertStatus(200);

        $request = app('request');
        $this->assertEquals('203.0.113.195', $request->ip());
        $this->assertTrue($request->isSecure());

        // Reset trusted proxies after test
        TrustProxies::at([]);
    }

    public function test_login_page_renders_with_security_headers(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_all_six_role_dashboards_render_with_security_headers(): void
    {
        $roles = [
            'operator',
            'kepala-pkbm',
            'ketua-yayasan',
            'wali-kelas',
            'tutor',
            'peserta-didik',
        ];

        foreach ($roles as $role) {
            /** @var User $user */
            $user = User::factory()->create();
            $user->assignRole($role);

            $dashboardRoute = $user->dashboardRoute();
            $response = $this->actingAs($user)->get(route($dashboardRoute));

            $response->assertStatus(200);
            $response->assertHeader('X-Content-Type-Options', 'nosniff');
            $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
            $this->assertNotNull($response->headers->get('Content-Security-Policy'));
        }
    }
}
