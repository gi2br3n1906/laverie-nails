<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LogisticsService
{
    /** @return list<array{id: string, name: string}> */
    public function provinces(): array
    {
        $results = $this->client()
            ->get($this->endpoint('province'))
            ->throw()
            ->json('rajaongkir.results', []);

        if (! is_array($results)) {
            return [];
        }

        return array_values(array_map(
            fn (array $province): array => [
                'id' => (string) ($province['province_id'] ?? ''),
                'name' => (string) ($province['province'] ?? ''),
            ],
            $results,
        ));
    }

    /** @return list<array{id: string, name: string}> */
    public function cities(string $provinceId): array
    {
        $results = $this->client()
            ->get($this->endpoint('city'), ['province' => $provinceId])
            ->throw()
            ->json('rajaongkir.results', []);

        if (! is_array($results)) {
            return [];
        }

        return array_values(array_map(
            fn (array $city): array => [
                'id' => (string) ($city['city_id'] ?? ''),
                'name' => trim((string) ($city['type'] ?? '').' '.(string) ($city['city_name'] ?? '')),
            ],
            $results,
        ));
    }

    /** @return list<array{key: string, courier: string, courier_name: string, service: string, description: string, cost: int, etd: string}> */
    public function shippingOptions(string $destinationId, int $weightGrams): array
    {
        $options = [];

        foreach ($this->couriers() as $courier) {
            $results = $this->client()
                ->asForm()
                ->post($this->endpoint('cost'), [
                    'origin' => $this->requiredConfig('origin_city_id'),
                    'destination' => $destinationId,
                    'weight' => max(1, $weightGrams),
                    'courier' => $courier,
                ])
                ->throw()
                ->json('rajaongkir.results', []);

            if (! is_array($results)) {
                continue;
            }

            foreach ($results as $result) {
                if (! is_array($result)) {
                    continue;
                }

                $code = strtolower((string) ($result['code'] ?? $courier));
                $courierName = (string) ($result['name'] ?? strtoupper($code));
                $costs = $result['costs'] ?? [];

                if (! is_array($costs)) {
                    continue;
                }

                foreach ($costs as $serviceCost) {
                    if (! is_array($serviceCost)) {
                        continue;
                    }

                    $service = (string) ($serviceCost['service'] ?? '');
                    $costDetails = $serviceCost['cost'][0] ?? null;

                    if ($service === '' || ! is_array($costDetails)) {
                        continue;
                    }

                    $options[] = [
                        'key' => $code.':'.$service,
                        'courier' => $code,
                        'courier_name' => $courierName,
                        'service' => $service,
                        'description' => (string) ($serviceCost['description'] ?? ''),
                        'cost' => (int) ($costDetails['value'] ?? 0),
                        'etd' => trim((string) ($costDetails['etd'] ?? '')),
                    ];
                }
            }
        }

        return $options;
    }

    public function itemWeightGrams(): int
    {
        return max(1, (int) config('services.rajaongkir.item_weight_grams', 500));
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()
            ->withHeaders(['key' => $this->requiredConfig('api_key')])
            ->connectTimeout(5)
            ->timeout(10)
            ->retry(2, 150);
    }

    private function endpoint(string $path): string
    {
        return rtrim($this->requiredConfig('base_url'), '/').'/'.$path;
    }

    /** @return list<string> */
    private function couriers(): array
    {
        $couriers = config('services.rajaongkir.couriers', []);

        if (! is_array($couriers)) {
            throw new RuntimeException('Daftar kurir RajaOngkir belum dikonfigurasi.');
        }

        return array_values(array_filter(array_map(
            static fn (mixed $courier): string => strtolower(trim((string) $courier)),
            $couriers,
        )));
    }

    private function requiredConfig(string $key): string
    {
        $value = config('services.rajaongkir.'.$key);

        if (! is_string($value) || trim($value) === '') {
            throw new RuntimeException("Konfigurasi RajaOngkir [{$key}] belum tersedia.");
        }

        return $value;
    }
}
