<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Notifications\OrderPaid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReconcilePendingPaymentsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.midtrans', [
            'server_key' => 'midtrans-server-secret',
            'client_key' => 'midtrans-client-key',
            'snap_url' => 'https://midtrans.test/snap/v1/transactions',
            'snap_js_url' => 'https://midtrans.test/snap/snap.js',
            'api_url' => 'https://api.sandbox.test/v2',
            'is_production' => false,
        ]);
    }

    public function test_it_reconciles_a_settled_pending_order_and_emails_the_customer_once(): void
    {
        Notification::fake();

        $order = Order::factory()->create([
            'payment_status' => PaymentStatus::Pending,
            'snap_token' => 'snap-token-for-order',
            'grand_total' => 100000,
            'created_at' => now()->subHour(),
        ]);

        Http::fake([
            'https://api.sandbox.test/v2/'.$order->id.'/status' => Http::response([
                'order_id' => $order->id,
                'gross_amount' => '100000.00',
                'transaction_status' => 'settlement',
            ]),
        ]);

        $this->artisan('orders:reconcile-payments')->assertSuccessful();

        $this->assertSame(PaymentStatus::Paid, $order->refresh()->payment_status);
        Notification::assertSentOnDemandTimes(OrderPaid::class, 1);

        $this->artisan('orders:reconcile-payments')->assertSuccessful();

        Notification::assertSentOnDemandTimes(OrderPaid::class, 1);
    }

    public function test_it_does_not_query_orders_that_are_still_within_the_grace_period(): void
    {
        Notification::fake();
        Http::fake();

        Order::factory()->create([
            'payment_status' => PaymentStatus::Pending,
            'snap_token' => 'snap-token-for-order',
            'created_at' => now(),
        ]);

        $this->artisan('orders:reconcile-payments')->assertSuccessful();

        Http::assertNothingSent();
        Notification::assertNothingSent();
    }
}
