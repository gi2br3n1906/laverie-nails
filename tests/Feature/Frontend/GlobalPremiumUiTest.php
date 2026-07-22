<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalPremiumUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_and_auth_pages_share_the_premium_announcement_and_navbar(): void
    {
        foreach ([route('guidance'), route('measurements.create'), route('products.index'), route('login')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('data-homepage-announcement', false)
                ->assertSee('data-homepage-navbar', false)
                ->assertSee('Laverie Nails');
        }
    }

    public function test_authenticated_and_admin_pages_share_the_premium_chrome(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->withRole(UserRole::Admin)->create();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-homepage-announcement', false)
            ->assertSee('data-homepage-navbar', false);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-homepage-announcement', false)
            ->assertSee('data-homepage-navbar', false);
    }

    public function test_key_journey_pages_render_without_bright_pink_or_red_utility_classes(): void
    {
        foreach ([route('guidance'), route('measurements.create'), route('products.index'), route('login')] as $url) {
            $content = $this->get($url)->assertOk()->getContent();

            $this->assertDoesNotMatchRegularExpression('/(?:rose|pink|red)-(?:50|100|200|300|400|500|600|700|800|900|950)/', $content);
        }
    }

    public function test_key_pages_use_blue_white_tokens_without_true_black_primary_utilities(): void
    {
        foreach ([route('home'), route('guidance'), route('measurements.create'), route('products.index'), route('login')] as $url) {
            $content = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('#0C1C39', $content);
            $this->assertDoesNotMatchRegularExpression('/\b(?:bg|text|border)-black\b/', $content);
        }
    }

    public function test_login_card_does_not_repeat_the_laverie_brand_above_its_heading(): void
    {
        $content = $this->get(route('login'))->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression('/<header[^>]*>\s*<p[^>]*>Laverie Nails<\/p>\s*<h1[^>]*>Welcome back<\/h1>/s', $content);
    }
}
