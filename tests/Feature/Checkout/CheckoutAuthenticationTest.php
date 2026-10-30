<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_must_log_in_before_checkout_and_returns_with_their_cart(): void
    {
        $user = User::factory()->create(['password' => 'secure-password']);
        $product = Product::factory()->create([
            'name' => 'Checkout Login Test Set',
            'stock' => 5,
        ]);

        $this->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'size_type' => 'standard',
            'standard_size' => 'M',
        ])->assertRedirect('/');

        $guestCartItem = CartItem::query()->sole();

        $this->get(route('checkout.create'))->assertRedirect(route('login'));
        $this->post(route('checkout.store'), [
            'customer_name' => 'Checkout Guest',
            'customer_email' => 'checkout@example.test',
            'customer_phone' => '081234567890',
            'shipping_address' => 'Jl. Melati No. 7, Jakarta Pusat',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', [
            'id' => $guestCartItem->id,
            'user_id' => null,
            'quantity' => 1,
        ]);
        $this->assertSame(5, $product->refresh()->stock);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'secure-password',
        ])->assertRedirect(route('checkout.create'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('cart_items', [
            'id' => $guestCartItem->id,
            'user_id' => $user->id,
            'session_id' => null,
        ]);
        $this->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('Checkout Login Test Set');
    }

    public function test_guest_can_register_from_checkout_and_returns_with_their_cart(): void
    {
        $product = Product::factory()->create([
            'name' => 'Checkout Registration Test Set',
            'stock' => 5,
        ]);

        $this->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'size_type' => 'standard',
            'standard_size' => 'S',
        ])->assertRedirect('/');

        $guestCartItem = CartItem::query()->sole();
        $this->get(route('checkout.create'))->assertRedirect(route('login'));

        $this->post(route('register'), [
            'name' => 'Checkout Registration Customer',
            'email' => 'checkout-register@example.test',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertRedirect(route('checkout.create'));

        $user = User::query()->where('email', 'checkout-register@example.test')->sole();
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('cart_items', [
            'id' => $guestCartItem->id,
            'user_id' => $user->id,
            'session_id' => null,
        ]);

        $this->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('Checkout Registration Test Set');
    }
}
