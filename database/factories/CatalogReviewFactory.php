<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CatalogReview;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CatalogReview> */
class CatalogReviewFactory extends Factory
{
    protected $model = CatalogReview::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->sentence(),
        ];
    }
}
