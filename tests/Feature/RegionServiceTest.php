<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exceptions\RegionApiException;
use App\Services\RegionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class RegionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config()->set('services.wilayah.base_url', 'https://wilayah.test/api');
    }

    public function test_it_fetches_sorts_and_caches_provinces(): void
    {
        Http::fake([
            'https://wilayah.test/api/provinces.json' => Http::response([
                'data' => [
                    ['code' => '32', 'name' => 'Jawa Barat'],
                    ['code' => '31', 'name' => 'DKI Jakarta'],
                ],
            ]),
        ]);

        $service = app(RegionService::class);

        $this->assertSame([
            ['code' => '31', 'name' => 'DKI Jakarta'],
            ['code' => '32', 'name' => 'Jawa Barat'],
        ], $service->provinces()->all());

        $service->provinces();

        Http::assertSentCount(1);
    }

    public function test_it_fetches_nested_region_levels_with_the_expected_codes(): void
    {
        Http::fake([
            'https://wilayah.test/api/regencies/31.json' => Http::response([
                'data' => [['code' => '31.74', 'name' => 'Kota Administrasi Jakarta Selatan']],
            ]),
            'https://wilayah.test/api/districts/31.74.json' => Http::response([
                'data' => [['code' => '31.74.09', 'name' => 'Setiabudi']],
            ]),
            'https://wilayah.test/api/villages/31.74.09.json' => Http::response([
                'data' => [['code' => '31.74.09.1002', 'name' => 'Kuningan Barat']],
            ]),
        ]);

        $service = app(RegionService::class);

        $this->assertSame('Kota Administrasi Jakarta Selatan', $service->regencies('31')->first()['name']);
        $this->assertSame('Setiabudi', $service->districts('31.74')->first()['name']);
        $this->assertSame('Kuningan Barat', $service->villages('31.74.09')->first()['name']);
    }

    public function test_it_rejects_invalid_region_codes_before_calling_the_api(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(RegionService::class)->regencies('31.7');
    }

    public function test_provinces_fall_back_to_the_static_map_when_the_api_is_down(): void
    {
        Http::fake([
            'https://wilayah.test/api/provinces.json' => Http::response([], 503),
        ]);

        $provinces = app(RegionService::class)->provinces();

        $this->assertCount(38, $provinces);
        $this->assertSame('DKI Jakarta', $provinces->firstWhere('code', '31')['name']);
    }

    public function test_nested_region_failures_surface_as_a_domain_exception(): void
    {
        Http::fake([
            'https://wilayah.test/api/regencies/31.json' => Http::response([], 503),
        ]);

        $this->expectException(RegionApiException::class);

        app(RegionService::class)->regencies('31');
    }
}
