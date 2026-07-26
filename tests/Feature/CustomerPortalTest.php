<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\FulfillmentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_dashboard_lists_only_owned_orders_and_links_to_item_details(): void
    {
        $customer = User::factory()->create();
        $older = Order::factory()->for($customer)->paid()->create(['created_at' => now()->subDay()]);
        $latest = Order::factory()->for($customer)->create([
            'created_at' => now(),
            'fulfillment_status' => FulfillmentStatus::Shipped,
            'tracking_number' => 'JNE-CUSTOMER-123',
        ]);
        $foreign = Order::factory()->for(User::factory())->create();
        $guest = Order::factory()->create();
        OrderItem::factory()->for($latest)->create(['product_name' => 'Owned Azure Set']);

        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->actingAs($customer)->get('/dashboard')
            ->assertOk()
            ->assertSeeInOrder([$latest->id, $older->id])
            ->assertSeeText('JNE-CUSTOMER-123')
            ->assertDontSee($foreign->id)
            ->assertDontSee($guest->id);

        $this->actingAs($customer)->get('/dashboard/orders/'.$latest->id)
            ->assertOk()->assertSeeText('Owned Azure Set');
        $this->actingAs($customer)->get('/dashboard/orders/'.$foreign->id)->assertNotFound();
    }

    public function test_customer_can_save_canonical_ten_finger_measurement_profile(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->patch('/dashboard/measurements', [
            'custom_measurements' => $this->measurements(),
        ])->assertRedirect(route('dashboard'))->assertSessionHas('status');

        $customer->refresh();
        $this->assertIsArray($customer->default_size_payload);
        $this->assertSame(14, $customer->default_size_payload['right_hand']['jempol']);
        $this->assertSame(8.5, $customer->default_size_payload['left_hand']['kelingking']);
    }

    public function test_measurement_profile_requires_all_ten_bounded_values(): void
    {
        $customer = User::factory()->create();
        $measurements = $this->measurements();
        unset($measurements['left_hand']['manis']);
        $measurements['right_hand']['jempol'] = 26;

        $this->actingAs($customer)->from('/dashboard')->patch('/dashboard/measurements', [
            'custom_measurements' => $measurements,
        ])->assertRedirect('/dashboard')->assertSessionHasErrors([
            'custom_measurements.left_hand.manis',
            'custom_measurements.right_hand.jempol',
        ]);
        $this->assertNull($customer->refresh()->default_size_payload);
    }

    public function test_saved_measurements_prefill_editorial_product_custom_inputs(): void
    {
        $customer = User::factory()->create(['default_size_payload' => $this->measurements()]);
        $product = Product::factory()->create(['is_active' => true]);

        $this->actingAs($customer)->get(route('storefront.products.show', $product))
            ->assertOk()
            ->assertSee('name="custom_measurements[right_hand][jempol]"', false)
            ->assertSee('value="14.0"', false)
            ->assertSee('value="8.5"', false)
            ->assertSeeText('Ukuran tersimpan telah diisi otomatis.');
    }

    /** @return array<string, array<string, float|int|string>> */
    private function measurements(): array
    {
        return [
            'right_hand' => ['jempol' => '14.0', 'telunjuk' => 10, 'tengah' => 11, 'manis' => 9.5, 'kelingking' => 8],
            'left_hand' => ['jempol' => 13.5, 'telunjuk' => 10.5, 'tengah' => 11.5, 'manis' => 9, 'kelingking' => 8.5],
        ];
    }
}
