<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PaymentStatusSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.midtrans.server_key', 'midtrans-server-secret');
        config()->set('services.midtrans.api_url', 'https://midtrans.test/v2');
        Notification::fake();
    }

    public function test_owner_can_sync_a_settled_payment_without_a_webhook(): void
    {
        $user = User::factory()->create();
        $order = $this->pendingOrderFor($user);

        Http::fake([
            'https://midtrans.test/v2/*/status' => Http::response($this->statusPayload($order)),
        ]);

        $this->actingAs($user)
            ->getJson(route('checkout.status', $order))
            ->assertOk()
            ->assertJson([
                'payment_status' => 'paid',
                'paid' => true,
                'redirect_url' => route('dashboard.orders.show', $order),
            ]);

        $this->assertSame(PaymentStatus::Paid, $order->refresh()->payment_status);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://midtrans.test/v2/'.$order->id.'/status');
    }

    public function test_payment_page_refreshes_the_status_after_returning_from_snap(): void
    {
        $user = User::factory()->create();
        $order = $this->pendingOrderFor($user);

        Http::fake([
            'https://midtrans.test/v2/*/status' => Http::response($this->statusPayload($order)),
        ]);

        $this->actingAs($user)
            ->get(route('checkout.payment', ['order' => $order, 'check' => 1]))
            ->assertRedirect(route('dashboard.orders.show', $order));

        $this->assertSame(PaymentStatus::Paid, $order->refresh()->payment_status);
    }

    public function test_a_signed_finish_url_can_refresh_the_payment_without_a_session(): void
    {
        $user = User::factory()->create();
        $order = $this->pendingOrderFor($user);

        Http::fake([
            'https://midtrans.test/v2/*/status' => Http::response($this->statusPayload($order)),
        ]);

        $url = URL::temporarySignedRoute(
            'checkout.payment',
            now()->addDay(),
            ['order' => $order, 'check' => 1],
        );

        $this->get($url)
            ->assertOk()
            ->assertSee('Payment has been received.')
            ->assertDontSee('Pay Now');
    }

    public function test_a_non_owner_cannot_read_or_sync_the_payment_status(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = $this->pendingOrderFor($owner);

        Http::fake();

        $this->actingAs($intruder)
            ->getJson(route('checkout.status', $order))
            ->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_a_mismatched_amount_is_never_applied(): void
    {
        $user = User::factory()->create();
        $order = $this->pendingOrderFor($user);

        Http::fake([
            'https://midtrans.test/v2/*/status' => Http::response([
                ...$this->statusPayload($order),
                'gross_amount' => '1.00',
            ]),
        ]);

        $this->actingAs($user)
            ->getJson(route('checkout.status', $order))
            ->assertOk()
            ->assertJson(['paid' => false, 'payment_status' => 'pending']);

        $this->assertSame(PaymentStatus::Pending, $order->refresh()->payment_status);
    }

    private function pendingOrderFor(User $user): Order
    {
        return Order::factory()->for($user)->create([
            'guest_id' => null,
            'payment_status' => PaymentStatus::Pending,
            'snap_token' => 'snap-token-123',
            'grand_total' => 224000,
        ]);
    }

    /** @return array<string, string> */
    private function statusPayload(Order $order): array
    {
        return [
            'order_id' => $order->id,
            'status_code' => '200',
            'gross_amount' => number_format($order->grand_total, 2, '.', ''),
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'payment_type' => 'qris',
        ];
    }
}
