<?php

declare(strict_types=1);

namespace Tests\Feature\Measurements;

use App\Models\Measurement;
use Database\Seeders\SizeStandardSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SizingResultTipNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_result_page_displays_tip_numbers_instead_of_raw_millimeters(): void
    {
        $this->seed(SizeStandardSeeder::class);

        $response = $this->post(route('measurements.store'), [
            'right_hand_data' => $this->hand(16.0, 11.5, 13.0, 12.0, 9.5),
            'left_hand_data' => $this->hand(14.0, 9.0, 11.0, 10.0, 8.0),
        ]);

        $response->assertOk();

        // Right hand: 16→Tip #2, 11.5→Tip #9, 13→Tip #6, 12→Tip #8, 9.5→Tip #12
        foreach ([2, 9, 6, 8, 12] as $tip) {
            $response->assertSee("Tip #{$tip}");
        }

        // Left hand: 14→Tip #4, 9→Tip #13, 11→Tip #10, 10→Tip #11, 8→Tip #14
        foreach ([4, 13, 10, 11, 14] as $tip) {
            $response->assertSee("Tip #{$tip}");
        }

        // Raw mm is no longer the primary display value in the finger cards:
        // the old primary <dd> only carried e.g. "16.0 mm" with no Tip label.
        $response->assertDontSee('mt-2 font-semibold tabular-nums text-stone-900">16.0 mm', false);
    }

    /**
     * @return array<string, float>
     */
    private function hand(float $jempol, float $telunjuk, float $tengah, float $manis, float $kelingking): array
    {
        return [
            'jempol' => $jempol,
            'telunjuk' => $telunjuk,
            'tengah' => $tengah,
            'manis' => $manis,
            'kelingking' => $kelingking,
        ];
    }
}
