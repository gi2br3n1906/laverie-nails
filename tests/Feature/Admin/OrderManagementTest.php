<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\CartSizeType;
use App\Enums\FulfillmentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_access_order_management(): void
    {
        $order = Order::factory()->create();

        $this->get('/admin/orders')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get('/admin/orders')->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/admin/orders/'.$order->id)->assertForbidden();
        $this->actingAs($this->admin())->get('/admin/orders')->assertOk()->assertSee('Kelola Pesanan');
    }

    public function test_admin_list_is_latest_first_and_can_filter_payment_status(): void
    {
        $olderPaid = Order::factory()->paid()->create([
            'customer_name' => 'Older Paid Customer',
            'created_at' => now()->subDay(),
        ]);
        $latestPending = Order::factory()->create([
            'customer_name' => 'Latest Pending Customer',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin())->get('/admin/orders');
        $response->assertOk()
            ->assertSeeInOrder([$latestPending->id, $olderPaid->id])
            ->assertSee('Kelola Pesanan');

        $this->actingAs($this->admin())->get('/admin/orders?payment_status=paid')
            ->assertOk()
            ->assertSee($olderPaid->id)
            ->assertDontSee($latestPending->id);
    }

    public function test_admin_detail_displays_operations_data_and_all_standard_and_custom_sizes(): void
    {
        $order = Order::factory()->paid()->create([
            'customer_name' => 'Nadia Ayu',
            'customer_email' => 'nadia@example.com',
            'customer_phone' => '081222333444',
            'shipping_address' => 'Jl. Kenanga No. 19, Semarang',
            'courier' => 'jne:REG',
        ]);
        OrderItem::factory()->for($order)->create([
            'product_name' => 'Moonlit Pearl',
            'size_type' => CartSizeType::Standard,
            'size_payload' => ['size' => 'M'],
        ]);
        OrderItem::factory()->for($order)->create([
            'product_name' => 'Bespoke Azure',
            'size_type' => CartSizeType::Custom,
            'size_payload' => $this->customSizePayload(),
        ]);

        $this->actingAs($this->admin())->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSeeText('Nadia Ayu')
            ->assertSeeText('nadia@example.com')
            ->assertSeeText('081222333444')
            ->assertSeeText('Jl. Kenanga No. 19, Semarang')
            ->assertSeeText('JNE · REG')
            ->assertSeeText('Moonlit Pearl')
            ->assertSeeText('Standard · M')
            ->assertSeeText('Bespoke Azure')
            ->assertSeeText('Jempol')
            ->assertSeeText('Telunjuk')
            ->assertSeeText('Tengah')
            ->assertSeeText('Manis')
            ->assertSeeText('Kelingking')
            ->assertSeeText('14 mm')
            ->assertSeeText('8.5 mm');
    }

    public function test_admin_can_update_fulfillment_status_and_tracking_number(): void
    {
        $order = Order::factory()->paid()->create();

        $this->actingAs($this->admin())->patch('/admin/orders/'.$order->id, [
            'fulfillment_status' => 'shipped',
            'tracking_number' => 'JNE-RESI-998877',
        ])->assertRedirect(route('admin.orders.show', $order))->assertSessionHas('status');

        $order->refresh();
        $this->assertSame(FulfillmentStatus::Shipped, $order->fulfillment_status);
        $this->assertSame('JNE-RESI-998877', $order->tracking_number);
    }

    public function test_shipped_status_requires_tracking_number_and_invalid_status_is_rejected(): void
    {
        $order = Order::factory()->paid()->create();

        $this->actingAs($this->admin())->from(route('admin.orders.show', $order))->patch('/admin/orders/'.$order->id, [
            'fulfillment_status' => 'shipped',
            'tracking_number' => '',
        ])->assertRedirect(route('admin.orders.show', $order))->assertSessionHasErrors(['tracking_number']);

        $this->actingAs($this->admin())->from(route('admin.orders.show', $order))->patch('/admin/orders/'.$order->id, [
            'fulfillment_status' => 'cancelled',
            'tracking_number' => 'INVALID',
        ])->assertRedirect(route('admin.orders.show', $order))->assertSessionHasErrors(['fulfillment_status']);

        $this->assertSame(FulfillmentStatus::Pending, $order->refresh()->fulfillment_status);
        $this->assertNull($order->tracking_number);
    }

    public function test_completed_status_preserves_an_existing_tracking_number(): void
    {
        $order = Order::factory()->paid()->create([
            'fulfillment_status' => FulfillmentStatus::Shipped,
            'tracking_number' => 'JNE-PERSIST-123',
        ]);

        $this->actingAs($this->admin())->patch('/admin/orders/'.$order->id, [
            'fulfillment_status' => 'completed',
            'tracking_number' => '',
        ])->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame(FulfillmentStatus::Completed, $order->refresh()->fulfillment_status);
        $this->assertSame('JNE-PERSIST-123', $order->tracking_number);
    }

    public function test_invalid_payment_filter_is_rejected(): void
    {
        $this->actingAs($this->admin())->get('/admin/orders?payment_status=refunded')
            ->assertSessionHasErrors(['payment_status']);
    }

    /** @return array<string, array<string, float|int>> */
    private function customSizePayload(): array
    {
        return [
            'right_hand' => ['jempol' => 14, 'telunjuk' => 10, 'tengah' => 11, 'manis' => 9.5, 'kelingking' => 8.5],
            'left_hand' => ['jempol' => 13.5, 'telunjuk' => 10.5, 'tengah' => 11.5, 'manis' => 9, 'kelingking' => 8],
        ];
    }

    private function admin(): User
    {
        return User::factory()->withRole(UserRole::Admin)->create();
    }
}
