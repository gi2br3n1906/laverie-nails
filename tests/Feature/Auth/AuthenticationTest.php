<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => 'secure-password',
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'secure-password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_is_redirected_to_order_management_after_login(): void
    {
        $admin = User::factory()->create([
            'roles' => ['admin'],
            'password' => 'secure-password',
        ]);

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'secure-password',
        ])->assertRedirect(route('admin.orders.index'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_users_cannot_authenticate_with_an_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => 'secure-password',
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_logout_with_cloudflare_access_cleans_access_session_then_returns_home(): void
    {
        $user = User::factory()->create();

        config(['services.cloudflare_access.team_domain' => 'https://team.cloudflareaccess.com']);

        $this->actingAs($user)
            ->withUnencryptedCookie('CF_Authorization', 'access-token')
            ->post(route('logout'))
            ->assertOk()
            ->assertSee("fetch('/cdn-cgi/access/logout', { credentials: 'include' })", false)
            ->assertSee(json_encode('https://team.cloudflareaccess.com/cdn-cgi/access/logout'), false)
            ->assertSee('mode: \'no-cors\'', false)
            ->assertSee('window.setTimeout(resolve, 400)', false)
            ->assertSee('window.location.replace('.json_encode(url('/')), false)
            ->assertSee('http-equiv="refresh" content="1;url='.url('/').'"', false)
            ->assertSee('href="'.url('/').'"', false);

        $this->assertGuest();
    }

    public function test_logout_with_cloudflare_headers_also_renders_cleanup_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeaders(['Cf-Access-Authenticated-User-Email' => 'admin@example.com'])
            ->post(route('logout'))
            ->assertOk()
            ->assertSee('/cdn-cgi/access/logout', false)
            ->assertSee('window.location.replace('.json_encode(url('/')), false);
    }
}
