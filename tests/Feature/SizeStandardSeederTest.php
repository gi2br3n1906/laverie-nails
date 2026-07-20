<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SizeStandard;
use Database\Seeders\SizeStandardSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SizeStandardSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_persists_the_exact_empirical_thesis_metrics(): void
    {
        $this->seed(SizeStandardSeeder::class);

        $this->assertDatabaseCount('size_standards', 4);

        $expectedStandards = [
            'XS' => ['14.0', '10.0', '11.0', '10.0', '8.0'],
            'S' => ['15.0', '11.0', '12.0', '11.0', '9.0'],
            'M' => ['16.0', '12.0', '13.0', '12.0', '10.0'],
            'L' => ['17.0', '13.0', '14.0', '13.0', '11.0'],
        ];

        foreach ($expectedStandards as $sizeName => $expectedMetrics) {
            $standard = SizeStandard::query()->where('size_name', $sizeName)->sole();

            $this->assertSame($expectedMetrics, [
                $standard->jempol,
                $standard->telunjuk,
                $standard->tengah,
                $standard->manis,
                $standard->kelingking,
            ]);
            $this->assertSame('1.0', $standard->tolerance);
            $this->assertTrue($standard->is_active);
        }
    }

    public function test_seeder_is_idempotent_and_restores_canonical_values(): void
    {
        $this->seed(SizeStandardSeeder::class);

        SizeStandard::query()->where('size_name', 'XS')->update([
            'jempol' => 20.0,
            'is_active' => false,
        ]);

        $this->seed(SizeStandardSeeder::class);

        $this->assertDatabaseCount('size_standards', 4);

        $xs = SizeStandard::query()->where('size_name', 'XS')->sole();

        $this->assertSame('14.0', $xs->jempol);
        $this->assertTrue($xs->is_active);
    }
}
