<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Measurement;
use App\Services\MeasurementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MeasurementHistoryController extends Controller
{
    public function __construct(private readonly MeasurementService $measurementService) {}

    public function index(Request $request): JsonResponse|View
    {
        $measurements = $this->measurementService->historyFor($request->user());

        return $request->expectsJson()
            ? response()->json(['data' => $measurements])
            : view('measurements.history.index', compact('measurements'));
    }

    public function show(Request $request, Measurement $measurement): JsonResponse|View
    {
        return $request->expectsJson()
            ? response()->json(['data' => $measurement])
            : view('measurements.history.show', compact('measurement'));
    }

    public function print(Request $request, Measurement $measurement): JsonResponse|View
    {
        return $request->expectsJson()
            ? response()->json(['data' => $measurement])
            : view('measurements.history.print', compact('measurement'));
    }

    public function destroy(Request $request, Measurement $measurement): RedirectResponse|Response
    {
        $this->measurementService->delete($measurement);

        return $request->expectsJson()
            ? response()->noContent()
            : redirect()->route('history.index')->with('status', 'Riwayat pengukuran berhasil dihapus.');
    }
}
