<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MidtransNotificationRequest;
use App\Models\Order;
use App\Notifications\OrderPaid;
use App\Services\OrderNotifier;
use App\Services\PaymentService;
use App\Services\PaymentStatusService;
use Illuminate\Http\JsonResponse;

class MidtransNotificationController extends Controller
{
    public function __invoke(
        MidtransNotificationRequest $request,
        PaymentService $paymentService,
        PaymentStatusService $statusService,
        OrderNotifier $notifier,
    ): JsonResponse {
        $payload = $request->validated();
        abort_unless($paymentService->hasValidSignature($payload), 403, 'Invalid Midtrans signature.');

        $incomingStatus = $paymentService->statusFromNotification($payload);
        $paidOrder = $statusService->apply(
            Order::query()->findOrFail((string) $payload['order_id']),
            $incomingStatus,
            $payload,
        );

        if ($paidOrder instanceof Order) {
            $notifier->send($paidOrder, new OrderPaid($paidOrder));
        }

        return response()->json(['message' => 'Notification processed.']);
    }
}
