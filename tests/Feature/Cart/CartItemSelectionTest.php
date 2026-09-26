<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartItemSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_items_are_selected_by_default(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->post('/cart-items', [
            'product_id' => $product->id,
            'size_type' => 'standard',
            'standard_size' => 'M',
        ])->assertRedirect('/');

        $this->assertTrue(CartItem::query()->sole()->is_selected);
    }

    public function test_owner_can_toggle_an_item_selection_and_state_reflects_only_selected_items(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'price' => 50000]);
        $other = Product::factory()->create(['stock' => 10, 'price' => 75000]);

        $this->post('/cart-items', [
            'product_id' => $product->id,
            'size_type' => 'standard',
            'standard_size' => 'M',
        ]);
        $this->post('/cart-items', [
            'product_id' => $other->id,
            'size_type' => 'standard',
            'standard_size' => 'S',
        ]);

        $first = CartItem::query()->where('product_id', $product->id)->sole();

        $this->patchJson("/cart-items/{$first->id}/selection", ['is_selected' => false])
            ->assertOk()
            ->assertJsonPath('data.quantity', 1)
            ->assertJsonPath('data.total', 7500000);

        $this->assertFalse($first->refresh()->is_selected);

        $this->patchJson("/cart-items/{$first->id}/selection", ['is_selected' => true])
            ->assertOk()
            ->assertJsonPath('data.quantity', 2);

        $this->assertTrue($first->refresh()->is_selected);
    }

    public function test_selection_toggle_rejects_items_owned_by_another_session(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $foreign = CartItem::query()->create([
            'product_id' => $product->id,
            'session_id' => 'someone-elses-session',
            'quantity' => 1,
            'size_type' => 'standard',
            'size_payload' => ['size' => 'M', 'length' => 'Medium'],
            'size_signature' => hash('sha256', 'foreign'),
        ]);

        $this->patchJson("/cart-items/{$foreign->id}/selection", ['is_selected' => false])
            ->assertNotFound();
    }
}
