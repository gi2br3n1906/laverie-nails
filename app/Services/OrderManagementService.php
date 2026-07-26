<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FulfillmentStatus;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderManagementService
{
    /** @return LengthAwarePaginator<int, Order> */
    public function paginate(?string $paymentStatus): LengthAwarePaginator
    {
        return Order::query()
            ->when($paymentStatus, fn ($query, string $status) => $query->where('payment_status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function detail(Order $order): Order
    {
        return $order->loadMissing('items');
    }

    public function updateFulfillment(Order $order, FulfillmentStatus $status, ?string $trackingNumber): Order
    {
        return DB::transaction(function () use ($order, $status, $trackingNumber): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());
            $lockedOrder->update([
                'fulfillment_status' => $status,
                'tracking_number' => $trackingNumber ?: $lockedOrder->tracking_number,
            ]);

            return $lockedOrder->refresh();
        });
    }

    public function track(string $orderId, string $email): ?Order
    {
        return Order::query()
            ->with('items')
            ->whereKey($orderId)
            ->whereRaw('LOWER(customer_email) = ?', [strtolower($email)])
            ->first();
    }
}
