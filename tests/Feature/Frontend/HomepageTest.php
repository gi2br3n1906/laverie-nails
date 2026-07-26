<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\CatalogReview;
use App\Models\Category;
use App\Models\HeroBanner;
use App\Models\NailCatalog;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
                'perfect fit, stunning nails',
                'OUR COLLECTION',
                'SIZING',
                'SALON QUALITY LOOKS',
                'ZERO NAIL DAMAGE',
                'REUSABLE',
                'AFFORDABLE',
                '100% HAND PAINTED',
                'Pretty Picks',
                'Shop by Style',
                'Our Collection',
                'Handpainted press on nails designed to match every mood, occasion, and style',
                'Speak to Us',
                'Real reviews from those who trust laverie for salon quality nails at home',
            ])
            ->assertSeeInOrder(['Classy', 'Coquette', 'Y2K', 'Floral', 'Grunge'])
            ->assertSee('images/hero-banner.png', false)
            ->assertSee('/images/hero-banner.png?v=', false)
            ->assertSee('data-overlay-navigation="true"', false)
            ->assertSee('data-homepage-navbar-contrast', false)
            ->assertSee('data-homepage-hero-indicators', false)
            ->assertSee('data-hero-carousel', false)
            ->assertSee('aria-roledescription="carousel"', false)
            ->assertSee('data-homepage-hero-ctas', false)
            ->assertSee('data-homepage-featured-sets', false)
            ->assertSee('data-homepage-size-grid', false)
            ->assertSee('Verified')
            ->assertSee('data-homepage-announcement', false)
            ->assertSee('data-homepage-navbar', false)
            ->assertSee('data-homepage-hero', false)
            ->assertDontSee('Featured Sets')
            ->assertDontSee('FIND YOUR SIGNATURE LOOK')
            ->assertDontSee('MADE FOR EVERY MOOD');

        $content = $response->getContent();
        preg_match('/<section[^>]*data-homepage-hero[^>]*>(.*?)<\/section>/s', $content, $heroMatch);

        $this->assertNotEmpty($heroMatch);
        $this->assertStringContainsString('items-end', $heroMatch[0]);
        $this->assertStringContainsString('text-white', $heroMatch[0]);
        $this->assertStringContainsString('drop-shadow-lg', $heroMatch[0]);
        $this->assertStringContainsString('pointer-events-none absolute inset-x-0 top-0 -z-10 h-32 bg-gradient-to-b from-[#0C1C39]/50 to-transparent', $heroMatch[0]);
        $this->assertStringContainsString('data-homepage-navbar-contrast', $heroMatch[0]);
        $this->assertStringContainsString('data-homepage-hero-indicators', $heroMatch[0]);
        $this->assertStringContainsString('data-homepage-hero-ctas', $heroMatch[0]);
        $this->assertSame(1, preg_match_all('/\sdata-hero-slide(?:\s|>)/', $heroMatch[0]));
        $this->assertSame(1, substr_count($heroMatch[0], 'data-hero-indicator='));
        $this->assertStringContainsString('aria-current="true"', $heroMatch[0]);
        $this->assertStringNotContainsString('bg-white/65', $heroMatch[0]);
        $this->assertStringContainsString('>OUR COLLECTION<', $heroMatch[0]);
        $this->assertStringContainsString('>SIZING<', $heroMatch[0]);
        $this->assertStringContainsString('whitespace-nowrap', $heroMatch[0]);
        $this->assertStringContainsString('-mt-2 text-sm', $heroMatch[0]);
        $this->assertStringContainsString('sm:text-base', $heroMatch[0]);
        $this->assertStringContainsString(route('products.index'), $heroMatch[0]);
        $this->assertStringContainsString(route('measurements.create'), $heroMatch[0]);
        $this->assertSame(1, substr_count($content, '>SIZING<'));
        $this->assertStringContainsString('tracking-wide', $content);
        $this->assertStringNotContainsString('Shop By Style', $content);
        $this->assertDoesNotMatchRegularExpression('/<section[^>]*class="[^"]*border-(?:t|y)[^"]*"[^>]*aria-labelledby="shape-heading"/', $content);
        $this->assertMatchesRegularExpression('/<section[^>]*class="[^"]*bg-\[#0C1C39\][^"]*text-white[^"]*"[^>]*data-homepage-benefits>/', $content);

        $carouselScript = file_get_contents(resource_path('js/hero-carousel.js'));
        $this->assertIsString($carouselScript);
        $this->assertStringContainsString("indicator.addEventListener('click'", $carouselScript);
        $this->assertStringContainsString('window.setInterval', $carouselScript);
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $carouselScript);
    }

    public function test_homepage_footer_contains_customer_service_social_and_newsletter_content(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('CUSTOMER SERVICE')
            ->assertSee('data-global-footer-layout', false)
            ->assertSee('data-footer-top-row', false)
            ->assertSee('data-footer-bottom-row', false)
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

        $content = $this->get(route('home'))->getContent();
        preg_match('/<div[^>]*data-footer-bottom-row[^>]*>/', $content, $footerBottomRow);

        $this->assertNotEmpty($footerBottomRow);
        $this->assertStringNotContainsString('border-t', $footerBottomRow[0]);
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
        $this->assertStringContainsString('Pretty Picks', $content);
        $this->assertStringNotContainsString('data-homepage-find-size', $content);
        $this->assertSame(1, substr_count($content, '>SIZING<'));
    }

    public function test_homepage_has_no_inline_styles_or_inline_scripts(): void
    {
        $content = $this->get(route('home'))->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression('/\sstyle\s*=/i', $content);
        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $content);
    }

    public function test_homepage_uses_only_active_database_banners_in_sequence_order(): void
    {
        Storage::fake('public');
        HeroBanner::factory()->create(['image_path' => 'banners/second.jpg', 'sequence' => 20]);
        HeroBanner::factory()->create(['image_path' => 'banners/first.jpg', 'sequence' => 10]);
        HeroBanner::factory()->inactive()->create(['image_path' => 'banners/hidden.jpg', 'sequence' => 1]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                Storage::disk('public')->url('banners/first.jpg'),
                Storage::disk('public')->url('banners/second.jpg'),
            ], false)
            ->assertDontSee('banners/hidden.jpg', false)
            ->assertDontSee('/images/hero-banner.png?v=', false);
    }

    public function test_homepage_falls_back_to_the_static_banner_when_no_active_banner_exists(): void
    {
        HeroBanner::factory()->inactive()->create();

        $response = $this->get(route('home'))
            ->assertOk()
            ->assertSee('/images/hero-banner.png?v=', false);

        $this->assertSame(1, preg_match_all('/\sdata-hero-slide(?:\s|>)/', $response->getContent()));
        $this->assertSame(1, substr_count($response->getContent(), 'data-hero-indicator='));
    }

    public function test_shop_by_style_contains_only_categories_with_active_products(): void
    {
        $visible = Category::factory()->create(['name' => 'Editorial Classy']);
        Product::factory()->for($visible)->create();
        $inactiveOnly = Category::factory()->create(['name' => 'Hidden Coquette']);
        Product::factory()->for($inactiveOnly)->inactive()->create();
        Category::factory()->create(['name' => 'Empty Y2K']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Editorial Classy')
            ->assertDontSee('Hidden Coquette')
            ->assertDontSee('Empty Y2K');
    }

    public function test_our_collection_uses_latest_active_products_and_supports_missing_image_fallback(): void
    {
        Storage::fake('public');
        $older = Product::factory()->create(['name' => 'Editorial Older', 'price' => '125000']);
        ProductImage::factory()->for($older)->primary()->create(['image_path' => 'products/older.jpg']);
        $latest = Product::factory()->create(['name' => 'Editorial Latest', 'price' => '195000']);
        Product::factory()->inactive()->create(['name' => 'Editorial Hidden']);

        $content = $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['Editorial Latest', 'Editorial Older'])
            ->assertSee('Rp 195.000')
            ->assertSee('data-product-image-fallback', false)
            ->assertDontSee('Editorial Hidden')
            ->getContent();

        $this->assertSame(2, substr_count($content, 'data-homepage-product-card'));
    }
}
