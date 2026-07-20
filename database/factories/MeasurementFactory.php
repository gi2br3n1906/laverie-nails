<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Measurement;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Measurement> */
class MeasurementFactory extends Factory
{
    protected $model = Measurement::class;

    /**
     * @return array<string, array<string, float>|float|string|null>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'right_hand_data' => $this->hand(14.0, 10.0, 11.0, 10.0, 8.0),
            'left_hand_data' => null,
            'classified_size_right' => 'XS',
            'classified_size_left' => null,
            'confidence_score_right' => 100.0,
            'confidence_score_left' => null,
        ];
    }

    /**
     * @return array{jempol: float, telunjuk: float, tengah: float, manis: float, kelingking: float}
     */
    private function hand(float $jempol, float $telunjuk, float $tengah, float $manis, float $kelingking): array
    {
        return compact('jempol', 'telunjuk', 'tengah', 'manis', 'kelingking');
    }
}
