<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Http\Requests\MidtransNotificationRequest;
use App\Models\Order;
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

            if ($incomingStatus === PaymentStatus::Pending && $order->payment_status !== PaymentStatus::Pending) {
                return;
            }

            $order->update(['payment_status' => $incomingStatus]);
        }, 3);

        return response()->json(['message' => 'Notification processed.']);
    }
}
