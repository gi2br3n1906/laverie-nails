<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\FulfillmentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_tracking_form_renders_with_premium_compliant_source(): void
    {
        $response = $this->get('/lacak-pesanan');

        $response->assertOk()
            ->assertSee('Lacak Pesanan')
            ->assertSee('order_id', false)
            ->assertSee('email', false);

        $source = file_get_contents(resource_path('views/orders/tracking.blade.php'));
        $this->assertIsString($source);
        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $source);
        $this->assertDoesNotMatchRegularExpression('/style\s*=/i', $source);
        $this->assertDoesNotMatchRegularExpression('/(?:bg|text|border|ring|from|via|to)-(?:pink|red|black)(?:-|\b)/', $source);
    }

    public function test_matching_order_id_and_email_displays_tracking_summary_and_resi(): void
    {
        $order = Order::factory()->paid()->create([
            'customer_email' => 'customer@example.com',
            'fulfillment_status' => FulfillmentStatus::Shipped,
            'tracking_number' => 'JNT-TRACK-445566',
        ]);
        OrderItem::factory()->for($order)->create(['product_name' => 'Cloud French Set']);

        $this->post('/lacak-pesanan', [
            'order_id' => strtolower($order->id),
            'email' => 'CUSTOMER@EXAMPLE.COM',
        ])->assertOk()
            ->assertSeeText($order->id)
            ->assertSeeText('Cloud French Set')
            ->assertSeeText('Paid')
            ->assertSeeText('Shipped')
            ->assertSeeText('JNT-TRACK-445566');
    }

    public function test_invalid_order_or_email_returns_one_generic_error_without_exposing_order_data(): void
    {
        $order = Order::factory()->paid()->create([
            'customer_email' => 'private@example.com',
            'tracking_number' => 'SECRET-RESI-123',
        ]);

        foreach ([
            ['order_id' => $order->id, 'email' => 'wrong@example.com'],
            ['order_id' => 'ORD-20990101-01ARZ3NDEKTSV4RRFFQ69G5FAV', 'email' => 'private@example.com'],
        ] as $credentials) {
            $this->post('/lacak-pesanan', $credentials)
                ->assertOk()
                ->assertSeeText('Pesanan tidak ditemukan. Periksa kembali Order ID dan email Anda.')
                ->assertDontSeeText('SECRET-RESI-123')
                ->assertDontSeeText($order->shipping_address);
        }
    }

    public function test_tracking_lookup_requires_well_formed_order_id_and_email(): void
    {
        $this->from('/lacak-pesanan')->post('/lacak-pesanan', [
            'order_id' => 'not-an-order',
            'email' => 'not-an-email',
        ])->assertRedirect('/lacak-pesanan')->assertSessionHasErrors(['order_id', 'email']);
    }
}
