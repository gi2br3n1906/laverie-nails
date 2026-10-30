<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReleaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.midtrans.server_key', 'midtrans-server-secret');
    }

    public function test_expiring_an_unpaid_order_returns_reserved_stock_exactly_once(): void
    {
        [$order, $product] = $this->orderWithReservedStock(quantity: 2, stockAfterReservation: 3);

        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($order, 'expire'))->assertOk();

        $this->assertSame(5, $product->refresh()->stock);
        $this->assertSame(PaymentStatus::Expired, $order->refresh()->payment_status);
        $this->assertNotNull($order->stock_restored_at);

        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($order, 'expire'))->assertOk();

        $this->assertSame(5, $product->refresh()->stock);
    }

    public function test_failed_payment_also_returns_reserved_stock(): void
    {
        [$order, $product] = $this->orderWithReservedStock(quantity: 3, stockAfterReservation: 0);

        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($order, 'cancel'))->assertOk();

        $this->assertSame(3, $product->refresh()->stock);
        $this->assertSame(PaymentStatus::Failed, $order->refresh()->payment_status);
    }

    public function test_paid_order_keeps_its_reserved_stock_when_a_stale_expire_arrives(): void
    {
        [$order, $product] = $this->orderWithReservedStock(quantity: 2, stockAfterReservation: 3);

        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($order, 'settlement'))->assertOk();
        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($order, 'expire'))->assertOk();

        $this->assertSame(3, $product->refresh()->stock);
        $this->assertSame(PaymentStatus::Paid, $order->refresh()->payment_status);
        $this->assertNull($order->refresh()->stock_restored_at);
    }

    public function test_settlement_after_expiry_does_not_mark_a_released_order_as_paid(): void
    {
        [$order, $product] = $this->orderWithReservedStock(quantity: 2, stockAfterReservation: 3);

        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($order, 'expire'))->assertOk();
        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($order, 'settlement'))->assertOk();

        $this->assertSame(5, $product->refresh()->stock);
        $this->assertSame(PaymentStatus::Expired, $order->refresh()->payment_status);
    }

    /** @return array{0: Order, 1: Product} */
    private function orderWithReservedStock(int $quantity, int $stockAfterReservation): array
    {
        $product = Product::factory()->create([
            'stock' => $stockAfterReservation,
            'price' => '100000.00',
        ]);
        $order = Order::factory()->create();

        OrderItem::factory()->for($order)->for($product)->create([
            'quantity' => $quantity,
            'product_price' => 100000,
        ]);

        return [$order, $product];
    }

    /** @return array<string, string> */
    private function signedPayload(Order $order, string $transactionStatus): array
    {
        $payload = [
            'order_id' => $order->id,
            'status_code' => '200',
            'gross_amount' => number_format($order->grand_total, 2, '.', ''),
            'transaction_status' => $transactionStatus,
        ];
        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'midtrans-server-secret',
        );

        return $payload;
    }
}
