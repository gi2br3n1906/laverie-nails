<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\CloudflareAccessTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CloudflareAccessSsoTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_cloudflare_access_token_logs_an_admin_into_laravel(): void
    {
        $admin = User::factory()->create([
            'email' => 'laverienails@gmail.com',
            'roles' => ['admin'],
        ]);
        $this->configureCloudflareAccess();

        $this->withHeaders(['Cf-Access-Jwt-Assertion' => $this->fixture('adminToken')])
            ->get('/admin/orders')
            ->assertOk();

        $this->assertAuthenticatedAs($admin);
    }

    public function test_cloudflare_authorization_cookie_can_bootstrap_an_admin_session(): void
    {
        $admin = User::factory()->create([
            'email' => 'laverienails@gmail.com',
            'roles' => ['admin'],
        ]);
        $this->configureCloudflareAccess();

        $this->withUnencryptedCookie('CF_Authorization', $this->fixture('adminToken'))
            ->get('/admin/orders')
            ->assertOk();

        $this->assertAuthenticatedAs($admin);
    }

    public function test_cloudflare_access_token_for_a_non_admin_is_forbidden(): void
    {
        User::factory()->create(['email' => 'customer@example.com']);
        $this->configureCloudflareAccess();

        $this->withHeaders(['Cf-Access-Jwt-Assertion' => $this->fixture('customerToken')])
            ->get('/admin/orders')
            ->assertForbidden();

        $this->assertGuest();
    }

    public function test_invalid_expired_or_wrong_audience_access_tokens_are_forbidden(): void
    {
        User::factory()->create([
            'email' => 'laverienails@gmail.com',
            'roles' => ['admin'],
        ]);
        $this->configureCloudflareAccess();

        foreach (['invalid-token', $this->fixture('expiredToken'), $this->fixture('wrongAudienceToken')] as $token) {
            $this->withHeaders(['Cf-Access-Jwt-Assertion' => $token])
                ->get('/admin/orders')
                ->assertForbidden();
        }

        $this->assertGuest();
    }

    public function test_missing_cloudflare_access_assertion_keeps_the_login_fallback(): void
    {
        $this->get('/admin/orders')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_configured_access_allows_an_existing_admin_session_without_the_assertion(): void
    {
        $admin = User::factory()->create(['roles' => ['admin']]);
        config([
            'services.cloudflare_access.team_domain' => 'https://gibsteam.cloudflareaccess.com',
            'services.cloudflare_access.audience' => 'test-audience',
        ]);

        $this->actingAs($admin)
            ->get('/admin/orders')
            ->assertOk();
    }

    public function test_existing_admin_session_ignores_a_stale_access_cookie(): void
    {
        $admin = User::factory()->create(['roles' => ['admin']]);
        $this->configureCloudflareAccess();

        $this->actingAs($admin)
            ->withUnencryptedCookie('CF_Authorization', 'stale-token')
            ->get('/admin/orders')
            ->assertOk();
    }

    public function test_access_identity_must_match_an_existing_admin_session(): void
    {
        $admin = User::factory()->create([
            'email' => 'laverienails@gmail.com',
            'roles' => ['admin'],
        ]);
        $otherAdmin = User::factory()->create([
            'email' => 'other-admin@example.com',
            'roles' => ['admin'],
        ]);
        config([
            'services.cloudflare_access.team_domain' => 'https://gibsteam.cloudflareaccess.com',
            'services.cloudflare_access.audience' => 'test-audience',
        ]);
        $this->app->bind(CloudflareAccessTokenService::class, function () use ($otherAdmin): CloudflareAccessTokenService {
            return new class($otherAdmin->email) extends CloudflareAccessTokenService
            {
                public function __construct(private readonly string $email) {}

                public function email(string $token): ?string
                {
                    return $this->email;
                }
            };
        });

        $this->actingAs($admin)
            ->withHeaders(['Cf-Access-Jwt-Assertion' => 'valid-token'])
            ->get('/admin/orders')
            ->assertForbidden();
    }

    private function configureCloudflareAccess(): void
    {
        config([
            'services.cloudflare_access.team_domain' => 'https://gibsteam.cloudflareaccess.com',
            'services.cloudflare_access.audience' => 'test-audience',
        ]);

        Cache::flush();

        Http::fake([
            'https://gibsteam.cloudflareaccess.com/cdn-cgi/access/certs' => Http::response([
                'public_certs' => [[
                    'kid' => 'fixture-key',
                    'cert' => $this->fixture('publicKey'),
                ]],
            ]),
        ]);
    }

    private function fixture(string $key): string
    {
        $fixtures = require __DIR__.'/../../Fixtures/cloudflare-access-fixture.php';

        return $fixtures[$key];
    }
}
