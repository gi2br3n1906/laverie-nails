<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CartViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_editorial_product_detail_has_complete_standard_and_custom_sizing_form(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create([
            'name' => 'Midnight Pearl Edit',
            'slug' => 'midnight-pearl-edit',
            'price' => '185000',
            'stock' => 7,
        ]);
        ProductImage::factory()->for($product)->primary()->create(['image_path' => 'products/midnight.jpg']);

        $response = $this->get('/koleksi/'.$product->slug)
            ->assertOk()
            ->assertSee('Midnight Pearl Edit')
            ->assertSee('Rp 185.000')
            ->assertSee('7 tersedia')
            ->assertSee('Standard Size')
            ->assertSee('Custom Measurements')
            ->assertSeeInOrder(['XS', 'S', 'M', 'L'])
            ->assertSee('custom_measurements[right_hand][jempol]', false)
            ->assertSee('custom_measurements[right_hand][kelingking]', false)
            ->assertSee('custom_measurements[left_hand][jempol]', false)
            ->assertSee('custom_measurements[left_hand][kelingking]', false)
            ->assertSee('action="'.url('/cart').'"', false)
            ->assertSee('Tambah ke Keranjang');

        $this->assertDoesNotMatchRegularExpression('/\sstyle\s*=/i', $response->getContent());
        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $response->getContent());
    }

    public function test_inactive_editorial_product_detail_is_not_publicly_visible(): void
    {
        $product = Product::factory()->inactive()->create(['slug' => 'hidden-editorial-set']);

        $this->get('/koleksi/'.$product->slug)->assertNotFound();
    }

    public function test_cart_page_displays_size_details_subtotals_grand_total_controls_image_and_quantity_count(): void
    {
        Storage::fake('public');
        $first = Product::factory()->create([
            'name' => 'Pearl Muse',
            'price' => '125000',
            'stock' => 5,
        ]);
        ProductImage::factory()->for($first)->primary()->create(['image_path' => 'products/pearl.jpg']);
        $second = Product::factory()->create([
            'name' => 'Blue Whisper',
            'price' => '200000',
            'stock' => 3,
        ]);

        $this->post('/cart', [
            'product_id' => $first->id,
            'quantity' => 2,
            'size_type' => 'standard',
            'standard_size' => 'M',
        ]);
        $this->post('/cart', [
            'product_id' => $second->id,
            'quantity' => 1,
            'size_type' => 'custom',
            'custom_measurements' => $this->customMeasurements(),
        ]);

        $response = $this->get('/cart')
            ->assertOk()
            ->assertSee('Keranjang Belanja')
            ->assertSee(Storage::disk('public')->url('products/pearl.jpg'), false)
            ->assertSeeInOrder(['Pearl Muse', 'Size: M', 'Rp 125.000', 'Rp 250.000'])
            ->assertSee('Blue Whisper')
            ->assertSee('Size: Custom')
            ->assertSee('Jempol 14,2 mm')
            ->assertSee('Kelingking 8,7 mm')
            ->assertSee('Grand Total')
            ->assertSee('Rp 450.000')
            ->assertSee('Kurangi jumlah Pearl Muse')
            ->assertSee('Tambah jumlah Pearl Muse')
            ->assertSee('Hapus Pearl Muse')
            ->assertSee('data-cart-count', false)
            ->assertSee('>3</span>', false);

        $this->assertDoesNotMatchRegularExpression('/\sstyle\s*=/i', $response->getContent());
        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $response->getContent());
    }

    public function test_empty_cart_has_a_clear_storefront_return_action(): void
    {
        $this->get('/cart')
            ->assertOk()
            ->assertSee('Keranjang Anda masih kosong')
            ->assertSee('Kembali ke koleksi')
            ->assertSee(url('/#collection'), false);
    }

    public function test_homepage_editorial_product_card_links_to_the_editorial_detail_route(): void
    {
        $product = Product::factory()->create([
            'name' => 'Linked Editorial Set',
            'slug' => 'linked-editorial-set',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Linked Editorial Set')
            ->assertSee(url('/koleksi/linked-editorial-set'), false);
    }

    /** @return array<string, array<string, float>> */
    private function customMeasurements(): array
    {
        return [
            'right_hand' => [
                'jempol' => 14.2,
                'telunjuk' => 11.5,
                'tengah' => 12.1,
                'manis' => 11.4,
                'kelingking' => 8.7,
            ],
            'left_hand' => [
                'jempol' => 14.1,
                'telunjuk' => 11.4,
                'tengah' => 12.0,
                'manis' => 11.3,
                'kelingking' => 8.6,
            ],
        ];
    }
}
