<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CatalogSize;
use App\Models\NailCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NailCatalog> */
class NailCatalogFactory extends Factory
{
    protected $model = NailCatalog::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'images' => ['catalogs/placeholder.jpg'],
            'price' => fake()->numberBetween(100000, 500000),
            'size' => fake()->randomElement(CatalogSize::cases()),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
