<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\FulfillmentStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterOrdersRequest;
use App\Http\Requests\Admin\UpdateOrderFulfillmentRequest;
use App\Models\Order;
use App\Services\OrderManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderManagementService $orders) {}

    public function index(FilterOrdersRequest $request): View
    {
        $paymentStatus = $request->validated('payment_status');

        return view('admin.orders.index', [
            'orders' => $this->orders->paginate(is_string($paymentStatus) ? $paymentStatus : null),
            'paymentStatuses' => PaymentStatus::cases(),
            'selectedPaymentStatus' => $paymentStatus,
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $this->orders->detail($order),
            'fulfillmentStatuses' => FulfillmentStatus::cases(),
        ]);
    }

    public function update(UpdateOrderFulfillmentRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();
        $this->orders->updateFulfillment(
            $order,
            FulfillmentStatus::from($data['fulfillment_status']),
            $data['tracking_number'] ?? null,
        );

        return to_route('admin.orders.show', $order)->with('status', 'Status pemenuhan pesanan berhasil diperbarui.');
    }
}
