<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Models\NailCatalog;
use Database\Seeders\SizeStandardSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_marketplace_exposes_only_active_laverie_catalogs(): void
    {
        NailCatalog::factory()->create(['title' => 'PUBLIC-LAVERIE-CATALOG']);
        NailCatalog::factory()->inactive()->create(['title' => 'INACTIVE-CATALOG']);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('PUBLIC-LAVERIE-CATALOG')
            ->assertDontSee('INACTIVE-CATALOG');
    }

    public function test_size_query_filters_exactly_to_the_requested_canonical_size(): void
    {
        NailCatalog::factory()->create(['title' => 'SIZE-M', 'size' => 'M']);
        NailCatalog::factory()->create(['title' => 'SIZE-S', 'size' => 'S']);

        $this->get(route('products.index', ['size' => 'M']))
            ->assertOk()
            ->assertSee('SIZE-M')
            ->assertDontSee('SIZE-S');

        $this->get(route('products.index', ['size' => 'XL']))
            ->assertUnprocessable();
    }

    public function test_non_public_catalogs_cannot_be_opened_directly(): void
    {
        $inactive = NailCatalog::factory()->inactive()->create();

        $this->get(route('products.show', $inactive))->assertNotFound();
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
            ->assertDontSee('Konsultasikan ukuran Custom');

        $custom = $this->post(route('measurements.store'), [
            'right_hand_data' => $this->hand(25, 25, 25, 25, 25),
        ]);

        $custom
            ->assertOk()
            ->assertSee('Konsultasikan ukuran Custom')
            ->assertDontSee(route('products.index', ['size' => 'Custom']), false);
    }

    /** @return array{jempol: int, telunjuk: int, tengah: int, manis: int, kelingking: int} */
    private function hand(int $jempol, int $telunjuk, int $tengah, int $manis, int $kelingking): array
    {
        return compact('jempol', 'telunjuk', 'tengah', 'manis', 'kelingking');
    }
}
