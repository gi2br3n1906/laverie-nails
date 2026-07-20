<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\SizeStandard;
use App\Services\NailClassifierService;
use Database\Seeders\SizeStandardSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class NailClassifierServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_exact_metrics_return_the_database_size_with_full_confidence(): void
    {
        $this->seed(SizeStandardSeeder::class);

        $result = $this->service()->classifyHand([
            'jempol' => 16.0,
            'telunjuk' => 12.0,
            'tengah' => 13.0,
            'manis' => 12.0,
            'kelingking' => 10.0,
        ]);

        $this->assertSame([
            'size' => 'M',
            'confidence' => 100.0,
        ], $result);
    }

    public function test_manhattan_distance_sums_absolute_differences_across_all_five_fingers(): void
    {
        $this->createStandard('Five finger standard', 10.0, 20.0);

        $result = $this->service()->classifyHand([
            'jempol' => 11.0,
            'telunjuk' => 8.0,
            'tengah' => 13.0,
            'manis' => 6.0,
            'kelingking' => 15.0,
        ]);

        $this->assertSame('Five finger standard', $result['size']);
        $this->assertSame(25.0, $result['confidence']);
    }

    public function test_only_active_database_standards_are_considered(): void
    {
        $this->createStandard('Inactive exact match', 10.0, 10.0, false);
        $this->createStandard('Active database match', 11.0, 10.0);

        $result = $this->service()->classifyHand($this->uniformHand(10.0));

        $this->assertSame('Active database match', $result['size']);
        $this->assertSame(75.0, $result['confidence']);
    }

    public function test_standards_are_queried_at_runtime_for_every_hand(): void
    {
        $firstStandard = $this->createStandard('First active standard', 10.0, 10.0);
        $service = $this->service();

        $firstResult = $service->classifyHand($this->uniformHand(10.0));

        $firstStandard->update(['is_active' => false]);
        $this->createStandard('Newly active standard', 10.0, 10.0);

        $secondResult = $service->classifyHand($this->uniformHand(10.0));

        $this->assertSame('First active standard', $firstResult['size']);
        $this->assertSame('Newly active standard', $secondResult['size']);
    }

    public function test_equal_distances_fall_back_to_the_smaller_database_standard(): void
    {
        $this->createStandard('A larger standard', 12.0, 10.0);
        $this->createStandard('Z smaller and safer', 10.0, 10.0);

        $result = $this->service()->classifyHand($this->uniformHand(11.0));

        $this->assertSame('Z smaller and safer', $result['size']);
        $this->assertSame(75.0, $result['confidence']);
    }

    public function test_decimal_midpoint_ties_are_not_changed_by_floating_point_noise(): void
    {
        $this->createStandard('Smaller decimal standard', 10.2, 10.0);
        $this->createStandard('Larger decimal standard', 10.4, 10.0);

        $result = $this->service()->classifyHand($this->uniformHand(10.3));

        $this->assertSame('Smaller decimal standard', $result['size']);
        $this->assertSame(97.5, $result['confidence']);
    }

    public function test_canonical_xs_and_s_tie_falls_back_to_xs(): void
    {
        $this->seed(SizeStandardSeeder::class);
        SizeStandard::query()->update(['tolerance' => 3.0]);

        $result = $this->service()->classifyHand([
            'jempol' => 14.5,
            'telunjuk' => 10.5,
            'tengah' => 11.5,
            'manis' => 10.5,
            'kelingking' => 8.5,
        ]);

        $this->assertSame('XS', $result['size']);
        $this->assertSame(87.5, $result['confidence']);
    }

    public function test_distance_equal_to_tolerance_remains_a_standard_size(): void
    {
        $this->createStandard('Boundary size', 10.0, 1.0);
        $input = $this->uniformHand(10.0);
        $input['jempol'] = 11.0;

        $result = $this->service()->classifyHand($input);

        $this->assertSame('Boundary size', $result['size']);
        $this->assertSame(95.0, $result['confidence']);
    }

    public function test_decimal_distance_equal_to_tolerance_is_not_changed_by_floating_point_noise(): void
    {
        $this->createStandard('Decimal boundary size', 10.0, 0.1);
        $input = $this->uniformHand(10.0);
        $input['jempol'] = 10.1;

        $result = $this->service()->classifyHand($input);

        $this->assertSame('Decimal boundary size', $result['size']);
        $this->assertSame(99.5, $result['confidence']);
    }

    public function test_distance_strictly_greater_than_tolerance_returns_custom(): void
    {
        $this->createStandard('Closest database size', 10.0, 1.0);
        $input = $this->uniformHand(10.0);
        $input['jempol'] = 11.1;

        $result = $this->service()->classifyHand($input);

        $this->assertSame('Custom', $result['size']);
        $this->assertSame(94.5, $result['confidence']);
    }

    public function test_custom_cutoff_uses_the_winning_standard_tolerance(): void
    {
        $this->createStandard('Closest strict standard', 10.0, 0.5);
        $this->createStandard('Far permissive standard', 20.0, 25.0);
        $input = $this->uniformHand(10.0);
        $input['jempol'] = 10.6;

        $result = $this->service()->classifyHand($input);

        $this->assertSame('Custom', $result['size']);
        $this->assertSame(97.0, $result['confidence']);
    }

    public function test_confidence_never_falls_below_zero(): void
    {
        $this->createStandard('Far database size', 10.0, 1.0);

        $result = $this->service()->classifyHand($this->uniformHand(25.0));

        $this->assertSame('Custom', $result['size']);
        $this->assertSame(0.0, $result['confidence']);
    }

    public function test_right_and_left_hands_are_classified_independently(): void
    {
        $this->createStandard('Right hand fit', 10.0, 1.0);
        $this->createStandard('Left hand fit', 14.0, 1.0);
        $service = $this->service();

        $rightResult = $service->classifyHand($this->uniformHand(10.0));
        $leftInput = $this->uniformHand(14.0);
        $leftInput['kelingking'] = 14.5;
        $leftResult = $service->classifyHand($leftInput);

        $this->assertSame([
            'size' => 'Right hand fit',
            'confidence' => 100.0,
        ], $rightResult);
        $this->assertSame([
            'size' => 'Left hand fit',
            'confidence' => 97.5,
        ], $leftResult);
    }

    public function test_classification_fails_explicitly_when_no_active_standards_exist(): void
    {
        $this->createStandard('Inactive standard', 10.0, 1.0, false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No active nail size standards are available.');

        $this->service()->classifyHand($this->uniformHand(10.0));
    }

    /** @param array<string, mixed> $input */
    #[DataProvider('invalidHandDataProvider')]
    public function test_invalid_hand_payloads_are_rejected(array $input): void
    {
        $this->createStandard('Database size', 10.0, 1.0);

        $this->expectException(InvalidArgumentException::class);

        $this->service()->classifyHand($input);
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function invalidHandDataProvider(): array
    {
        return [
            'missing finger' => [[
                'jempol' => 10.0,
                'telunjuk' => 10.0,
                'tengah' => 10.0,
                'manis' => 10.0,
            ]],
            'non-numeric finger' => [[
                'jempol' => 10.0,
                'telunjuk' => 10.0,
                'tengah' => 'invalid',
                'manis' => 10.0,
                'kelingking' => 10.0,
            ]],
            'non-finite finger' => [[
                'jempol' => 10.0,
                'telunjuk' => 10.0,
                'tengah' => INF,
                'manis' => 10.0,
                'kelingking' => 10.0,
            ]],
            'negative finger' => [[
                'jempol' => -0.1,
                'telunjuk' => 10.0,
                'tengah' => 10.0,
                'manis' => 10.0,
                'kelingking' => 10.0,
            ]],
            'finger above domain maximum' => [[
                'jempol' => 25.1,
                'telunjuk' => 10.0,
                'tengah' => 10.0,
                'manis' => 10.0,
                'kelingking' => 10.0,
            ]],
        ];
    }

    private function service(): NailClassifierService
    {
        return $this->app->make(NailClassifierService::class);
    }

    private function createStandard(
        string $sizeName,
        float $uniformWidth,
        float $tolerance,
        bool $isActive = true,
    ): SizeStandard {
        return SizeStandard::factory()->create([
            'size_name' => $sizeName,
            'jempol' => $uniformWidth,
            'telunjuk' => $uniformWidth,
            'tengah' => $uniformWidth,
            'manis' => $uniformWidth,
            'kelingking' => $uniformWidth,
            'tolerance' => $tolerance,
            'is_active' => $isActive,
        ]);
    }

    /**
     * @return array{jempol: float, telunjuk: float, tengah: float, manis: float, kelingking: float}
     */
    private function uniformHand(float $width): array
    {
        return [
            'jempol' => $width,
            'telunjuk' => $width,
            'tengah' => $width,
            'manis' => $width,
            'kelingking' => $width,
        ];
    }
}
