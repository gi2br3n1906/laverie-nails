<?php

declare(strict_types=1);

namespace Tests\Feature\Measurements;

use App\Models\Measurement;
use App\Models\User;
use Database\Seeders\SizeStandardSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StoreMeasurementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_classify_and_persist_both_hands_independently(): void
    {
        $this->seed(SizeStandardSeeder::class);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('measurements.store'), [
            'right_hand_data' => $this->hand(16.0, 12.0, 13.0, 12.0, 10.0),
            'left_hand_data' => $this->hand(14.0, 10.0, 11.0, 10.0, 8.0),
        ]);

        $measurement = Measurement::query()->sole();

        $response
            ->assertCreated()
            ->assertJsonPath('data.id', $measurement->id)
            ->assertJsonPath('data.classified_size_right', 'M')
            ->assertJsonPath('data.classified_size_left', 'XS');

        $this->assertSame($user->id, $measurement->user_id);
        $this->assertEquals($this->hand(16.0, 12.0, 13.0, 12.0, 10.0), $measurement->right_hand_data);
        $this->assertEquals($this->hand(14.0, 10.0, 11.0, 10.0, 8.0), $measurement->left_hand_data);
        $this->assertSame('M', $measurement->classified_size_right);
        $this->assertSame('XS', $measurement->classified_size_left);
        $this->assertSame('100.00', $measurement->confidence_score_right);
        $this->assertSame('100.00', $measurement->confidence_score_left);
    }

    public function test_right_hand_only_flow_persists_nullable_left_hand_fields(): void
    {
        $this->seed(SizeStandardSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('measurements.store'), [
                'right_hand_data' => $this->hand(15.0, 11.0, 12.0, 11.0, 9.0),
            ])
            ->assertCreated()
            ->assertJsonPath('data.classified_size_right', 'S')
            ->assertJsonPath('data.left_hand_data', null)
            ->assertJsonPath('data.classified_size_left', null)
            ->assertJsonPath('data.confidence_score_left', null);

        $measurement = Measurement::query()->sole();

        $this->assertNull($measurement->left_hand_data);
        $this->assertNull($measurement->classified_size_left);
        $this->assertNull($measurement->confidence_score_left);
    }

    public function test_guest_flow_persists_a_measurement_without_an_owner(): void
    {
        $this->seed(SizeStandardSeeder::class);

        $this->postJson(route('measurements.store'), [
            'right_hand_data' => $this->hand(17.0, 13.0, 14.0, 13.0, 11.0),
        ])->assertCreated();

        $this->assertNull(Measurement::query()->sole()->user_id);
    }

    public function test_measurement_model_casts_json_and_decimal_fields(): void
    {
        $measurement = Measurement::factory()->create([
            'right_hand_data' => $this->hand(14.0, 10.0, 11.0, 10.0, 8.0),
            'left_hand_data' => $this->hand(15.0, 11.0, 12.0, 11.0, 9.0),
            'confidence_score_right' => 95.5,
            'confidence_score_left' => 87.25,
        ]);

        $this->assertIsArray($measurement->right_hand_data);
        $this->assertIsArray($measurement->left_hand_data);
        $this->assertSame('95.50', $measurement->confidence_score_right);
        $this->assertSame('87.25', $measurement->confidence_score_left);
    }

    public function test_measurements_are_deleted_when_the_owner_is_deleted(): void
    {
        $user = User::factory()->create();
        $measurement = Measurement::factory()->for($user)->create();

        $user->delete();

        $this->assertModelMissing($measurement);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $errors
     */
    #[DataProvider('invalidMeasurementProvider')]
    public function test_store_rejects_invalid_measurement_payloads(array $payload, array $errors): void
    {
        $this->seed(SizeStandardSeeder::class);

        $this->postJson(route('measurements.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errors);

        $this->assertDatabaseCount('measurements', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, list<string>}>
     */
    public static function invalidMeasurementProvider(): array
    {
        return [
            'missing right hand' => [[], ['right_hand_data']],
            'missing right finger' => [[
                'right_hand_data' => [
                    'jempol' => 14.0,
                    'telunjuk' => 10.0,
                    'tengah' => 11.0,
                    'manis' => 10.0,
                ],
            ], ['right_hand_data.kelingking']],
            'right values outside domain' => [[
                'right_hand_data' => [
                    'jempol' => -0.1,
                    'telunjuk' => 25.1,
                    'tengah' => 'invalid',
                    'manis' => 10.0,
                    'kelingking' => 8.0,
                ],
            ], ['right_hand_data.jempol', 'right_hand_data.telunjuk', 'right_hand_data.tengah']],
            'partial left hand' => [[
                'right_hand_data' => [
                    'jempol' => 14.0,
                    'telunjuk' => 10.0,
                    'tengah' => 11.0,
                    'manis' => 10.0,
                    'kelingking' => 8.0,
                ],
                'left_hand_data' => [
                    'jempol' => 14.0,
                ],
            ], ['left_hand_data.telunjuk', 'left_hand_data.tengah', 'left_hand_data.manis', 'left_hand_data.kelingking']],
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
