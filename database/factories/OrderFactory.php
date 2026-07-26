<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FulfillmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(100000, 500000);
        $shippingCost = $this->faker->numberBetween(10000, 30000);

        return [
            'id' => 'ORD-'.now()->format('Ymd').'-'.Str::ulid(),
            'user_id' => null,
            'guest_id' => (string) Str::uuid(),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
            'customer_phone' => '081234567890',
            'shipping_address' => $this->faker->address(),
            'province_id' => '6',
            'city_id' => '152',
            'courier' => 'jne:REG',
            'shipping_cost' => $shippingCost,
            'subtotal' => $subtotal,
            'grand_total' => $subtotal + $shippingCost,
            'payment_status' => PaymentStatus::Pending,
            'fulfillment_status' => FulfillmentStatus::Pending,
            'tracking_number' => null,
            'snap_token' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (): array => ['payment_status' => PaymentStatus::Paid]);
    }
}
