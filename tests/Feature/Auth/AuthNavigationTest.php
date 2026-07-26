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
}
