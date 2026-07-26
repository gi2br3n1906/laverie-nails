<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\HeroBanner;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HeroBanner> */
class HeroBannerFactory extends Factory
{
    protected $model = HeroBanner::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'image_path' => 'banners/'.$this->faker->uuid().'.jpg',
            'is_active' => true,
            'sequence' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
