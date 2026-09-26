<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_applying_an_active_coupon_discounts_the_drawer_total(): void
    {
        $product = Product::factory()->create(['stock' => 5, 'price' => '100000.00']);
        Coupon::query()->create(['code' => 'SAVE10', 'discount_percentage' => 10, 'is_active' => true]);

        $this->post('/cart-items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'size_type' => 'standard',
            'standard_size' => 'M',
        ]);

        $this->postJson('/cart-coupon', ['code' => ' save10 '])
            ->assertOk()
            ->assertJsonPath('data.coupon.code', 'SAVE10')
            ->assertJsonPath('data.coupon.discount_percentage', 10)
            ->assertJsonPath('data.subtotal', 20000000)
            ->assertJsonPath('data.discount', 2000000)
            ->assertJsonPath('data.total', 18000000);
    }

    public function test_unknown_or_inactive_coupon_is_rejected(): void
    {
        $product = Product::factory()->create(['stock' => 5, 'price' => '50000.00']);
        Coupon::query()->create(['code' => 'OFF', 'discount_percentage' => 15, 'is_active' => false]);

        $this->post('/cart-items', [
            'product_id' => $product->id,
            'quantity' => 1,
            'size_type' => 'standard',
            'standard_size' => 'M',
        ]);

        $this->postJson('/cart-coupon', ['code' => 'NOPE'])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('code');

        $this->postJson('/cart-coupon', ['code' => 'OFF'])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('code');

        $this->getJson('/cart-state')->assertJsonPath('data.coupon', null);
    }

    public function test_removing_a_coupon_restores_the_full_total(): void
    {
        $product = Product::factory()->create(['stock' => 5, 'price' => '100000.00']);
        Coupon::query()->create(['code' => 'SAVE10', 'discount_percentage' => 10, 'is_active' => true]);

        $this->post('/cart-items', [
            'product_id' => $product->id,
            'quantity' => 1,
            'size_type' => 'standard',
            'standard_size' => 'M',
        ]);
        $this->postJson('/cart-coupon', ['code' => 'SAVE10'])->assertOk();

        $this->deleteJson('/cart-coupon')
            ->assertOk()
            ->assertJsonPath('data.coupon', null)
            ->assertJsonPath('data.discount', 0)
            ->assertJsonPath('data.total', 10000000);
    }

    public function test_discount_only_counts_selected_items(): void
    {
        $a = Product::factory()->create(['stock' => 5, 'price' => '100000.00']);
        $b = Product::factory()->create(['stock' => 5, 'price' => '50000.00']);
        Coupon::query()->create(['code' => 'SAVE10', 'discount_percentage' => 10, 'is_active' => true]);

        $this->post('/cart-items', ['product_id' => $a->id, 'quantity' => 1, 'size_type' => 'standard', 'standard_size' => 'M']);
        $this->post('/cart-items', ['product_id' => $b->id, 'quantity' => 1, 'size_type' => 'standard', 'standard_size' => 'S']);

        $second = CartItem::query()->where('product_id', $b->id)->sole();
        $this->patchJson("/cart-items/{$second->id}/selection", ['is_selected' => false])->assertOk();

        $this->postJson('/cart-coupon', ['code' => 'SAVE10'])
            ->assertOk()
            ->assertJsonPath('data.subtotal', 10000000)
            ->assertJsonPath('data.discount', 1000000)
            ->assertJsonPath('data.total', 9000000);
    }
}
