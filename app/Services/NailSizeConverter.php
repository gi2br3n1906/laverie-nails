<?php

declare(strict_types=1);

namespace App\Services;

class NailSizeConverter
{
    /**
     * Ordered [millimeter, tip number] lookup pairs, largest to smallest.
     *
     * @var list<array{float, int}>
     */
    private const LOOKUP = [
        [18.0, 0],
        [17.0, 1],
        [16.0, 2],
        [15.0, 3],
        [14.0, 4],
        [13.5, 5],
        [13.0, 6],
        [12.5, 7],
        [12.0, 8],
        [11.5, 9],
        [11.0, 10],
        [10.0, 11],
        [9.5, 12],
        [9.0, 13],
        [8.0, 14],
    ];

    public function toTipNumber(float $mm): int
    {
        $bestTip = self::LOOKUP[0][1];
        $bestDistance = PHP_FLOAT_MAX;

        foreach (self::LOOKUP as [$referenceMm, $tip]) {
            $distance = abs($mm - $referenceMm);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestTip = $tip;
            }
        }

        return $bestTip;
    }
}
