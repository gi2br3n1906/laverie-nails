<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegionEndpointTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config()->set('services.wilayah.base_url', 'https://wilayah.test/api');
    }

    public function test_it_proxies_province_data_as_json(): void
    {
        Http::fake([
            'https://wilayah.test/api/provinces.json' => Http::response([
                'data' => [['code' => '31', 'name' => 'DKI Jakarta']],
            ]),
        ]);

        $this->getJson(route('regions.provinces'))
            ->assertOk()
            ->assertJsonPath('data.0.code', '31')
            ->assertJsonPath('data.0.name', 'DKI Jakarta');
    }

    public function test_it_rejects_a_malformed_code_without_calling_the_api(): void
    {
        $this->getJson(route('regions.regencies', ['province' => 'abc']))
            ->assertStatus(422)
            ->assertJsonStructure(['message']);

        Http::assertNothingSent();
    }

    public function test_it_reports_an_upstream_failure_as_service_unavailable(): void
    {
        Http::fake([
            'https://wilayah.test/api/regencies/31.json' => Http::response([], 503),
        ]);

        $this->getJson(route('regions.regencies', ['province' => '31']))
            ->assertStatus(503)
            ->assertJsonPath('message', 'Layanan data wilayah sedang tidak tersedia. Coba lagi.');
    }
}
