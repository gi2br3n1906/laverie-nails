<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ShippingZone;
use App\Models\Setting;

class ShippingRateService
{
    /** @return array{java: int, outside_java: int} */
    public function rates(): array
    {
        $values = Setting::query()
            ->whereIn('key', ['shipping.java_cost', 'shipping.outside_java_cost'])
            ->pluck('value', 'key');

        return [
            'java' => $this->normalize($values['shipping.java_cost'] ?? config('shipping.defaults.java')),
            'outside_java' => $this->normalize($values['shipping.outside_java_cost'] ?? config('shipping.defaults.outside_java')),
        ];
    }

    /** @return array{zone: ShippingZone, cost: int} */
    public function quote(string $provinceCode): array
    {
        $zone = in_array($provinceCode, config('shipping.java_provinces', []), true)
            ? ShippingZone::Java
            : ShippingZone::OutsideJava;

        return [
            'zone' => $zone,
            'cost' => $this->rates()[$zone->value],
        ];
    }

    private function normalize(mixed $value): int
    {
        return max(0, (int) $value);
    }
}
