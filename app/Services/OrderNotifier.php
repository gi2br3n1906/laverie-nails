<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Throwable;

class OrderNotifier
{
    /**
     * Queue a transactional email to the address stored on the order.
     *
     * A mail failure must never break checkout or the payment webhook, so the
     * queue push is isolated and reported to the log instead.
     */
    public function send(Order $order, Notification $notification): void
    {
        $email = $order->customer_email;

        if (! is_string($email) || trim($email) === '') {
            return;
        }

        try {
            NotificationFacade::route('mail', $email)->notify($notification);
        } catch (Throwable $exception) {
            Log::warning('Unable to queue an order notification.', [
                'order_id' => $order->getKey(),
                'notification' => $notification::class,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
