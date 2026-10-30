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
        $coupon = Coupon::query()->whereRaw('UPPER(code) = ?', [$code])->first();
        $subtotal = intdiv($this->cartService->state(CartOwner::fromRequest($request))['subtotal'], 100);

        if (! $coupon) {
            throw ValidationException::withMessages([
                'code' => 'Kode diskon tidak ditemukan.',
            ]);
        }

        if ($reason = $coupon->invalidReason($subtotal)) {
            throw ValidationException::withMessages([
                'code' => $reason,
            ]);
        }

        $request->session()->put('cart_coupon_id', $coupon->id);

        return response()->json([
            'message' => __('ui.cart.coupon_applied', ['code' => $coupon->code]),
            'data' => $this->stateFor($request),
        ]);
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $request->session()->forget('cart_coupon_id');

        return response()->json([
            'message' => __('ui.cart.coupon_removed'),
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
                'message' => __('ui.cart.cart_added'),
                'data' => $this->stateFor($request),
            ]);
        }

        return to_route('home')->with('status', __('ui.cart.cart_added'));
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
                'message' => __('ui.cart.cart_updated'),
                'data' => $this->stateFor($request),
            ]);
        }

        return to_route('home')->with('status', __('ui.cart.cart_updated'));
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
            'message' => __('ui.cart.cart_selection_updated'),
            'data' => $this->stateFor($request),
        ]);
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse|JsonResponse
    {
        $this->cartService->remove(CartOwner::fromRequest($request), $cartItem->id);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('ui.cart.cart_removed'),
                'data' => $this->stateFor($request),
            ]);
        }

        return to_route('home')->with('status', __('ui.cart.cart_removed'));
    }

    /** @return array<string, mixed> */
    private function stateFor(Request $request): array
    {
        $couponId = $request->session()->get('cart_coupon_id');
        $coupon = $couponId ? Coupon::query()->whereKey($couponId)->first() : null;

        return $this->cartService->state(CartOwner::fromRequest($request), $coupon);
    }
}
