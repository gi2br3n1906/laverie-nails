<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\RegionApiException;
use App\Services\RegionService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class RegionController extends Controller
{
    public function __construct(private readonly RegionService $regions) {}

    public function provinces(): JsonResponse
    {
        return $this->respond(fn (): mixed => $this->regions->provinces());
    }

    public function regencies(string $province): JsonResponse
    {
        return $this->respond(fn (): mixed => $this->regions->regencies($province));
    }

    public function districts(string $regency): JsonResponse
    {
        return $this->respond(fn (): mixed => $this->regions->districts($regency));
    }

    public function villages(string $district): JsonResponse
    {
        return $this->respond(fn (): mixed => $this->regions->villages($district));
    }

    private function respond(callable $callback): JsonResponse
    {
        try {
            return response()->json(['data' => $callback()]);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (RegionApiException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }
}
