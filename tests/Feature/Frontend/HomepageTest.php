<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\CatalogReview;
use App\Models\NailCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_premium_storefront_sections_in_sequence(): void
    {
        $catalog = NailCatalog::factory()->create();
        CatalogReview::factory()->for($catalog, 'catalog')->create([
            'comment' => 'The fit feels effortless and elegant.',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSeeInOrder([
                'CLEAN NAILS',
                'Laverie Nails',
                'Nail It, Fit It, Wear It',
                'perfect fit, stunning nails',
                'SALON QUALITY LOOKS',
                'ZERO NAIL DAMAGE',
                'REUSABLE',
                'AFFORDABLE',
                '100% HAND PAINTED',
                'Pretty Picks',
                'Shop By Style',
                'Our Handpainted press on nails designed to match every mood, occasion, and style',
                'Speak to Us',
                'Real reviews from those who trust laverie for salon quality nails at home',
            ])
            ->assertSeeInOrder(['Classy', 'Coquette', 'Y2K', 'Floral', 'Grunge'])
            ->assertSee('images/hero-banner.jpg', false)
            ->assertSee('our collection')
            ->assertSee('sizing')
            ->assertSee('Verified')
            ->assertSee('data-homepage-announcement', false)
            ->assertSee('data-homepage-navbar', false)
            ->assertSee('data-homepage-hero', false);
    }

    public function test_homepage_footer_contains_customer_service_social_and_newsletter_content(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Customer Service')
            ->assertSee('Sizing')
            ->assertSee('Measurement guide')
            ->assertSee('NEWSLETTER')
            ->assertSee('Sign up to save measurement history and get 10% off for your first order.')
            ->assertSee('placeholder="sign up"', false)
            ->assertSee('aria-label="WhatsApp"', false)
            ->assertSee('aria-label="Instagram"', false)
            ->assertSee('aria-label="TikTok"', false)
            ->assertDontSee('>Discover<', false)
            ->assertDontSee('>Account<', false);
    }

    public function test_homepage_displays_only_active_products_with_rating_and_price(): void
    {
        $active = NailCatalog::factory()->create([
            'title' => 'LAVERIE-ACTIVE-SET',
            'price' => '179000',
            'size' => 'M',
        ]);
        NailCatalog::factory()->inactive()->create(['title' => 'LAVERIE-HIDDEN-SET']);
        CatalogReview::factory()->for($active, 'catalog')->create(['rating' => 5]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('LAVERIE-ACTIVE-SET')
            ->assertSee('Rp 179.000')
            ->assertSee('5.0')
            ->assertDontSee('LAVERIE-HIDDEN-SET');
    }

    public function test_homepage_has_no_inline_styles_or_inline_scripts(): void
    {
        $content = $this->get(route('home'))->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression('/\sstyle\s*=/i', $content);
        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $content);
    }
}
