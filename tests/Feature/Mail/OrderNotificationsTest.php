<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Enums\CartSizeType;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\AdminOrderPlaced;
use App\Notifications\OrderPaid;
use App\Notifications\OrderPlaced;
use App\Notifications\OrderShipped;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderNotificationsTest extends TestCase
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
            'is_production' => false,
        ]);
    }

    public function test_placing_an_order_queues_a_receipt_with_a_signed_payment_link(): void
    {
        Notification::fake();
        config()->set('laverie.admin.email', 'owner@example.com');
        $this->fakeGateways();

        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => '209000.00', 'stock' => 5]);
        $payload = ['size' => 'M', 'length' => 'Medium'];

        CartItem::query()->create([
            'user_id' => $user->id,
            'session_id' => null,
            'product_id' => $product->id,
            'quantity' => 1,
            'size_type' => 'standard',
            'size_payload' => $payload,
            'size_signature' => hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)),
        ]);

        $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Alya Putri',
            'customer_email' => 'alya@example.com',
            'customer_phone' => '081234567890',
            'shipping_address' => 'Jl. Melati No. 7, RT 01/RW 02, 12950',
            'province_code' => '31',
            'province_name' => 'DKI Jakarta',
            'regency_code' => '31.74',
            'regency_name' => 'Kota Administrasi Jakarta Selatan',
            'district_code' => '31.74.09',
            'district_name' => 'Setiabudi',
            'village_code' => '31.74.09.1002',
            'village_name' => 'Kuningan Barat',
        ])->assertRedirect();

        $order = Order::query()->sole();

        Notification::assertSentOnDemand(
            OrderPlaced::class,
            fn (OrderPlaced $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notification->order->getKey() === $order->getKey()
                && $channels === ['mail']
                && $notifiable->routes['mail'] === 'alya@example.com',
        );

        Notification::assertSentOnDemand(
            AdminOrderPlaced::class,
            fn (AdminOrderPlaced $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notification->order->getKey() === $order->getKey()
                && $channels === ['mail']
                && $notifiable->routes['mail'] === 'owner@example.com',
        );

        $rendered = (string) (new OrderPlaced($order))->toMail(new AnonymousNotifiable)->render();

        $this->assertStringContainsString($order->getKey(), $rendered);
        $this->assertStringContainsString('Rp 209.000', $rendered);
        $this->assertStringContainsString('Ongkos kirim: Rp 15.000', $rendered);
        $this->assertStringContainsString('Total: Rp 224.000', $rendered);
        $this->assertStringContainsString('signature=', $rendered);

        $adminRendered = (string) (new AdminOrderPlaced($order))->toMail(new AnonymousNotifiable)->render();

        $this->assertStringContainsString($order->getKey(), $adminRendered);
        $this->assertStringContainsString('Alya Putri', $adminRendered);
        $this->assertStringContainsString(route('admin.orders.show', $order), $adminRendered);
    }

    public function test_settlement_emails_the_customer_exactly_once(): void
    {
        Notification::fake();

        $order = Order::factory()->create([
            'customer_email' => 'buyer@example.com',
            'grand_total' => 227000,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => null,
            'size_type' => CartSizeType::Standard,
        ]);

        $payload = $this->signedPayload($order, 'settlement');

        $this->postJson('/api/payments/midtrans/notification', $payload)->assertOk();
        $this->assertSame(PaymentStatus::Paid, $order->refresh()->payment_status);

        Notification::assertSentOnDemandTimes(OrderPaid::class, 1);

        $this->postJson('/api/payments/midtrans/notification', $payload)->assertOk();
        Notification::assertSentOnDemandTimes(OrderPaid::class, 1);
    }

    public function test_late_settlement_after_stock_was_released_does_not_email_the_customer(): void
    {
        Notification::fake();

        $order = Order::factory()->create([
            'customer_email' => 'buyer@example.com',
            'payment_status' => PaymentStatus::Expired,
            'stock_restored_at' => now(),
        ]);

        $this->postJson('/api/payments/midtrans/notification', $this->signedPayload($order, 'settlement'))->assertOk();

        Notification::assertNothingSent();
    }

    public function test_marking_an_order_shipped_emails_the_customer_once(): void
    {
        Notification::fake();

        $order = Order::factory()->paid()->create(['customer_email' => 'buyer@example.com']);
        $admin = User::factory()->withRole(UserRole::Admin)->create();

        $this->actingAs($admin)->patch('/admin/orders/'.$order->id, [
            'fulfillment_status' => 'shipped',
            'courier' => 'JNE',
            'tracking_number' => 'JNE1234567890',
        ])->assertRedirect(route('admin.orders.show', $order));

        Notification::assertSentOnDemand(
            OrderShipped::class,
            fn (OrderShipped $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notification->order->getKey() === $order->getKey()
                && $notifiable->routes['mail'] === 'buyer@example.com',
        );

        $this->actingAs($admin)->patch('/admin/orders/'.$order->id, [
            'fulfillment_status' => 'shipped',
            'courier' => 'JNE',
            'tracking_number' => 'JNE1234567890',
        ])->assertRedirect(route('admin.orders.show', $order));

        Notification::assertSentOnDemandTimes(OrderShipped::class, 1);
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

    private function fakeGateways(): void
    {
        Http::fake(function (Request $request) {
            if ($request->url() === 'https://midtrans.test/snap/v1/transactions') {
                return Http::response(['token' => 'snap-token-for-order', 'redirect_url' => 'https://midtrans.test/pay']);
            }

            return Http::response(['message' => 'Unexpected request'], 500);
        });
    }
}
