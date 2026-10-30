<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_pages_show_the_google_button(): void
    {
        $this->configureGoogle();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Continue with Google')
            ->assertSee(route('auth.google.redirect'), false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Sign up with Google')
            ->assertSee(route('auth.google.redirect'), false);
    }

    public function test_google_button_is_hidden_until_credentials_are_configured(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Continue with Google');
    }

    public function test_google_redirect_sends_the_user_to_google(): void
    {
        $this->fakeGoogleUser();

        $this->get(route('auth.google.redirect'))
            ->assertRedirect('https://socialite.fake/google/authorize');
    }

    public function test_google_redirect_returns_a_clear_error_when_not_configured(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $this->get(route('auth.google.redirect'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_first_google_sign_in_creates_a_customer_account_and_logs_in(): void
    {
        $this->fakeGoogleUser([
            'id' => 'google-customer-1',
            'name' => 'Google Customer',
            'email' => 'google.customer@example.com',
        ]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $user = User::query()->where('email', 'google.customer@example.com')->sole();

        $this->assertSame('Google Customer', $user->name);
        $this->assertSame('google', $user->provider);
        $this->assertSame('google-customer-1', $user->provider_id);
        $this->assertSame([UserRole::User->value], $user->roles);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_existing_email_account_is_linked_to_google_without_losing_the_password(): void
    {
        $user = User::factory()->create([
            'email' => 'existing.customer@example.com',
            'password' => 'old-password',
        ]);

        $this->fakeGoogleUser([
            'id' => 'google-existing-1',
            'email' => $user->email,
        ]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user->refresh());
        $this->assertSame('google', $user->provider);
        $this->assertSame('google-existing-1', $user->provider_id);
        $this->assertNotNull($user->password);
    }

    public function test_google_sign_in_rejects_an_unverified_google_email(): void
    {
        $this->fakeGoogleUser([
            'email' => 'unverified@example.com',
            'email_verified' => false,
        ]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'unverified@example.com']);
    }

    public function test_admin_accounts_cannot_sign_in_with_google(): void
    {
        $admin = User::factory()->withRole(UserRole::Admin)->create([
            'email' => 'admin@example.com',
        ]);

        $this->fakeGoogleUser([
            'id' => 'google-admin-1',
            'email' => $admin->email,
        ]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertNull($admin->refresh()->provider);
    }

    public function test_google_sign_in_does_not_silently_switch_an_existing_provider(): void
    {
        $user = User::factory()->create([
            'email' => 'linked@example.com',
            'provider' => 'github',
            'provider_id' => 'github-1',
        ]);

        $this->fakeGoogleUser([
            'id' => 'google-1',
            'email' => $user->email,
        ]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame('github', $user->refresh()->provider);
    }

    public function test_google_sign_in_claims_the_guest_cart(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $payload = ['size' => 'M', 'length' => 'Medium'];
        $guestId = 'google-guest-session';

        CartItem::query()->create([
            'user_id' => null,
            'session_id' => $guestId,
            'product_id' => $product->id,
            'quantity' => 1,
            'size_type' => 'standard',
            'size_payload' => $payload,
            'size_signature' => hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)),
        ]);

        $this->fakeGoogleUser([
            'id' => 'google-cart-1',
            'email' => 'google.cart@example.com',
        ]);

        $this->withSession(['cart.guest_id' => $guestId])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'google.cart@example.com')->sole();

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'product_id' => $product->id,
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function fakeGoogleUser(array $attributes = []): void
    {
        $this->configureGoogle();

        Socialite::fake('google', SocialiteUser::fake(array_merge([
            'id' => 'google-user-1',
            'name' => 'Google User',
            'email' => 'google.user@example.com',
            'email_verified' => true,
        ], $attributes)));
    }

    private function configureGoogle(): void
    {
        config([
            'services.google.client_id' => 'test-google-client-id',
            'services.google.client_secret' => 'test-google-client-secret',
        ]);
    }
}
