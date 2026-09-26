<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Services\CartService;
use App\ValueObjects\CartOwner;
use App\ValueObjects\CartSize;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function state(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->stateFor($request)]);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $code = strtoupper(trim((string) $validated['code']));
        $coupon = Coupon::query()->whereRaw('UPPER(code) = ?', [$code])->where('is_active', true)->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'code' => 'Kode diskon tidak ditemukan atau sudah tidak aktif.',
            ]);
        }

        $request->session()->put('cart_coupon_id', $coupon->id);

        return response()->json([
            'message' => "Kode {$coupon->code} diterapkan.",
            'data' => $this->stateFor($request),
        ]);
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $request->session()->forget('cart_coupon_id');

        return response()->json([
            'message' => 'Kode diskon dihapus.',
            'data' => $this->stateFor($request),
        ]);
    }

    public function store(StoreCartItemRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $this->cartService->add(
            CartOwner::fromRequest($request),
            (int) $validated['product_id'],
            CartSize::fromValidated($validated),
            (int) $validated['quantity'],
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Produk ditambahkan ke keranjang.',
                'data' => $this->stateFor($request),
            ]);
        }

        return to_route('home')->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse|JsonResponse
    {
        $this->cartService->updateQuantity(
            CartOwner::fromRequest($request),
            $cartItem->id,
            (int) $request->validated('quantity'),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Jumlah produk diperbarui.',
                'data' => $this->stateFor($request),
            ]);
        }

        return to_route('home')->with('status', 'Jumlah produk diperbarui.');
    }

    public function selection(Request $request, CartItem $cartItem): JsonResponse
    {
        $validated = $request->validate([
            'is_selected' => ['required', 'boolean'],
        ]);

        $this->cartService->setSelected(
            CartOwner::fromRequest($request),
            $cartItem->id,
            (bool) $validated['is_selected'],
        );

        return response()->json([
            'message' => 'Pilihan produk diperbarui.',
            'data' => $this->stateFor($request),
        ]);
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse|JsonResponse
    {
        $this->cartService->remove(CartOwner::fromRequest($request), $cartItem->id);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Produk dihapus dari keranjang.',
                'data' => $this->stateFor($request),
            ]);
        }

        return to_route('home')->with('status', 'Produk dihapus dari keranjang.');
    }

    /** @return array<string, mixed> */
    private function stateFor(Request $request): array
    {
        $couponId = $request->session()->get('cart_coupon_id');
        $coupon = $couponId ? Coupon::query()->whereKey($couponId)->where('is_active', true)->first() : null;

        return $this->cartService->state(CartOwner::fromRequest($request), $coupon);
    }
}
