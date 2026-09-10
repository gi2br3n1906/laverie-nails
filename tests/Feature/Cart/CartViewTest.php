<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CartViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_layout_contains_accessible_ajax_cart_drawer(): void
    {
        $content = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('data-cart-drawer', $content);
        $this->assertStringContainsString('data-cart-drawer-trigger', $content);
        $this->assertStringContainsString('data-cart-drawer-close', $content);
        $this->assertStringContainsString('data-cart-drawer-items', $content);
        $this->assertStringContainsString('data-cart-drawer-notes', $content);
        $this->assertStringContainsString('data-cart-drawer-checkout', $content);
        $this->assertStringContainsString('Subtotal', $content);
        $this->assertStringContainsString(route('cart.state'), $content);
        $this->assertStringContainsString(route('checkout.create'), $content);
    }

    public function test_cart_state_and_ajax_mutations_return_owner_scoped_totals(): void
    {
        $product = Product::factory()->create(['price' => '125000.00', 'stock' => 5]);

        $this->postJson(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'size_type' => 'standard',
            'standard_size' => 'S',
        ])->assertOk()->assertJsonPath('data.quantity', 1)->assertJsonPath('data.total', 12500000);

        $item = CartItem::query()->sole();
        $this->patchJson(route('cart.update', $item), ['quantity' => 2])
            ->assertOk()->assertJsonPath('data.quantity', 2)->assertJsonPath('data.items.0.quantity', 2);

        $this->getJson(route('cart.state'))->assertOk()
            ->assertJsonPath('data.items.0.size_label', 'SIZE: S')
            ->assertJsonPath('data.items.0.update_url', route('cart.update', $item))
            ->assertJsonPath('data.items.0.remove_url', route('cart.destroy', $item));

        $this->deleteJson(route('cart.destroy', $item))->assertOk()
            ->assertJsonPath('data.quantity', 0)->assertJsonCount(0, 'data.items');
    }

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
            ->assertSee('action="'.route('cart.store').'"', false)
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

        $this->postJson(route('cart.store'), [
            'product_id' => $first->id,
            'quantity' => 2,
            'size_type' => 'standard',
            'standard_size' => 'M',
        ])->assertOk();
        $this->postJson(route('cart.store'), [
            'product_id' => $second->id,
            'quantity' => 1,
            'size_type' => 'custom',
            'custom_measurements' => $this->customMeasurements(),
        ])->assertOk();

        $response = $this->get('/input-data')
            ->assertOk()
            ->assertSee('Input Data Pengukuran')
            ->assertSee('Keranjang
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

    public function test_measurements_page_uses_ajax_cart_drawer_toggle(): void
    {
        $this->get(route('measurements.create'))
            ->assertOk()
            ->assertSee('data-cart-drawer', false)
            ->assertSee('data-cart-drawer-trigger', false)
            ->assertSee('aria-controls="cart-drawer"', false)
            ->assertSee(route('cart.state'), false);
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
