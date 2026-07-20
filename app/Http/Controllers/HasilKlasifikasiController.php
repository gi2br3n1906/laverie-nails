<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreMeasurementRequest;
use App\Services\MeasurementService;
use App\Services\SizeStandardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class HasilKlasifikasiController extends Controller
{
    public function __construct(
        private readonly MeasurementService $measurementService,
        private readonly SizeStandardService $sizeStandardService,
    ) {}

    public function store(StoreMeasurementRequest $request): JsonResponse|View
    {
        $measurement = $this->measurementService->classifyAndStore(
            $request->validated(),
            $request->user(),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $measurement,
            ], Response::HTTP_CREATED);
        }

        return view('measurements.result', [
            'measurement' => $measurement,
            'sizeStandards' => $this->sizeStandardService->active(),
        ]);
    }
}
