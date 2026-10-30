<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.midtrans.snap_js_url', 'https://midtrans.test/snap/snap.js');
        config()->set('services.midtrans.client_key', 'midtrans-client-key');
    }

    public function test_cart_has_checkout_cta_and_checkout_page_renders_premium_form_hooks_and_summary(): void
    {
        $product = Product::factory()->create(['name' => 'Moonlit Pearl', 'price' => '175000.00', 'stock' => 4]);
        $this->actingAs(User::factory()->create())->post('/cart-items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'size_type' => 'standard',
            'standard_size' => 'L',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-cart-drawer', false)
            ->assertSee('data-cart-drawer-checkout', false)
            ->assertSee(route('checkout.create'), false);

        $response = $this->get('/checkout')
            ->assertOk()
            ->assertSee('Pembayaran aman')
            ->assertSee('Moonlit Pearl')
            ->assertSee('Ongkir dibayar saat paket diterima')
            ->assertSee('Total Produk')
            ->assertSee('Rp 350.000')
            ->assertSee('data-checkout-form', false)
            ->assertDontSee('Ongkos kirim flat')
            ->assertDontSee('data-cities-url', false)
            ->assertDontSee('data-shipping-options-url', false)
            ->assertDontSee('data-grand-total', false)
            ->assertDontSee('name="shipping_option"', false)
            ->assertDontSee('Jawa Tengah');

        $content = (string) $response->getContent();
        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $content);
        $this->assertStringNotContainsString('style=', $content);
    }

    public function test_empty_cart_cannot_enter_checkout(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/checkout')
            ->assertRedirect('/')
            ->assertSessionHasErrors(['cart']);
    }

    public function test_payment_page_exposes_snap_configuration_via_data_attributes_without_inline_script(): void
    {
        $order = Order::factory()->create(['snap_token' => 'snap-token-123']);
        $this->withSession(['cart.guest_id' => $order->guest_id]);

        $response = $this->get(route('checkout.payment', $order))
            ->assertOk()
            ->assertSee('Selesaikan Pembayaran')
            ->assertSee('Total Produk')
            ->assertSee('Belum termasuk ongkir')
            ->assertSee('data-payment-snap', false)
            ->assertSee('data-snap-token="snap-token-123"', false)
            ->assertSee('data-snap-url="https://midtrans.test/snap/snap.js"', false)
            ->assertSee('data-client-key="midtrans-client-key"', false);

        $content = (string) $response->getContent();
        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $content);
        $this->assertStringNotContainsString('style=', $content);
    }

    public function test_expired_payment_page_hides_the_snap_button(): void
    {
        $order = Order::factory()->create([
            'payment_status' => PaymentStatus::Expired,
            'stock_restored_at' => now(),
        ]);
        $this->withSession(['cart.guest_id' => $order->guest_id]);

        $this->get(route('checkout.payment', $order))
            ->assertOk()
            ->assertDontSee('data-payment-snap', false)
            ->assertDontSee('Bayar Sekarang')
            ->assertSee('Pembayaran tidak dapat dilanjutkan.');
    }
}
