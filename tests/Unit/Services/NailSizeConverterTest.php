<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\NailSizeConverter;
use PHPUnit\Framework\TestCase;

class NailSizeConverterTest extends TestCase
{
    /**
     * @return array<string, array{float, int}>
     */
    public static function mmToTipProvider(): array
    {
        return [
            '18mm maps to zero' => [18.0, 0],
            '17mm maps to one' => [17.0, 1],
            '16mm maps to two' => [16.0, 2],
            '15mm maps to three' => [15.0, 3],
            '14mm maps to four' => [14.0, 4],
            '13.5mm maps to five' => [13.5, 5],
            '13mm maps to six' => [13.0, 6],
            '12.5mm maps to seven' => [12.5, 7],
            '12mm maps to eight' => [12.0, 8],
            '11.5mm maps to nine' => [11.5, 9],
            '11mm maps to ten' => [11.0, 10],
            '10mm maps to eleven' => [10.0, 11],
            '9.5mm maps to twelve' => [9.5, 12],
            '9mm maps to thirteen' => [9.0, 13],
            '8mm maps to fourteen' => [8.0, 14],
        ];
    }

    /**
     * @dataProvider mmToTipProvider
     */
    public function test_it_maps_known_millimeter_measurements_to_tip_numbers(float $mm, int $expectedTip): void
    {
        $converter = new NailSizeConverter;

        $this->assertSame($expectedTip, $converter->toTipNumber($mm));
    }

    public function test_it_returns_the_nearest_tip_number_and_prefers_the_smaller_number_on_a_tie(): void
    {
        $converter = new NailSizeConverter;

        // 13.25 is equidistant between 13.5 (tip 5) and 13.0 (tip 6): smaller tip number wins.
        $this->assertSame(5, $converter->toTipNumber(13.25));
        $this->assertSame(3, $converter->toTipNumber(14.9));
    }

    public function test_it_clamps_measurements_outside_the_lookup_range_to_the_nearest_endpoint(): void
    {
        $converter = new NailSizeConverter;

        $this->assertSame(0, $converter->toTipNumber(25.0));
        $this->assertSame(14, $converter->toTipNumber(2.0));
    }
}
