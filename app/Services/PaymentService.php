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
        $itemDetails = $order->items->map(fn (OrderItem $item): array => [
            'id' => (string) ($item->product_id ?? $item->id),
            'price' => $item->product_price,
            'quantity' => $item->quantity,
            'name' => mb_substr($item->product_name, 0, 50),
        ])->all();
        // Shipping is charged at checkout for every new order. Legacy orders with
        // shipping_cost = 0 simply omit this line item.
        if ($order->shipping_cost > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];
        }

        if ($order->discount_amount > 0) {
            $itemDetails[] = [
                'id' => 'DISCOUNT',
                'price' => -$order->discount_amount,
                'quantity' => 1,
                'name' => 'Diskon ('.$order->coupon_code.')',
            ];
        }

        $itemDetailsTotal = array_sum(array_map(
            static fn (array $item): int => $item['price'] * $item['quantity'],
            $itemDetails,
        ));

        if ($itemDetailsTotal !== $order->grand_total) {
            throw new RuntimeException('Jumlah item Midtrans tidak cocok dengan total pesanan.');
        }

        $shippingAddress = [
            'first_name' => $order->customer_name,
            'email' => $order->customer_email,
            'phone' => $order->customer_phone,
            'address' => $order->fullShippingAddress(),
            'country_code' => 'IDN',
        ];

        if (filled($order->regency_name)) {
            $shippingAddress['city'] = $order->regency_name;
        }

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
                    'shipping_address' => $shippingAddress,
                ],
                'item_details' => $itemDetails,
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
