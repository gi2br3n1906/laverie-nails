<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Http\Requests\MidtransNotificationRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MidtransNotificationController extends Controller
{
    public function __invoke(MidtransNotificationRequest $request, PaymentService $paymentService): JsonResponse
    {
        $payload = $request->validated();
        abort_unless($paymentService->hasValidSignature($payload), 403, 'Invalid Midtrans signature.');

        $incomingStatus = $paymentService->statusFromNotification($payload);

        DB::transaction(function () use ($payload, $incomingStatus): void {
            $order = Order::query()->lockForUpdate()->findOrFail((string) $payload['order_id']);

            if ($order->payment_status === PaymentStatus::Paid) {
                return;
            }

            // Stock was already released, so a late settlement must not turn this into a paid order.
            if ($order->stock_restored_at !== null && $incomingStatus === PaymentStatus::Paid) {
                return;
            }

            if (in_array($incomingStatus, [PaymentStatus::Expired, PaymentStatus::Failed], true)) {
                if ($order->stock_restored_at === null) {
                    $order->load('items');

                    foreach ($order->items as $item) {
                        if ($item->product_id === null) {
                            continue;
                        }

                        Product::query()
                            ->whereKey($item->product_id)
                            ->lockForUpdate()
                            ->increment('stock', $item->quantity);
                    }

                    $order->stock_restored_at = now();
                }

                $order->payment_status = $incomingStatus;
                $order->save();

                return;
            }

            if ($incomingStatus === PaymentStatus::Pending && $order->payment_status !== PaymentStatus::Pending) {
                return;
            }

            $order->update(['payment_status' => $incomingStatus]);
        }, 3);

        return response()->json(['message' => 'Notification processed.']);
    }
}
