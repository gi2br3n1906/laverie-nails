<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Models\Product;
use Database\Seeders\SizeStandardSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_marketplace_exposes_only_active_laverie_catalogs(): void
    {
        Product::factory()->create(['name' => 'PUBLIC-LAVERIE-CATALOG']);
        Product::factory()->inactive()->create(['name' => 'INACTIVE-CATALOG']);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Our Collection')
            ->assertSee('Handpainted press on nails designed to match every mood, occasion, and style')
            ->assertSee('>All<', false)
            ->assertDontSee('Official Laverie collection')
            ->assertDontSee('Koleksi Press-On Nails')
            ->assertSee('PUBLIC-LAVERIE-CATALOG')
            ->assertDontSee('INACTIVE-CATALOG');
    }

    public function test_size_query_filters_exactly_to_the_requested_canonical_size(): void
    {
        Product::factory()->create(['name' => 'SIZE-M', 'available_sizes' => ['M', 'L']]);
        Product::factory()->create(['name' => 'SIZE-S', 'available_sizes' => ['S']]);

        $this->get(route('products.index', ['size' => 'M']))
            ->assertOk()
            ->assertSee('SIZE-M')
            ->assertDontSee('SIZE-S');

        $this->get(route('products.index', ['size' => 'XL']))
            ->assertUnprocessable();
    }

    public function test_shop_all_lists_main_products_and_uses_available_sizes_for_recommendations(): void
    {
        $matching = Product::factory()->create(['name' => 'SHOP-ALL-M', 'available_sizes' => ['M']]);
        Product::factory()->create(['name' => 'SHOP-ALL-HIDDEN', 'available_sizes' => ['S'], 'is_active' => false]);

        $this->get(route('products.index', ['size' => 'M']))
            ->assertOk()
            ->assertSee('SHOP-ALL-M')
            ->assertDontSee('SHOP-ALL-HIDDEN')
            ->assertSee(route('storefront.products.show', $matching), false);
    }

    public function test_inactive_products_cannot_be_opened_directly(): void
    {
        $inactive = Product::factory()->inactive()->create();

        $this->get(route('storefront.products.show', $inactive))->assertNotFound();
    }

    public function test_standard_measurement_result_links_to_matching_products_and_custom_keeps_whatsapp(): void
    {
        $this->seed(SizeStandardSeeder::class);

        $standard = $this->post(route('measurements.store'), [
            'right_hand_data' => $this->hand(16, 12, 13, 12, 10),
        ]);

        $standard
            ->assertOk()
            ->assertSee(route('products.index', ['size' => 'M']), false)
            ->assertSee('view M press on nails')
            ->assertDontSee('consult here');

        $custom = $this->post(route('measurements.store'), [
            'right_hand_data' => $this->hand(25, 25, 25, 25, 25),
        ]);

        $custom
            ->assertOk()
            ->assertSee('consult here')
            ->assertDontSee(route('products.index', ['size' => 'Custom']), false);
    }

    public function test_sizing_result_renders_up_to_four_matching_main_product_recommendations(): void
    {
        $this->seed(SizeStandardSeeder::class);
        Product::factory()->create(['name' => 'RESULT-MATCH', 'available_sizes' => ['M']]);
        Product::factory()->create(['name' => 'RESULT-NO-MATCH', 'available_sizes' => ['S']]);

        $this->post(route('measurements.store'), [
            'right_hand_data' => $this->hand(16, 12, 13, 12, 10),
        ])->assertOk()
            ->assertSee('Recommended for your size')
            ->assertSee('RESULT-MATCH')
            ->assertDontSee('RESULT-NO-MATCH');
    }

    /** @return array{jempol: int, telunjuk: int, tengah: int, manis: int, kelingking: int} */
    private function hand(int $jempol, int $telunjuk, int $tengah, int $manis, int $kelingking): array
    {
        return compact('jempol', 'telunjuk', 'tengah', 'manis', 'kelingking');
    }
}
