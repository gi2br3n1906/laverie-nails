<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.midtrans.server_key', 'midtrans-server-secret');
    }

    public function test_valid_settlement_and_accepted_capture_notifications_mark_orders_paid(): void
    {
        $settlementOrder = Order::factory()->create();
        $captureOrder = Order::factory()->create();

        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($settlementOrder, 'settlement'))
            ->assertOk();
        $this->postJson('/api/payments/midtrans/notification', [
            ...$this->signedPayload($captureOrder, 'capture'),
            'fraud_status' => 'accept',
        ])->assertOk();

        $this->assertSame(PaymentStatus::Paid, $settlementOrder->refresh()->payment_status);
        $this->assertSame(PaymentStatus::Paid, $captureOrder->refresh()->payment_status);
    }

    public function test_failure_and_expiration_notifications_are_mapped_to_domain_statuses(): void
    {
        $deniedOrder = Order::factory()->create();
        $fraudDeniedOrder = Order::factory()->create();
        $cancelledOrder = Order::factory()->create();
        $expiredOrder = Order::factory()->create();

        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($deniedOrder, 'deny'))->assertOk();
        $this->postJson('/api/payments/midtrans/notification', [
            ...$this->signedPayload($fraudDeniedOrder, 'capture'),
            'fraud_status' => 'deny',
        ])->assertOk();
        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($cancelledOrder, 'cancel'))->assertOk();
        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($expiredOrder, 'expire'))->assertOk();

        $this->assertSame(PaymentStatus::Failed, $deniedOrder->refresh()->payment_status);
        $this->assertSame(PaymentStatus::Failed, $fraudDeniedOrder->refresh()->payment_status);
        $this->assertSame(PaymentStatus::Failed, $cancelledOrder->refresh()->payment_status);
        $this->assertSame(PaymentStatus::Expired, $expiredOrder->refresh()->payment_status);
    }

    public function test_capture_challenge_remains_pending_until_a_conclusive_notification_arrives(): void
    {
        $order = Order::factory()->create();

        $this->postJson('/api/payments/midtrans/notification', [
            ...$this->signedPayload($order, 'capture'),
            'fraud_status' => 'challenge',
        ])->assertOk();

        $this->assertSame(PaymentStatus::Pending, $order->refresh()->payment_status);
    }

    public function test_invalid_signature_is_rejected_without_changing_the_order(): void
    {
        $order = Order::factory()->create();

        $this->postJson('/api/payments/midtrans/notification', [
            ...$this->signedPayload($order, 'settlement'),
            'signature_key' => str_repeat('0', 128),
        ])->assertForbidden();

        $this->assertSame(PaymentStatus::Pending, $order->refresh()->payment_status);
    }

    public function test_unknown_order_is_rejected_after_signature_validation(): void
    {
        $payload = [
            'order_id' => 'ORD-UNKNOWN',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'transaction_status' => 'settlement',
        ];
        $payload['signature_key'] = $this->signature($payload);

        $this->postJson('/api/payments/midtrans/notification', $payload)->assertNotFound();
    }

    public function test_paid_order_does_not_regress_when_stale_notifications_arrive(): void
    {
        $order = Order::factory()->paid()->create();

        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($order, 'pending'))->assertOk();
        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($order, 'cancel'))->assertOk();
        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($order, 'expire'))->assertOk();

        $this->assertSame(PaymentStatus::Paid, $order->refresh()->payment_status);
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
        $payload['signature_key'] = $this->signature($payload);

        return $payload;
    }

    /** @param  array<string, string>  $payload */
    private function signature(array $payload): string
    {
        return hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'midtrans-server-secret');
    }
}
