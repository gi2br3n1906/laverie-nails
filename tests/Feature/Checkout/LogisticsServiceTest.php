<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Services\LogisticsService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LogisticsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.rajaongkir', [
            'base_url' => 'https://rajaongkir.test/starter',
            'api_key' => 'raja-secret',
            'origin_city_id' => '399',
            'couriers' => ['jne', 'jnt'],
            'item_weight_grams' => 500,
        ]);
    }

    public function test_it_fetches_and_normalizes_provinces_with_the_configured_api_key(): void
    {
        Http::fake([
            'rajaongkir.test/starter/province' => Http::response([
                'rajaongkir' => ['results' => [
                    ['province_id' => '6', 'province' => 'Jawa Tengah'],
                    ['province_id' => '5', 'province' => 'DI Yogyakarta'],
                ]],
            ]),
        ]);

        $provinces = app(LogisticsService::class)->provinces();

        $this->assertSame([
            ['id' => '6', 'name' => 'Jawa Tengah'],
            ['id' => '5', 'name' => 'DI Yogyakarta'],
        ], $provinces);
        Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
            && $request->url() === 'https://rajaongkir.test/starter/province'
            && $request->hasHeader('key', 'raja-secret'));
    }

    public function test_it_fetches_cities_for_the_selected_province(): void
    {
        Http::fake([
            'rajaongkir.test/starter/city*' => Http::response([
                'rajaongkir' => ['results' => [
                    ['city_id' => '152', 'type' => 'Kota', 'city_name' => 'Jakarta Pusat'],
                    ['city_id' => '153', 'type' => 'Kabupaten', 'city_name' => 'Jakarta Barat'],
                ]],
            ]),
        ]);

        $cities = app(LogisticsService::class)->cities('6');

        $this->assertSame([
            ['id' => '152', 'name' => 'Kota Jakarta Pusat'],
            ['id' => '153', 'name' => 'Kabupaten Jakarta Barat'],
        ], $cities);
        Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
            && $request->url() === 'https://rajaongkir.test/starter/city?province=6');
    }

    public function test_it_calculates_and_normalizes_shipping_options_without_trusting_the_browser(): void
    {
        Http::fake(function (Request $request) {
            $courier = (string) $request['courier'];

            return Http::response([
                'rajaongkir' => ['results' => [[
                    'code' => $courier,
                    'name' => strtoupper($courier),
                    'costs' => [[
                        'service' => $courier === 'jne' ? 'REG' : 'EZ',
                        'description' => 'Regular Service',
                        'cost' => [['value' => $courier === 'jne' ? 18000 : 16500, 'etd' => '2-3', 'note' => '']],
                    ]],
                ]]],
            ]);
        });

        $options = app(LogisticsService::class)->shippingOptions('152', 1500);

        $this->assertSame([
            [
                'key' => 'jne:REG',
                'courier' => 'jne',
                'courier_name' => 'JNE',
                'service' => 'REG',
                'description' => 'Regular Service',
                'cost' => 18000,
                'etd' => '2-3',
            ],
            [
                'key' => 'jnt:EZ',
                'courier' => 'jnt',
                'courier_name' => 'JNT',
                'service' => 'EZ',
                'description' => 'Regular Service',
                'cost' => 16500,
                'etd' => '2-3',
            ],
        ], $options);
        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request->url() === 'https://rajaongkir.test/starter/cost'
            && $request['origin'] === '399'
            && $request['destination'] === '152'
            && $request['weight'] === 1500
            && $request['courier'] === 'jne');
    }
}
