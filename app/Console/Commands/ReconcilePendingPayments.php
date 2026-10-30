<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Notifications\OrderPaid;
use App\Services\OrderNotifier;
use App\Services\PaymentService;
use App\Services\PaymentStatusService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReconcilePendingPayments extends Command
{
    protected $signature = 'orders:reconcile-payments
                            {--minutes=30 : Only inspect orders older than this many minutes}
                            {--limit=100 : Maximum number of orders to inspect}';

    protected $description = 'Reconcile pending orders with the authoritative Midtrans transaction status';

    public function handle(
        PaymentService $payments,
        PaymentStatusService $statuses,
        OrderNotifier $notifier,
    ): int {
        $minutes = max(1, (int) $this->option('minutes'));
        $limit = max(1, min(500, (int) $this->option('limit')));

        $orders = Order::query()
            ->where('payment_status', PaymentStatus::Pending->value)
            ->whereNotNull('snap_token')
            ->where('created_at', '<=', now()->subMinutes($minutes))
            ->oldest('created_at')
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No pending orders need reconciliation.');

            return self::SUCCESS;
        }

        $failed = 0;

        foreach ($orders as $order) {
            try {
                $payload = $payments->fetchStatus($order);

                if ($payload === null) {
                    $this->warn("Skipped {$order->id}: Midtrans status was unavailable or unverified.");

                    continue;
                }

                $incomingStatus = $payments->statusFromNotification($payload);
                $paidOrder = $statuses->apply($order, $incomingStatus, $payload);

                if ($paidOrder !== null) {
                    $notifier->send($paidOrder, new OrderPaid($paidOrder));
                    $this->info("Marked {$order->id} as paid.");
                } else {
                    $this->line("Checked {$order->id}: {$incomingStatus->value}.");
                }
            } catch (Throwable $exception) {
                $failed++;
                Log::error('Unable to reconcile a pending payment.', [
                    'order_id' => $order->id,
                    'error' => $exception->getMessage(),
                ]);
                $this->error("Failed {$order->id}: {$exception->getMessage()}");
            }
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
