<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\RegionApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class RegionService
{
    /** @return Collection<int, array{code: string, name: string}> */
    public function provinces(): Collection
    {
        try {
            $provinces = $this->remember('provinces', fn (): Collection => $this->fetch('provinces.json'));
        } catch (Throwable) {
            return $this->fallbackProvinces();
        }

        return $provinces->isNotEmpty() ? $provinces : $this->fallbackProvinces();
    }

    /** @return Collection<int, array{code: string, name: string}> */
    public function regencies(string $provinceCode): Collection
    {
        $this->assertCode($provinceCode, 'province');

        return $this->remember(
            'regencies.'.$provinceCode,
            fn (): Collection => $this->fetch('regencies/'.$provinceCode.'.json'),
        );
    }

    /** @return Collection<int, array{code: string, name: string}> */
    public function districts(string $regencyCode): Collection
    {
        $this->assertCode($regencyCode, 'regency');

        return $this->remember(
            'districts.'.$regencyCode,
            fn (): Collection => $this->fetch('districts/'.$regencyCode.'.json'),
        );
    }

    /** @return Collection<int, array{code: string, name: string}> */
    public function villages(string $districtCode): Collection
    {
        $this->assertCode($districtCode, 'district');

        return $this->remember(
            'villages.'.$districtCode,
            fn (): Collection => $this->fetch('villages/'.$districtCode.'.json'),
        );
    }

    /** @return array{code: string, name: string}|null */
    public function findProvince(string $code): ?array
    {
        return $this->provinces()->firstWhere('code', $code);
    }

    /** @return array{code: string, name: string}|null */
    public function findRegency(string $provinceCode, string $code): ?array
    {
        return $this->regencies($provinceCode)->firstWhere('code', $code);
    }

    /** @return array{code: string, name: string}|null */
    public function findDistrict(string $regencyCode, string $code): ?array
    {
        return $this->districts($regencyCode)->firstWhere('code', $code);
    }

    /** @return array{code: string, name: string}|null */
    public function findVillage(string $districtCode, string $code): ?array
    {
        return $this->villages($districtCode)->firstWhere('code', $code);
    }

    public function isJavaProvince(string $provinceCode): bool
    {
        return in_array($provinceCode, config('shipping.java_provinces', []), true);
    }

    /** @param callable(): Collection<int, array{code: string, name: string}> $callback */
    private function remember(string $key, callable $callback): Collection
    {
        return Cache::remember('wilayah.'.$key, now()->addDays(30), $callback);
    }

    /** @return Collection<int, array{code: string, name: string}> */
    private function fetch(string $path): Collection
    {
        $baseUrl = rtrim((string) config('services.wilayah.base_url'), '/');

        try {
            $response = $this->http()->get($baseUrl.'/'.$path);
        } catch (Throwable $exception) {
            throw new RegionApiException(previous: $exception);
        }

        if ($response->failed()) {
            throw new RegionApiException;
        }

        $data = $response->json('data');

        if (! is_array($data)) {
            throw new RegionApiException;
        }

        return collect($data)
            ->filter(fn (mixed $item): bool => is_array($item) && isset($item['code'], $item['name']))
            ->map(fn (array $item): array => [
                'code' => (string) $item['code'],
                'name' => trim((string) $item['name']),
            ])
            ->filter(fn (array $item): bool => $item['code'] !== '' && $item['name'] !== '')
            ->sortBy(fn (array $item): string => Str::lower($item['name']))
            ->values();
    }

    private function http(): PendingRequest
    {
        return Http::acceptJson()
            ->connectTimeout(3)
            ->timeout((int) config('services.wilayah.timeout', 8))
            ->retry(2, 200);
    }

    private function assertCode(string $code, string $level): void
    {
        $pattern = match ($level) {
            'province' => '/^\d{2}$/',
            'regency' => '/^\d{2}\.\d{2}$/',
            'district' => '/^\d{2}\.\d{2}\.\d{2}$/',
            default => throw new InvalidArgumentException('Level wilayah tidak dikenal.'),
        };

        if (preg_match($pattern, $code) !== 1) {
            throw new InvalidArgumentException('Kode wilayah tidak valid.');
        }
    }

    /** @return Collection<int, array{code: string, name: string}> */
    private function fallbackProvinces(): Collection
    {
        return collect(config('regions.provinces', []))
            ->map(fn (string $name, string $code): array => [
                'code' => $code,
                'name' => $name,
            ])
            ->sortBy(fn (array $item): string => Str::lower($item['name']))
            ->values();
    }
}
