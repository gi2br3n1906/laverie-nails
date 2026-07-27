<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_navbar_shows_guest_login_and_authenticated_account_actions(): void
    {
        $this->get('/')->assertOk()->assertSeeText('Login');

        $user = User::factory()->create(['name' => 'Alya Account']);
        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSeeText('Akun')
            ->assertSeeText('Dashboard')
            ->assertSeeText('Pengaturan Akun')
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('method="POST"', false)
            ->assertSeeText('Logout');
    }

    public function test_authenticated_user_can_log_out_through_post_route(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')
            ->assertRedirect(route('login'));
        $this->assertGuest();

        $this->get('/logout')->assertMethodNotAllowed();
    }

    public function test_account_navigation_lives_only_in_the_hamburger_menu(): void
    {
        $source = file_get_contents(resource_path('views/components/storefront/navbar.blade.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString('data-hamburger-account-menu', $source);
        $this->assertStringNotContainsString('data-desktop-account-menu', $source);
        $this->assertStringContainsString('data-navbar-blend', $source);
        $this->assertStringContainsString('backdrop-blur-md', $source);
        $this->assertSame(1, substr_count($source, "route('history.index')"));
        $this->assertSame(1, substr_count($source, "route('logout')"));
        $this->assertSame(1, substr_count($source, '@csrf'));
    }

    public function test_hamburger_dashboard_link_is_role_aware(): void
    {
        $admin = User::factory()->create(['roles' => ['admin']]);
        $adminResponse = $this->actingAs($admin)->get('/');
        $adminResponse->assertOk();
        $this->assertSame(1, substr_count($adminResponse->getContent(), 'href="'.route('admin.orders.index').'"'));

        $customer = User::factory()->create();
        $customerResponse = $this->actingAs($customer)->get('/');
        $customerResponse->assertOk();
        $this->assertSame(1, substr_count($customerResponse->getContent(), 'href="'.route('dashboard').'"'));
    }
}
