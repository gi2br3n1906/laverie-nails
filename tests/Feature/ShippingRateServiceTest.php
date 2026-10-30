<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\ShippingRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingRateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_quotes_java_and_outside_java_from_defaults(): void
    {
        $service = app(ShippingRateService::class);

        $java = $service->quote('31');
        $outside = $service->quote('51');

        $this->assertSame(15000, $java['cost']);
        $this->assertSame('java', $java['zone']->value);
        $this->assertSame(20000, $outside['cost']);
        $this->assertSame('outside_java', $outside['zone']->value);
    }

    public function test_it_uses_settings_overrides(): void
    {
        Setting::putMany([
            'shipping.java_cost' => '27500',
            'shipping.outside_java_cost' => '35000',
        ]);

        $service = app(ShippingRateService::class);

        $this->assertSame(27500, $service->quote('32')['cost']);
        $this->assertSame(35000, $service->quote('51')['cost']);
        $this->assertSame(['java' => 27500, 'outside_java' => 35000], $service->rates());
    }
}
