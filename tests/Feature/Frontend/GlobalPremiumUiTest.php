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
            $content = $this->get($url)
                ->assertOk()
                ->assertSee('data-homepage-announcement', false)
                ->assertSee('data-homepage-navbar', false)
                ->assertSee('data-overlay-navigation="false"', false)
                ->assertSee('Laverie Nails')
                ->getContent();

            preg_match('/<header[^>]*data-homepage-navbar[^>]*>/', $content, $navbar);

            $this->assertNotEmpty($navbar);
            $this->assertStringContainsString('sticky top-0 z-50', $navbar[0]);
            $this->assertStringContainsString('bg-white', $navbar[0]);
            $this->assertStringNotContainsString('border-b', $navbar[0]);
            $this->assertStringNotContainsString('shadow', $navbar[0]);
        }
    }

    public function test_only_homepage_uses_the_transparent_white_overlay_navigation(): void
    {
        $content = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('data-overlay-navigation="true"', $content);
        $this->assertMatchesRegularExpression('/<header[^>]*class="[^"]*sticky top-0 z-50[^"]*text-white[^"]*"[^>]*data-homepage-navbar/s', $content);
        $this->assertMatchesRegularExpression('/<header[^>]*class="[^"]*-mb-16[^"]*sm:-mb-20[^"]*"[^>]*data-homepage-navbar/s', $content);
        $this->assertMatchesRegularExpression('/<header[^>]*class="[^"]*bg-gradient-to-b[^"]*from-\[#0C1C39\]\/70[^"]*backdrop-blur-md[^"]*"[^>]*data-homepage-navbar/s', $content);
        $this->assertStringContainsString('data-navbar-blend', $content);
        $this->assertStringContainsString('data-navbar-scrolled="false"', $content);

        $navbarScript = file_get_contents(resource_path('js/storefront-navbar.js'));
        $this->assertIsString($navbarScript);
        $this->assertStringContainsString('window.scrollY > 24', $navbarScript);
        $this->assertStringContainsString('navbarScrolled', $navbarScript);
        $this->assertMatchesRegularExpression('/grid-cols-\[1fr_auto_1fr\]/', $content);
        $this->assertMatchesRegularExpression('/data-navbar-left.*?aria-label="Buka menu".*?data-navbar-brand/s', $content);
        $this->assertMatchesRegularExpression('/data-navbar-brand[^>]*>Laverie Nails<\/a>.*?data-navbar-right/s', $content);
        $this->assertMatchesRegularExpression('/font-logo[^>]*text-white[^>]*>Laverie Nails<\/a>/', $content);
        $this->assertMatchesRegularExpression('/<a[^>]*class="[^"]*text-white[^"]*"[^>]*aria-label="Cari produk"/', $content);
        $this->assertMatchesRegularExpression('/<a[^>]*class="[^"]*text-white[^"]*"[^>]*aria-label="Tas belanja"/', $content);
        $this->assertMatchesRegularExpression('/<summary[^>]*class="[^"]*text-white[^"]*"[^>]*aria-label="Buka menu"/', $content);
        $this->assertSame(3, preg_match_all('/<svg[^>]*class="[^"]*drop-shadow-lg[^"]*"[^>]*data-navbar-icon="(?:menu|search|cart)"/', $content));
        $this->assertStringContainsString('>Sizing</a>', $content);
        $this->assertStringNotContainsString('>Find your size</a>', $content);
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
