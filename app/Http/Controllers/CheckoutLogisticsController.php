<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LogisticsCitiesRequest;
use App\Http\Requests\LogisticsShippingOptionsRequest;
use App\Models\CartItem;
use App\Services\LogisticsService;
use App\ValueObjects\CartOwner;
use Illuminate\Http\JsonResponse;

class CheckoutLogisticsController extends Controller
{
    public function cities(LogisticsCitiesRequest $request, LogisticsService $logisticsService): JsonResponse
    {
        return response()->json([
            'data' => $logisticsService->cities((string) $request->validated('province_id')),
        ]);
    }

    public function shippingOptions(
        LogisticsShippingOptionsRequest $request,
        LogisticsService $logisticsService,
    ): JsonResponse {
        $owner = CartOwner::fromRequest($request);
        $quantity = (int) $owner->scope(CartItem::query())->sum('quantity');

        if ($quantity < 1) {
            return response()->json(['message' => 'Keranjang belanja Anda masih kosong.'], 422);
        }

        return response()->json([
            'data' => $logisticsService->shippingOptions(
                (string) $request->validated('destination_id'),
                $quantity * $logisticsService->itemWeightGrams(),
            ),
        ]);
    }
}
