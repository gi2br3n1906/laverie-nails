<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Enums\FulfillmentStatus;
use App\Enums\PaymentStatus;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.rajaongkir', [
            'base_url' => 'https://rajaongkir.test/starter',
            'api_key' => 'raja-secret',
            'origin_city_id' => '399',
            'couriers' => ['jne', 'jnt'],
            'item_weight_grams' => 500,
        ]);
        config()->set('services.midtrans', [
            'server_key' => 'midtrans-server-secret',
            'client_key' => 'midtrans-client-key',
            'snap_url' => 'https://midtrans.test/snap/v1/transactions',
            'snap_js_url' => 'https://midtrans.test/snap/snap.js',
            'is_production' => false,
        ]);
    }

    public function test_guest_checkout_revalidates_totals_creates_snapshots_deducts_stock_and_clears_only_its_cart(): void
    {
        $this->fakeGateways();
        $product = Product::factory()->create([
            'name' => 'Azure Whisper',
            'price' => '250000.00',
            'stock' => 8,
        ]);
        $otherProduct = Product::factory()->create(['stock' => 4]);

        $this->post('/cart', $this->cartPayload($product, 2))->assertRedirect('/cart');
        $guestId = (string) CartItem::query()->sole()->session_id;
        CartItem::query()->create([
            'user_id' => null,
            'session_id' => 'another-browser',
            'product_id' => $otherProduct->id,
            'quantity' => 1,
            'size_type' => 'standard',
            'size_payload' => ['size' => 'S'],
            'size_signature' => hash('sha256', 'other-cart'),
        ]);

        $response = $this->post('/checkout', [
            ...$this->checkoutPayload(),
            'shipping_cost' => 1,
        ]);

        $order = Order::query()->sole();
        $response->assertRedirect(route('checkout.payment', $order));
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[0-9A-HJKMNP-TV-Z]{26}$/', $order->id);
        $this->assertNull($order->user_id);
        $this->assertSame($guestId, $order->guest_id);
        $this->assertSame('jne:REG', $order->courier);
        $this->assertSame(18000, $order->shipping_cost);
        $this->assertSame(500000, $order->subtotal);
        $this->assertSame(518000, $order->grand_total);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(FulfillmentStatus::Pending, $order->fulfillment_status);
        $this->assertSame('snap-token-for-order', $order->snap_token);
        $this->assertSame('Please pack the adhesive tabs separately.', $order->order_notes);
        $this->assertSame(1, $order->items()->count());
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Azure Whisper',
            'product_price' => 250000,
            'quantity' => 2,
            'size_type' => 'standard',
        ]);
        $this->assertSame(['size' => 'M'], $order->items()->sole()->size_payload);
        $this->assertSame(6, $product->refresh()->stock);
        $this->assertDatabaseMissing('cart_items', ['session_id' => $guestId]);
        $this->assertDatabaseHas('cart_items', ['session_id' => 'another-browser']);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://midtrans.test/snap/v1/transactions'
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('midtrans-server-secret:'))
            && $request['transaction_details']['order_id'] === $order->id
            && $request['transaction_details']['gross_amount'] === 518000
            && $request['item_details'][0]['price'] === 250000
            && $request['item_details'][1]['id'] === 'SHIPPING');
    }

    public function test_authenticated_checkout_records_the_user_without_a_guest_owner(): void
    {
        $this->fakeGateways();
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => '125000.00', 'stock' => 3]);
        $this->actingAs($user)->post('/cart', $this->cartPayload($product));

        $this->actingAs($user)->post('/checkout', $this->checkoutPayload())->assertRedirect();

        $order = Order::query()->sole();
        $this->assertSame($user->id, $order->user_id);
        $this->assertNull($order->guest_id);
        $this->assertSame(0, $user->cartItems()->count());
    }

    public function test_stock_change_after_cart_creation_rejects_checkout_without_partial_writes(): void
    {
        $this->fakeGateways();
        $product = Product::factory()->create(['price' => '200000.00', 'stock' => 3]);
        $this->post('/cart', $this->cartPayload($product, 2));
        $product->update(['stock' => 1]);

        $this->from('/checkout')->post('/checkout', $this->checkoutPayload())
            ->assertRedirect('/checkout')
            ->assertSessionHasErrors(['cart']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame(1, $product->refresh()->stock);
        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'midtrans.test'));
    }

    public function test_midtrans_failure_rolls_back_order_items_stock_and_cart_deletion(): void
    {
        $this->fakeGateways(failPayment: true);
        $product = Product::factory()->create(['price' => '200000.00', 'stock' => 3]);
        $this->post('/cart', $this->cartPayload($product, 2));
        $this->withoutExceptionHandling();

        try {
            $this->post('/checkout', $this->checkoutPayload());
            $this->fail('Midtrans failure should bubble as an HTTP client exception.');
        } catch (RequestException) {
            // Expected: the surrounding database transaction must roll back.
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame(3, $product->refresh()->stock);
    }

    public function test_payment_page_is_visible_only_to_the_order_owner(): void
    {
        $this->fakeGateways();
        $product = Product::factory()->create(['stock' => 2]);
        $this->post('/cart', $this->cartPayload($product));
        $this->post('/checkout', $this->checkoutPayload());
        $order = Order::query()->sole();

        $this->get(route('checkout.payment', $order))->assertOk()->assertSee($order->id);
        $this->withSession(['cart.guest_id' => 'different-browser'])
            ->get(route('checkout.payment', $order))
            ->assertNotFound();
    }

    public function test_authenticated_payment_page_is_hidden_from_other_users_and_guests(): void
    {
        $this->fakeGateways();
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $product = Product::factory()->create(['stock' => 2]);
        $this->actingAs($owner)->post('/cart', $this->cartPayload($product));
        $this->actingAs($owner)->post('/checkout', $this->checkoutPayload());
        $order = Order::query()->sole();

        $this->actingAs($owner)->get(route('checkout.payment', $order))->assertOk();
        $this->actingAs($intruder)->get(route('checkout.payment', $order))->assertNotFound();
        $this->app['auth']->forgetGuards();
        $this->get(route('checkout.payment', $order))->assertNotFound();
    }

    public function test_unknown_or_tampered_shipping_option_cannot_create_an_order(): void
    {
        $this->fakeGateways();
        $product = Product::factory()->create(['stock' => 2]);
        $this->post('/cart', $this->cartPayload($product));

        $this->from('/checkout')->post('/checkout', [
            ...$this->checkoutPayload(),
            'shipping_option' => 'jne:FREE',
        ])->assertRedirect('/checkout')->assertSessionHasErrors(['shipping_option']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame(2, $product->refresh()->stock);
        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'midtrans.test'));
    }

    public function test_order_and_item_snapshots_survive_customer_and_product_deletion(): void
    {
        $this->fakeGateways();
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Permanent Memory', 'stock' => 2]);
        $this->actingAs($user)->post('/cart', $this->cartPayload($product));
        $this->actingAs($user)->post('/checkout', $this->checkoutPayload());
        $order = Order::query()->sole();

        $user->delete();
        $product->delete();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'user_id' => null]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => null,
            'product_name' => 'Permanent Memory',
        ]);
    }

    /** @return array<string, int|string> */
    private function cartPayload(Product $product, int $quantity = 1): array
    {
        return [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'size_type' => 'standard',
            'standard_size' => 'M',
        ];
    }

    /** @return array<string, string> */
    private function checkoutPayload(): array
    {
        return [
            'customer_name' => 'Alya Putri',
            'customer_email' => 'alya@example.com',
            'customer_phone' => '081234567890',
            'province_id' => '6',
            'city_id' => '152',
            'shipping_address' => 'Jl. Melati No. 7, Kecamatan Menteng, Jakarta',
            'shipping_option' => 'jne:REG',
            'order_notes' => 'Please pack the adhesive tabs separately.',
        ];
    }

    private function fakeGateways(bool $failPayment = false): void
    {
        Http::fake(function (Request $request) use ($failPayment) {
            if (str_ends_with($request->url(), '/cost')) {
                $courier = (string) $request['courier'];

                return Http::response([
                    'rajaongkir' => ['results' => [[
                        'code' => $courier,
                        'name' => strtoupper($courier),
                        'costs' => [[
                            'service' => $courier === 'jne' ? 'REG' : 'EZ',
                            'description' => 'Regular Service',
                            'cost' => [['value' => $courier === 'jne' ? 18000 : 16500, 'etd' => '2-3', 'note' => '']],
                        ]],
                    ]]],
                ]);
            }

            if ($request->url() === 'https://midtrans.test/snap/v1/transactions') {
                return $failPayment
                    ? Http::response(['error_messages' => ['Gateway unavailable']], 503)
                    : Http::response(['token' => 'snap-token-for-order', 'redirect_url' => 'https://midtrans.test/pay']);
            }

            return Http::response(['message' => 'Unexpected request'], 500);
        });
    }
}
