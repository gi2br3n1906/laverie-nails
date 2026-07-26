<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaymentService
{
    public function createSnapToken(Order $order): string
    {
        $order->loadMissing('items');
        $token = Http::acceptJson()
            ->asJson()
            ->withBasicAuth($this->serverKey(), '')
            ->connectTimeout(5)
            ->timeout(15)
            ->post($this->requiredConfig('snap_url'), [
                'transaction_details' => [
                    'order_id' => $order->id,
                    'gross_amount' => $order->grand_total,
                ],
                'customer_details' => [
                    'first_name' => $order->customer_name,
                    'email' => $order->customer_email,
                    'phone' => $order->customer_phone,
                    'shipping_address' => [
                        'first_name' => $order->customer_name,
                        'email' => $order->customer_email,
                        'phone' => $order->customer_phone,
                        'address' => $order->shipping_address,
                    ],
                ],
                'item_details' => [
                    ...$order->items->map(fn (OrderItem $item): array => [
                        'id' => (string) ($item->product_id ?? $item->id),
                        'price' => $item->product_price,
                        'quantity' => $item->quantity,
                        'name' => mb_substr($item->product_name, 0, 50),
                    ])->all(),
                    [
                        'id' => 'SHIPPING',
                        'price' => $order->shipping_cost,
                        'quantity' => 1,
                        'name' => 'Ongkos Kirim',
                    ],
                ],
            ])
            ->throw()
            ->json('token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Midtrans tidak mengembalikan Snap Token.');
        }

        return $token;
    }

    /** @param  array<string, mixed>  $payload */
    public function hasValidSignature(array $payload): bool
    {
        $providedSignature = $payload['signature_key'] ?? null;

        if (! is_string($providedSignature) || $providedSignature === '') {
            return false;
        }

        $expectedSignature = hash('sha512',
            (string) ($payload['order_id'] ?? '').
            (string) ($payload['status_code'] ?? '').
            (string) ($payload['gross_amount'] ?? '').
            $this->serverKey()
        );

        return hash_equals($expectedSignature, $providedSignature);
    }

    /** @param  array<string, mixed>  $payload */
    public function statusFromNotification(array $payload): PaymentStatus
    {
        $transactionStatus = strtolower((string) ($payload['transaction_status'] ?? ''));
        $fraudStatus = strtolower((string) ($payload['fraud_status'] ?? ''));

        return match ($transactionStatus) {
            'settlement' => PaymentStatus::Paid,
            'capture' => match ($fraudStatus) {
                'accept' => PaymentStatus::Paid,
                'deny' => PaymentStatus::Failed,
                default => PaymentStatus::Pending,
            },
            'deny', 'cancel' => PaymentStatus::Failed,
            'expire' => PaymentStatus::Expired,
            default => PaymentStatus::Pending,
        };
    }

    private function serverKey(): string
    {
        return $this->requiredConfig('server_key');
    }

    private function requiredConfig(string $key): string
    {
        $value = config('services.midtrans.'.$key);

        if (! is_string($value) || trim($value) === '') {
            throw new RuntimeException("Konfigurasi Midtrans [{$key}] belum tersedia.");
        }

        return $value;
    }
}
