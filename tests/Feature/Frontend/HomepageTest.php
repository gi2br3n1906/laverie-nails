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
                'Find your perfect fit',
                'Zero nail damage',
                'Best Sellers',
                'Shop By Shape',
                'The Laverie Collections',
                'Loved by the Laverie community',
            ])
            ->assertSee('Almond')
            ->assertSee('Coffin')
            ->assertSee('Oval')
            ->assertSee('Squoval')
            ->assertSee('Square')
            ->assertSee('Verified')
            ->assertSee('data-homepage-announcement', false)
            ->assertSee('data-homepage-navbar', false)
            ->assertSee('data-homepage-hero', false);
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
