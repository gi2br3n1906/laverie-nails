<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Measurement;
use App\Models\SizeStandard;
use Database\Seeders\SizeStandardSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserJourneyViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guidance_page_is_public_and_contains_manual_visual_and_video_guidance(): void
    {
        $this->get(route('guidance'))
            ->assertOk()
            ->assertSee('How to Measure Your Nails')
            ->assertSee('a quick and easy guide to ensure your press on nails fit comfortably and look stunning')
            ->assertSee('watch the video below for a more detailed guide')
            ->assertSeeInOrder(['Measure 1', 'measure-1.svg', 'Measure 2', 'measure-2.svg'])
            ->assertSee('<video', false)
            ->assertDontSee('Mulai pengukuran')
            ->assertDontSee('Tiga langkah')
            ->assertDontSee('Persiapkan, ukur, lalu catat');
    }

    public function test_input_page_has_five_finger_fields_per_hand_and_scoped_toggle_hooks(): void
    {
        $response = $this->get(route('measurements.create'))
            ->assertOk()
            ->assertSee('SIZING')
            ->assertSee('enter the nail measurement in milimeters')
            ->assertSee('Jempol')
            ->assertSee('thumb')
            ->assertSee('Telunjuk')
            ->assertSee('index')
            ->assertSee('Jari tengah')
            ->assertSee('middle')
            ->assertSee('Jari manis')
            ->assertSee('ring')
            ->assertSee('Kelingking')
            ->assertSee('pinky')
            ->assertDontSee('Wajib diisi lengkap untuk lima jari.')
            ->assertDontSee('Aktifkan untuk mengukur tangan kiri secara independen.')
            ->assertDontSee('Isi lengkap ketika toggle diaktifkan.')
            ->assertDontSee('Ukur bagian nail plate yang paling lebar.')
            ->assertSee('data-measurement-form', false)
            ->assertSee('data-hand-toggle', false)
            ->assertSee('data-left-hand-panel', false);

        foreach (['jempol', 'telunjuk', 'tengah', 'manis', 'kelingking'] as $finger) {
            $response
                ->assertSee('name="right_hand_data['.$finger.']"', false)
                ->assertSee('name="left_hand_data['.$finger.']"', false);
        }

        $response
            ->assertSee('min="0"', false)
            ->assertSee('max="25"', false)
            ->assertSee('step="0.1"', false);
    }

    public function test_invalid_browser_submission_redirects_back_to_the_input_page_with_errors(): void
    {
        $this->from(route('measurements.create'))
            ->post(route('measurements.store'), [
                'right_hand_data' => [
                    'jempol' => 25.1,
                ],
            ])
            ->assertRedirect(route('measurements.create'))
            ->assertSessionHasErrors([
                'right_hand_data.jempol',
                'right_hand_data.telunjuk',
            ]);
    }

    public function test_browser_submission_renders_result_with_inputs_scores_and_database_chart(): void
    {
        $this->seed(SizeStandardSeeder::class);
        SizeStandard::query()->where('size_name', 'L')->update([
            'size_name' => 'DB-LARGE',
            'jempol' => 17.2,
        ]);
        SizeStandard::query()->where('size_name', 'S')->update([
            'size_name' => 'INACTIVE-DB-SIZE',
            'is_active' => false,
        ]);

        $response = $this->post(route('measurements.store'), [
            'right_hand_data' => $this->hand(16.0, 12.0, 13.0, 12.0, 10.0),
            'left_hand_data' => $this->hand(14.0, 10.0, 11.0, 10.0, 8.0),
        ]);

        $measurement = Measurement::query()->sole();

        $response
            ->assertOk()
            ->assertViewIs('measurements.result')
            ->assertViewHas('measurement', fn (Measurement $viewMeasurement): bool => $viewMeasurement->is($measurement))
            ->assertViewHas('sizeStandards', fn ($standards): bool => $standards->contains('size_name', 'DB-LARGE'))
            ->assertSee('Hasil Klasifikasi')
            ->assertSee('100.00%')
            ->assertSee('16.0 mm')
            ->assertSee('DB-LARGE')
            ->assertDontSee('INACTIVE-DB-SIZE')
            ->assertDontSee('Konsultasikan ukuran Custom');
    }

    public function test_custom_result_prominently_displays_whatsapp_consultation_cta(): void
    {
        $this->seed(SizeStandardSeeder::class);

        $this->post(route('measurements.store'), [
            'right_hand_data' => $this->hand(25.0, 25.0, 25.0, 25.0, 25.0),
        ])
            ->assertOk()
            ->assertSee('Custom')
            ->assertSee('Konsultasikan ukuran Custom')
            ->assertSee('https://wa.me/', false);
    }

    public function test_json_submission_contract_remains_unchanged(): void
    {
        $this->seed(SizeStandardSeeder::class);

        $this->postJson(route('measurements.store'), [
            'right_hand_data' => $this->hand(14.0, 10.0, 11.0, 10.0, 8.0),
        ])
            ->assertCreated()
            ->assertJsonPath('data.classified_size_right', 'XS');
    }

    /**
     * @return array{jempol: float, telunjuk: float, tengah: float, manis: float, kelingking: float}
     */
    private function hand(float $jempol, float $telunjuk, float $tengah, float $manis, float $kelingking): array
    {
        return compact('jempol', 'telunjuk', 'tengah', 'manis', 'kelingking');
    }
}
