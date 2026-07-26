<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.rajaongkir', [
            'base_url' => 'https://rajaongkir.test/starter',
            'api_key' => 'raja-secret',
            'origin_city_id' => '399',
            'couriers' => ['jne'],
            'item_weight_grams' => 500,
        ]);
        config()->set('services.midtrans.snap_js_url', 'https://midtrans.test/snap/snap.js');
        config()->set('services.midtrans.client_key', 'midtrans-client-key');
    }

    public function test_cart_has_checkout_cta_and_checkout_page_renders_premium_form_hooks_and_summary(): void
    {
        Http::fake([
            'rajaongkir.test/starter/province' => Http::response([
                'rajaongkir' => ['results' => [
                    ['province_id' => '6', 'province' => 'Jawa Tengah'],
                ]],
            ]),
        ]);
        $product = Product::factory()->create(['name' => 'Moonlit Pearl', 'price' => '175000.00', 'stock' => 4]);
        $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
            'size_type' => 'standard',
            'standard_size' => 'L',
        ]);

        $this->get('/cart')
            ->assertOk()
            ->assertSee(route('checkout.create'))
            ->assertSee('Lanjut ke Checkout');

        $response = $this->get('/checkout')
            ->assertOk()
            ->assertSee('Secure checkout')
            ->assertSee('Moonlit Pearl')
            ->assertSee('Jawa Tengah')
            ->assertSee('data-checkout-form', false)
            ->assertSee('data-cities-url', false)
            ->assertSee('data-shipping-options-url', false)
            ->assertSee('data-grand-total', false)
            ->assertSee('name="shipping_option"', false);

        $content = (string) $response->getContent();
        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $content);
        $this->assertStringNotContainsString('style=', $content);
    }

    public function test_city_and_shipping_endpoints_return_normalized_json_and_derive_weight_from_owned_cart(): void
    {
        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/city')) {
                return Http::response(['rajaongkir' => ['results' => [[
                    'city_id' => '152',
                    'type' => 'Kota',
                    'city_name' => 'Semarang',
                ]]]]);
            }

            return Http::response(['rajaongkir' => ['results' => [[
                'code' => 'jne',
                'name' => 'JNE',
                'costs' => [[
                    'service' => 'REG',
                    'description' => 'Regular Service',
                    'cost' => [['value' => 18000, 'etd' => '2-3', 'note' => '']],
                ]],
            ]]]]);
        });
        $product = Product::factory()->create(['stock' => 4]);
        $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 3,
            'size_type' => 'standard',
            'standard_size' => 'M',
        ]);

        $this->getJson('/checkout/logistics/cities?province_id=6')
            ->assertOk()
            ->assertExactJson(['data' => [['id' => '152', 'name' => 'Kota Semarang']]]);
        $this->getJson('/checkout/logistics/shipping-options?destination_id=152')
            ->assertOk()
            ->assertJsonPath('data.0.key', 'jne:REG')
            ->assertJsonPath('data.0.cost', 18000);

        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/cost')
            && $request['weight'] === 1500);
    }

    public function test_empty_cart_cannot_enter_checkout(): void
    {
        $this->get('/checkout')->assertRedirect('/cart')->assertSessionHasErrors(['cart']);
    }

    public function test_payment_page_exposes_snap_configuration_via_data_attributes_without_inline_script(): void
    {
        $order = Order::factory()->create(['snap_token' => 'snap-token-123']);
        $this->withSession(['cart.guest_id' => $order->guest_id]);

        $response = $this->get(route('checkout.payment', $order))
            ->assertOk()
            ->assertSee('Selesaikan Pembayaran')
            ->assertSee('data-payment-snap', false)
            ->assertSee('data-snap-token="snap-token-123"', false)
            ->assertSee('data-snap-url="https://midtrans.test/snap/snap.js"', false)
            ->assertSee('data-client-key="midtrans-client-key"', false);

        $content = (string) $response->getContent();
        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $content);
        $this->assertStringNotContainsString('style=', $content);
    }
}
