<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SizeStandard;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use RuntimeException;

class NailClassifierService
{
    private const DISTANCE_EPSILON = 1.0E-9;

    /** @var list<string> */
    private const FINGERS = [
        'jempol',
        'telunjuk',
        'tengah',
        'manis',
        'kelingking',
    ];

    /**
     * Classify one hand independently with database-backed L1 distance.
     *
     * @param  array<string, int|float|string>  $inputData
     * @return array{size: string, confidence: float}
     */
    public function classifyHand(array $inputData): array
    {
        $measurements = $this->normalizeMeasurements($inputData);
        $standards = $this->activeStandards();
        $bestMatch = null;
        $smallestDifference = INF;
        $smallestStandardWidth = INF;

        foreach ($standards as $standard) {
            $difference = $this->calculateDistance($measurements, $standard);
            $standardWidth = $this->calculateStandardWidth($standard);
            $isCloser = $difference < ($smallestDifference - self::DISTANCE_EPSILON);
            $isSaferTie = $this->distancesAreEqual($difference, $smallestDifference)
                && $standardWidth < $smallestStandardWidth;

            if ($isCloser || $isSaferTie) {
                $bestMatch = $standard;
                $smallestDifference = $difference;
                $smallestStandardWidth = $standardWidth;
            }
        }

        if (! $bestMatch instanceof SizeStandard) {
            throw new RuntimeException('No active nail size standards are available.');
        }

        $size = $smallestDifference > ((float) $bestMatch->tolerance + self::DISTANCE_EPSILON)
            ? 'Custom'
            : $bestMatch->size_name;

        return [
            'size' => $size,
            'confidence' => round(max(0.0, 100.0 - (5.0 * $smallestDifference)), 2),
        ];
    }

    /**
     * @return Collection<int, SizeStandard>
     */
    private function activeStandards(): Collection
    {
        $standards = SizeStandard::query()
            ->where('is_active', true)
            ->get();

        if ($standards->isEmpty()) {
            throw new RuntimeException('No active nail size standards are available.');
        }

        return $standards;
    }

    /**
     * @param  array<string, int|float|string>  $inputData
     * @return array{jempol: float, telunjuk: float, tengah: float, manis: float, kelingking: float}
     */
    private function normalizeMeasurements(array $inputData): array
    {
        $measurements = [];

        foreach (self::FINGERS as $finger) {
            if (! array_key_exists($finger, $inputData) || ! is_numeric($inputData[$finger])) {
                throw new InvalidArgumentException("The {$finger} measurement must be numeric.");
            }

            $measurement = (float) $inputData[$finger];

            if (! is_finite($measurement) || $measurement < 0.0 || $measurement > 25.0) {
                throw new InvalidArgumentException("The {$finger} measurement must be between 0 and 25 millimeters.");
            }

            $measurements[$finger] = $measurement;
        }

        /** @var array{jempol: float, telunjuk: float, tengah: float, manis: float, kelingking: float} $measurements */
        return $measurements;
    }

    /**
     * @param  array{jempol: float, telunjuk: float, tengah: float, manis: float, kelingking: float}  $measurements
     */
    private function calculateDistance(array $measurements, SizeStandard $standard): float
    {
        $distance = 0.0;

        foreach (self::FINGERS as $finger) {
            $distance += abs($measurements[$finger] - (float) $standard->getAttribute($finger));
        }

        return $distance;
    }

    private function calculateStandardWidth(SizeStandard $standard): float
    {
        $totalWidth = 0.0;

        foreach (self::FINGERS as $finger) {
            $totalWidth += (float) $standard->getAttribute($finger);
        }

        return $totalWidth;
    }

    private function distancesAreEqual(float $first, float $second): bool
    {
        return abs($first - $second) <= self::DISTANCE_EPSILON;
    }
}
