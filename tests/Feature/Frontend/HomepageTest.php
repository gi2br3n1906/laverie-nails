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
                'FIND YOUR PERFECT FIT ⭐',
                'Laverie Nails',
                'Nail It, Fit It, Wear It',
                'temukan ukuranmu, tampil lebih maksimal',
                'OUR COLLECTION',
                'SIZING',
                'Featured Sets',
                'SALON QUALITY LOOKS',
                'ZERO NAIL DAMAGE',
                'REUSABLE',
                'AFFORDABLE',
                '100% HAND PAINTED',
                'Shop By Style',
                'Our Handpainted press on nails designed to match every mood, occasion, and style',
                'Speak to Us',
                'Real reviews from those who trust laverie for salon quality nails at home',
            ])
            ->assertSeeInOrder(['Classy', 'Coquette', 'Y2K', 'Floral', 'Grunge'])
            ->assertSee('images/hero-banner.png', false)
            ->assertSee('/images/hero-banner.png?v=', false)
            ->assertSee('data-overlay-navigation="true"', false)
            ->assertSee('data-homepage-hero-indicators', false)
            ->assertSee('data-homepage-hero-ctas', false)
            ->assertSee('data-homepage-featured-sets', false)
            ->assertSee('data-homepage-size-grid', false)
            ->assertSee('Verified')
            ->assertSee('data-homepage-announcement', false)
            ->assertSee('data-homepage-navbar', false)
            ->assertSee('data-homepage-hero', false)
            ->assertDontSee('Pretty Picks')
            ->assertDontSee('perfect fit, stunning nails');

        $content = $response->getContent();
        preg_match('/<section[^>]*data-homepage-hero[^>]*>(.*?)<\/section>/s', $content, $heroMatch);

        $this->assertNotEmpty($heroMatch);
        $this->assertStringContainsString('items-end', $heroMatch[0]);
        $this->assertStringContainsString('text-white', $heroMatch[0]);
        $this->assertStringContainsString('drop-shadow-lg', $heroMatch[0]);
        $this->assertStringContainsString('data-homepage-hero-indicators', $heroMatch[0]);
        $this->assertStringContainsString('data-homepage-hero-ctas', $heroMatch[0]);
        $this->assertSame(3, substr_count($heroMatch[0], 'rounded-full bg-white'));
        $this->assertStringNotContainsString('bg-white/65', $heroMatch[0]);
        $this->assertStringContainsString('>OUR COLLECTION<', $heroMatch[0]);
        $this->assertStringContainsString('>SIZING<', $heroMatch[0]);
        $this->assertStringContainsString(route('products.index'), $heroMatch[0]);
        $this->assertStringContainsString(route('measurements.create'), $heroMatch[0]);
        $this->assertSame(1, substr_count($content, '>SIZING<'));
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
            ->assertSee('Rp 179.000,00')
            ->assertSee('Rp 179.000')
            ->assertSee('5.0')
            ->assertDontSee('LAVERIE-HIDDEN-SET');
    }

    public function test_featured_sets_renders_five_square_centered_product_slots_without_duplicate_sizing_cta(): void
    {
        NailCatalog::factory()->count(5)->sequence(
            ['title' => 'Pure Angelic', 'price' => '160000'],
            ['title' => 'Blue Whisper', 'price' => '165000'],
            ['title' => 'Pearl Muse', 'price' => '170000'],
            ['title' => 'Soft Petal', 'price' => '175000'],
            ['title' => 'Midnight Dew', 'price' => '180000'],
        )->create();

        $content = $this->get(route('home'))->assertOk()->getContent();

        $this->assertSame(5, substr_count($content, 'data-homepage-size-product'));
        $this->assertStringContainsString('lg:grid-cols-5', $content);
        $this->assertStringContainsString('aspect-square', $content);
        $this->assertStringContainsString('text-center', $content);
        $this->assertStringContainsString('Pure Angelic', $content);
        $this->assertStringContainsString('Rp 160.000,00', $content);
        $this->assertStringContainsString('Featured Sets', $content);
        $this->assertStringNotContainsString('data-homepage-find-size', $content);
        $this->assertSame(1, substr_count($content, '>SIZING<'));
    }

    public function test_homepage_has_no_inline_styles_or_inline_scripts(): void
    {
        $content = $this->get(route('home'))->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression('/\sstyle\s*=/i', $content);
        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $content);
    }
}
