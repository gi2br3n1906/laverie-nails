<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\LogisticsService;
use App\ValueObjects\CartOwner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(Request $request, CartService $cartService, LogisticsService $logisticsService): View|RedirectResponse
    {
        $owner = CartOwner::fromRequest($request);
        $items = $cartService->items($owner);

        if ($items->isEmpty()) {
            return redirect()->route('home')->withErrors([
                'cart' => 'Keranjang belanja Anda masih kosong.',
            ]);
        }

        return view('checkout.create', [
            'items' => $items,
            'subtotal' => intdiv($cartService->grandTotalInCents($items), 100),
            'provinces' => $logisticsService->provinces(),
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
            'province_id',
            'city_id',
            'shipping_option',
        ]);
        $order = $checkoutService->placeOrder(CartOwner::fromRequest($request), $data);

        return redirect()->route('checkout.payment', $order);
    }

    public function payment(Request $request, Order $order): View
    {
        abort_unless(CartOwner::fromRequest($request)->ownsOrder($order), 404);

        return view('checkout.payment', [
            'order' => $order->load('items'),
            'snapJsUrl' => (string) config('services.midtrans.snap_js_url'),
            'midtransClientKey' => (string) config('services.midtrans.client_key'),
        ]);
    }
}
