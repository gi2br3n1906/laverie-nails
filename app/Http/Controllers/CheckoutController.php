<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Notifications\OrderPaid;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderNotifier;
use App\Services\PaymentService;
use App\Services\PaymentStatusService;
use App\Services\ShippingRateService;
use App\ValueObjects\CartOwner;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly ShippingRateService $shippingRates,
    ) {}

    public function create(Request $request, CartService $cartService): View|RedirectResponse
    {
        $owner = CartOwner::fromRequest($request);
        $items = $cartService->items($owner)->where('is_selected', true)->values();

        if ($items->isEmpty()) {
            return redirect()->route('home')->withErrors([
                'cart' => __('ui.checkout.empty_error'),
            ]);
        }

        $couponId = $request->session()->get('cart_coupon_id');
        $coupon = $couponId ? Coupon::query()->whereKey($couponId)->first() : null;
        $cartState = $cartService->state($owner, $coupon);
        $user = $request->user();
        $selectedRegion = [
            'province_code' => $request->old('province_code', $user?->province_code),
            'province_name' => $request->old('province_name', $user?->province_name),
            'regency_code' => $request->old('regency_code', $user?->regency_code),
            'regency_name' => $request->old('regency_name', $user?->regency_name),
            'district_code' => $request->old('district_code', $user?->district_code),
            'district_name' => $request->old('district_name', $user?->district_name),
            'village_code' => $request->old('village_code', $user?->village_code),
            'village_name' => $request->old('village_name', $user?->village_name),
        ];
        $provinceCode = (string) ($selectedRegion['province_code'] ?? '');
        $shippingQuote = $provinceCode !== '' && array_key_exists($provinceCode, config('regions.provinces', []))
            ? $this->shippingRates->quote($provinceCode)
            : null;

        return view('checkout.create', [
            'items' => $items,
            'subtotal' => intdiv($cartState['subtotal'], 100),
            'discount' => intdiv($cartState['discount'], 100),
            'coupon' => $cartState['coupon'],
            'provinces' => config('regions.provinces', []),
            'selectedRegion' => $selectedRegion,
            'shippingQuote' => $shippingQuote,
            'shippingRates' => $this->shippingRates->rates(),
            'javaProvinces' => config('shipping.java_provinces', []),
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
            'province_code',
            'province_name',
            'regency_code',
            'regency_name',
            'district_code',
            'district_name',
            'village_code',
            'village_name',
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

    public function payment(
        Request $request,
        Order $order,
        PaymentService $paymentService,
        PaymentStatusService $statusService,
        OrderNotifier $notifier,
    ): View|RedirectResponse {
        // Signed links in transactional email let a customer reopen payment from another device.
        $this->authorizePaymentAccess($request, $order);
        $request->session()->put($this->paymentAccessKey($request, $order), true);

        if ($request->boolean('check')) {
            $order = $this->refreshPaymentStatus($order, $paymentService, $statusService, $notifier);

            if ($order->payment_status === PaymentStatus::Paid
                && $request->user()?->getAuthIdentifier() === $order->user_id) {
                return redirect()->route('dashboard.orders.show', $order);
            }
        }

        return view('checkout.payment', [
            'order' => $order->load('items'),
            'snapJsUrl' => (string) config('services.midtrans.snap_js_url'),
            'midtransClientKey' => (string) config('services.midtrans.client_key'),
            'paymentStatusUrl' => route('checkout.status', $order),
        ]);
    }

    public function status(
        Request $request,
        Order $order,
        PaymentService $paymentService,
        PaymentStatusService $statusService,
        OrderNotifier $notifier,
    ): JsonResponse {
        $this->authorizePaymentAccess($request, $order);

        $order = $this->refreshPaymentStatus($order, $paymentService, $statusService, $notifier);

        return response()->json([
            'payment_status' => $order->payment_status->value,
            'paid' => $order->payment_status === PaymentStatus::Paid,
            'redirect_url' => $this->paymentRedirectUrl($request, $order),
        ]);
    }

    private function authorizePaymentAccess(Request $request, Order $order): void
    {
        $hasSessionAccess = $request->session()->get($this->paymentAccessKey($request, $order)) === true;

        abort_unless(
            $hasSessionAccess
                || $request->hasValidSignature()
                || CartOwner::fromRequest($request)->ownsOrder($order),
            404,
        );
    }

    private function paymentAccessKey(Request $request, Order $order): string
    {
        $owner = CartOwner::fromRequest($request);

        return 'payment_access.'.$order->getKey().'.'.(string) ($owner->userId ?? $owner->sessionId);
    }

    private function refreshPaymentStatus(
        Order $order,
        PaymentService $paymentService,
        PaymentStatusService $statusService,
        OrderNotifier $notifier,
    ): Order {
        if ($order->payment_status !== PaymentStatus::Pending || blank($order->snap_token)) {
            return $order;
        }

        $payload = $paymentService->fetchStatus($order);

        if ($payload === null) {
            return $order;
        }

        $paidOrder = $statusService->apply(
            $order,
            $paymentService->statusFromNotification($payload),
            $payload,
        );

        if ($paidOrder instanceof Order) {
            $notifier->send($paidOrder, new OrderPaid($paidOrder));
        }

        return $order->refresh();
    }

    private function paymentRedirectUrl(Request $request, Order $order): string
    {
        if ($request->user()?->getAuthIdentifier() === $order->user_id && $order->guest_id === null) {
            return route('dashboard.orders.show', $order);
        }

        return URL::temporarySignedRoute(
            'checkout.payment',
            now()->addDay(),
            ['order' => $order],
        );
    }
}
