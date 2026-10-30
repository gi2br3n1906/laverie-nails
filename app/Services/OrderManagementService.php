<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FulfillmentStatus;
use App\Models\Order;
use App\Notifications\OrderShipped;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderManagementService
{
    public function __construct(private readonly OrderNotifier $notifier) {}

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

    public function updateFulfillment(Order $order, FulfillmentStatus $status, ?string $trackingNumber, ?string $courier = null): Order
    {
        $previousStatus = $order->fulfillment_status;

        $updated = DB::transaction(function () use ($order, $status, $trackingNumber, $courier): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());
            $lockedOrder->update([
                'fulfillment_status' => $status,
                'courier' => $courier ?: $lockedOrder->courier,
                'tracking_number' => $trackingNumber ?: $lockedOrder->tracking_number,
            ]);

            return $lockedOrder->refresh();
        });

        if ($status === FulfillmentStatus::Shipped && $previousStatus !== FulfillmentStatus::Shipped) {
            $this->notifier->send($updated, new OrderShipped($updated));
        }

        return $updated;
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
