<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CartSizeType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderItem> */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => $this->faker->words(3, true),
            'product_price' => $this->faker->numberBetween(100000, 500000),
            'quantity' => 1,
            'size_type' => CartSizeType::Standard,
            'size_payload' => ['size' => 'M'],
        ];
    }
}
