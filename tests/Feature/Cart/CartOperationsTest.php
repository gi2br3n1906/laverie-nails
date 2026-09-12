<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_a_standard_size_product_to_the_cart(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->post('/cart-items', [
            'product_id' => $product->id,
            'size_type' => 'standard',
            'standard_size' => 'M',
        ])->assertRedirect('/');

        $cartItem = CartItem::query()->sole();

        $this->assertNull($cartItem->user_id);
        $this->assertNotEmpty($cartItem->session_id);
        $this->assertSame($product->id, $cartItem->product_id);
        $this->assertSame(1, $cartItem->quantity);
        $this->assertSame('standard', $cartItem->size_type->value);
        $this->assertSame(['size' => 'M'], $cartItem->size_payload);
        $this->assertSame(64, strlen($cartItem->size_signature));
    }

    public function test_authenticated_user_cart_item_has_an_exclusive_user_owner(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 4]);

        $this->actingAs($user)->post('/cart-items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'size_type' => 'standard',
            'standard_size' => 'S',
        ])->assertRedirect('/');

        $cartItem = CartItem::query()->sole();

        $this->assertSame($user->id, $cartItem->user_id);
        $this->assertNull($cartItem->session_id);
        $this->assertSame(2, $cartItem->quantity);
    }

    public function test_same_product_and_canonical_standard_size_increment_one_row_while_different_size_is_separate(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->post('/cart-items', $this->standardPayload($product, 'M', 2))->assertRedirect('/');
        $this->post('/cart-items', $this->standardPayload($product, 'M'))->assertRedirect('/');
        $this->post('/cart-items', $this->standardPayload($product, 'L'))->assertRedirect('/');

        $this->assertDatabaseCount('cart_items', 2);
        $this->assertSame(3, CartItem::query()->whereJsonContains('size_payload->size', 'M')->sole()->quantity);
        $this->assertSame(1, CartItem::query()->whereJsonContains('size_payload->size', 'L')->sole()->quantity);
    }

    public function test_custom_measurements_are_canonicalized_saved_and_retrieved_for_all_ten_fingers(): void
    {
        $product = Product::factory()->create(['stock' => 4]);
        $measurements = $this->customMeasurements('10.00');

        $this->post('/cart-items', $this->customPayload($product, $measurements))->assertRedirect('/');

        $cartItem = CartItem::query()->sole();

        $this->assertSame('custom', $cartItem->size_type->value);
        $this->assertSame([
            'right_hand' => [
                'jempol' => 10,
                'telunjuk' => 10,
                'tengah' => 10,
                'manis' => 10,
                'kelingking' => 10,
            ],
            'left_hand' => [
                'jempol' => 10,
                'telunjuk' => 10,
                'tengah' => 10,
                'manis' => 10,
                'kelingking' => 10,
            ],
        ], $cartItem->size_payload);

        $numericEquivalent = $this->customMeasurements(10.0);
        $this->post('/cart-items', $this->customPayload($product, $numericEquivalent))->assertRedirect('/');

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame(2, $cartItem->refresh()->quantity);
    }

    public function test_size_selection_and_all_custom_measurements_are_required_and_bounded(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->post('/cart-items', ['product_id' => $product->id])
            ->assertSessionHasErrors(['size_type']);

        $this->post('/cart-items', [
            'product_id' => $product->id,
            'size_type' => 'standard',
        ])->assertSessionHasErrors(['standard_size']);

        $this->post('/cart-items', [
            'product_id' => $product->id,
            'size_type' => 'standard',
            'standard_size' => 'XL',
        ])->assertSessionHasErrors(['standard_size']);

        $missingFinger = $this->customMeasurements(10);
        unset($missingFinger['left_hand']['kelingking']);
        $this->post('/cart-items', $this->customPayload($product, $missingFinger))
            ->assertSessionHasErrors(['custom_measurements.left_hand.kelingking']);

        $outOfRange = $this->customMeasurements(10);
        $outOfRange['right_hand']['jempol'] = 25.1;
        $this->post('/cart-items', $this->customPayload($product, $outOfRange))
            ->assertSessionHasErrors(['custom_measurements.right_hand.jempol']);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_inactive_zero_stock_and_insufficient_stock_products_cannot_be_added(): void
    {
        $inactive = Product::factory()->inactive()->create(['stock' => 5]);
        $empty = Product::factory()->create(['stock' => 0]);
        $limited = Product::factory()->create(['stock' => 2]);

        $this->post('/cart-items', $this->standardPayload($inactive, 'M'))
            ->assertSessionHasErrors(['product_id']);
        $this->post('/cart-items', $this->standardPayload($empty, 'M'))
            ->assertSessionHasErrors(['quantity']);
        $this->post('/cart-items', $this->standardPayload($limited, 'M', 3))
            ->assertSessionHasErrors(['quantity']);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_combined_quantity_for_a_duplicate_item_cannot_exceed_current_stock(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        $this->post('/cart-items', $this->standardPayload($product, 'XS', 2))->assertRedirect('/');
        $this->post('/cart-items', $this->standardPayload($product, 'XS'))
            ->assertSessionHasErrors(['quantity']);

        $this->assertSame(2, CartItem::query()->sole()->quantity);
    }

    public function test_owner_can_update_quantity_within_stock_and_remove_an_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 3]);
        $this->actingAs($user)->post('/cart-items', $this->standardPayload($product, 'M'));
        $cartItem = CartItem::query()->sole();

        $this->actingAs($user)->patch('/cart-items/'.$cartItem->id, ['quantity' => 3])
            ->assertRedirect('/');
        $this->assertSame(3, $cartItem->refresh()->quantity);

        $this->actingAs($user)->patch('/cart-items/'.$cartItem->id, ['quantity' => 4])
            ->assertSessionHasErrors(['quantity']);
        $this->assertSame(3, $cartItem->refresh()->quantity);

        $this->actingAs($user)->delete('/cart-items/'.$cartItem->id)
            ->assertRedirect('/');
        $this->assertModelMissing($cartItem);
    }

    public function test_another_authenticated_user_cannot_update_or_remove_a_cart_item(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);
        $this->actingAs($owner)->post('/cart-items', $this->standardPayload($product, 'L'));
        $cartItem = CartItem::query()->sole();

        $this->actingAs($intruder)->patch('/cart-items/'.$cartItem->id, ['quantity' => 2])->assertNotFound();
        $this->actingAs($intruder)->delete('/cart-items/'.$cartItem->id)->assertNotFound();

        $this->assertSame(1, $cartItem->refresh()->quantity);
    }

    public function test_another_guest_session_cannot_update_or_remove_a_cart_item(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post('/cart-items', $this->standardPayload($product, 'S'));
        $cartItem = CartItem::query()->sole();

        $this->withSession(['cart.guest_id' => 'another-browser-session'])
            ->patch('/cart-items/'.$cartItem->id, ['quantity' => 2])
            ->assertNotFound();
        $this->withSession(['cart.guest_id' => 'another-browser-session'])
            ->delete('/cart-items/'.$cartItem->id)
            ->assertNotFound();

        $this->assertSame(1, $cartItem->refresh()->quantity);
    }

    public function test_authenticated_and_guest_carts_are_isolated(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post('/cart-items', $this->standardPayload($product, 'M'));

        $this->actingAs(User::factory()->create())->getJson(route('cart.state'))
            ->assertOk()
            ->assertJsonPath('data.quantity', 0)
            ->assertJsonCount(0, 'data.items');
    }

    public function test_deleting_a_product_or_user_cascades_their_cart_items(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);
        $this->actingAs($user)->post('/cart-items', $this->standardPayload($product, 'M'));

        $product->delete();
        $this->assertDatabaseCount('cart_items', 0);

        $secondProduct = Product::factory()->create(['stock' => 5]);
        $this->actingAs($user)->post('/cart-items', $this->standardPayload($secondProduct, 'S'));
        $user->delete();
        $this->assertDatabaseCount('cart_items', 0);
    }

    /** @return array<string, int|string> */
    private function standardPayload(Product $product, string $size, int $quantity = 1): array
    {
        return [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'size_type' => 'standard',
            'standard_size' => $size,
        ];
    }

    /** @param  array<string, array<string, float|int|string>>  $measurements
     * @return array<string, mixed>
     */
    private function customPayload(Product $product, array $measurements): array
    {
        return [
            'product_id' => $product->id,
            'quantity' => 1,
            'size_type' => 'custom',
            'custom_measurements' => $measurements,
        ];
    }

    /** @return array<string, array<string, float|int|string>> */
    private function customMeasurements(float|int|string $value): array
    {
        return [
            'right_hand' => [
                'jempol' => $value,
                'telunjuk' => $value,
                'tengah' => $value,
                'manis' => $value,
                'kelingking' => $value,
            ],
            'left_hand' => [
                'jempol' => $value,
                'telunjuk' => $value,
                'tengah' => $value,
                'manis' => $value,
                'kelingking' => $value,
            ],
        ];
    }
}
