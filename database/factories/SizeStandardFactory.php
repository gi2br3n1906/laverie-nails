<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SizeStandard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SizeStandard>
 */
class SizeStandardFactory extends Factory
{
    protected $model = SizeStandard::class;

    /**
     * @return array<string, bool|float|string>
     */
    public function definition(): array
    {
        return [
            'size_name' => fake()->unique()->randomElement(['XS', 'S', 'M', 'L']),
            'jempol' => 14.0,
            'telunjuk' => 10.0,
            'tengah' => 11.0,
            'manis' => 10.0,
            'kelingking' => 8.0,
            'tolerance' => 1.0,
            'is_active' => true,
        ];
    }
}
