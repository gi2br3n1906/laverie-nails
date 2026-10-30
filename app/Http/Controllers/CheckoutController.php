<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\ValueObjects\CartOwner;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(Request $request, CartService $cartService): View|RedirectResponse
    {
        $owner = CartOwner::fromRequest($request);
        $items = $cartService->items($owner)->where('is_selected', true)->values();

        if ($items->isEmpty()) {
            return redirect()->route('home')->withErrors([
                'cart' => 'Pilih setidaknya satu produk sebelum checkout.',
            ]);
        }

        $couponId = $request->session()->get('cart_coupon_id');
        $coupon = $couponId ? Coupon::query()->whereKey($couponId)->first() : null;
        $cartState = $cartService->state($owner, $coupon);

        return view('checkout.create', [
            'items' => $items,
            'subtotal' => intdiv($cartState['subtotal'], 100),
            'discount' => intdiv($cartState['discount'], 100),
            'coupon' => $cartState['coupon'],
        ]);
    }

    public function store(StoreCheckoutRequest $request, CheckoutService $checkoutService): RedirectResponse
    {
        /** @var array<string, string> $data */
        $data = $request->safe()->only([
            'customer_name',
            'customer_email',
            'customer_phone',
            'shipping_address',
            'order_notes',
        ]);
        $couponId = $request->session()->get('cart_coupon_id');
        $couponId = $couponId === null ? null : (int) $couponId;
        $lock = Cache::lock('checkout:user:'.$request->user()?->getAuthIdentifier(), 10);

        try {
            $order = $lock->block(
                5,
                fn (): Order => $checkoutService->placeOrder(CartOwner::fromRequest($request), $data, $couponId),
            );
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages([
                'cart' => 'Pesanan Anda sedang diproses. Tunggu sebentar, lalu cek riwayat pesanan sebelum mencoba lagi.',
            ]);
        }

        $request->session()->forget('cart_coupon_id');

        return redirect()->route('checkout.payment', $order);
    }

    public function payment(Request $request, Order $order): View
    {
        // Signed links in transactional email let a customer reopen payment from another device.
        abort_unless(
            $request->hasValidSignature() || CartOwner::fromRequest($request)->ownsOrder($order),
            404,
        );

        return view('checkout.payment', [
            'order' => $order->load('items'),
            'snapJsUrl' => (string) config('services.midtrans.snap_js_url'),
            'midtransClientKey' => (string) config('services.midtrans.client_key'),
        ]);
    }
}
