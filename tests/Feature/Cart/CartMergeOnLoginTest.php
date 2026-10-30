<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartMergeOnLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_merges_a_duplicate_guest_row_into_the_account_row(): void
    {
        $user = User::factory()->create(['password' => 'secure-password']);
        $product = Product::factory()->create(['stock' => 10]);

        $this->post(route('cart.store'), $this->payload($product, 2, 'M'))->assertRedirect('/');
        $guest = CartItem::query()->sole();
        $this->createAccountRow($user, $guest, quantity: 1);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'secure-password',
        ])->assertRedirect(route('dashboard'));

        $items = CartItem::query()->where('user_id', $user->id)->get();

        $this->assertCount(1, $items);
        $this->assertSame(3, $items->sole()->quantity);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_merge_clamps_the_combined_quantity_to_available_stock(): void
    {
        $user = User::factory()->create(['password' => 'secure-password']);
        $product = Product::factory()->create(['stock' => 3]);

        $this->post(route('cart.store'), $this->payload($product, 2, 'M'))->assertRedirect('/');
        $guest = CartItem::query()->sole();
        $this->createAccountRow($user, $guest, quantity: 2);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'secure-password',
        ])->assertRedirect(route('dashboard'));

        $items = CartItem::query()->where('user_id', $user->id)->get();

        $this->assertCount(1, $items);
        $this->assertSame(3, $items->sole()->quantity);
    }

    public function test_merge_keeps_rows_with_different_sizes_separate(): void
    {
        $user = User::factory()->create(['password' => 'secure-password']);
        $product = Product::factory()->create(['stock' => 10]);

        $this->post(route('cart.store'), $this->payload($product, 1, 'M'))->assertRedirect('/');
        $guest = CartItem::query()->sole();
        $this->createAccountRow($user, $guest, quantity: 1, size: 'S');

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'secure-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame(2, CartItem::query()->where('user_id', $user->id)->count());
        $this->assertSame(2, CartItem::query()->where('user_id', $user->id)->sum('quantity'));
    }

    public function test_merge_drops_the_row_when_the_product_is_out_of_stock(): void
    {
        $user = User::factory()->create(['password' => 'secure-password']);
        $product = Product::factory()->create(['stock' => 0]);
        $payload = ['size' => 'M', 'length' => 'Medium'];

        $guest = CartItem::query()->create([
            'user_id' => null,
            'session_id' => 'zero-stock-guest',
            'product_id' => $product->id,
            'quantity' => 1,
            'size_type' => 'standard',
            'size_payload' => $payload,
            'size_signature' => hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)),
        ]);
        $this->createAccountRow($user, $guest, quantity: 1);

        $this->withSession(['cart.guest_id' => 'zero-stock-guest'])->post(route('login'), [
            'email' => $user->email,
            'password' => 'secure-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame(0, CartItem::query()->count());
    }

    private function createAccountRow(
        User $user,
        CartItem $guest,
        int $quantity,
        ?string $size = null,
    ): CartItem {
        $payload = $size === null ? $guest->size_payload : ['size' => $size, 'length' => 'Medium'];

        return CartItem::query()->create([
            'user_id' => $user->id,
            'session_id' => null,
            'product_id' => $guest->product_id,
            'quantity' => $quantity,
            'size_type' => $guest->size_type,
            'size_payload' => $payload,
            'size_signature' => $size === null
                ? $guest->size_signature
                : hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)),
        ]);
    }

    /** @return array<string, int|string> */
    private function payload(Product $product, int $quantity, string $size): array
    {
        return [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'size_type' => 'standard',
            'standard_size' => $size,
        ];
    }
}
