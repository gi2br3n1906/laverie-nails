<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentStatusService
{
    /**
     * Apply a verified Midtrans status to an order.
     *
     * Returns the order only when this call transitions it to paid, so the
     * caller can send the paid notification exactly once.
     *
     * @param  array<string, mixed>  $payload
     */
    public function apply(Order $order, PaymentStatus $incomingStatus, array $payload): ?Order
    {
        return DB::transaction(function () use ($order, $incomingStatus, $payload): ?Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail((string) $order->getKey());

            if ($lockedOrder->payment_status === PaymentStatus::Paid) {
                return null;
            }

            // Stock was already released, so a late settlement must not turn this into a paid order.
            if ($lockedOrder->stock_restored_at !== null && $incomingStatus === PaymentStatus::Paid) {
                Log::critical('Midtrans reported payment after stock was released; manual refund and reconciliation required.', [
                    'order_id' => $lockedOrder->getKey(),
                    'gross_amount' => $payload['gross_amount'] ?? null,
                    'transaction_status' => $payload['transaction_status'] ?? null,
                ]);

                return null;
            }

            if (in_array($incomingStatus, [PaymentStatus::Expired, PaymentStatus::Failed], true)) {
                if ($lockedOrder->stock_restored_at === null) {
                    $lockedOrder->load('items');

                    foreach ($lockedOrder->items as $item) {
                        if ($item->product_id === null) {
                            continue;
                        }

                        Product::query()
                            ->whereKey($item->product_id)
                            ->lockForUpdate()
                            ->increment('stock', $item->quantity);
                    }

                    $lockedOrder->stock_restored_at = now();
                }

                $lockedOrder->payment_status = $incomingStatus;
                $lockedOrder->save();

                return null;
            }

            if ($incomingStatus === PaymentStatus::Pending && $lockedOrder->payment_status !== PaymentStatus::Pending) {
                return null;
            }

            $lockedOrder->update(['payment_status' => $incomingStatus]);

            return $incomingStatus === PaymentStatus::Paid ? $lockedOrder : null;
        }, 3);
    }
}
