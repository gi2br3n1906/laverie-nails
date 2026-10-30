<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Throwable;

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
                'callbacks' => [
                    'finish' => URL::temporarySignedRoute(
                        'checkout.payment',
                        now()->addDay(),
                        ['order' => $order, 'check' => 1],
                    ),
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

    /**
     * Ask Midtrans for the authoritative status of an order.
     *
     * This is the fallback when a webhook is delayed or cannot reach the
     * application. Returns null when the status cannot be trusted.
     *
     * @return array<string, mixed>|null
     */
    public function fetchStatus(Order $order): ?array
    {
        try {
            $response = Http::acceptJson()
                ->withBasicAuth($this->serverKey(), '')
                ->connectTimeout(5)
                ->timeout(15)
                ->get($this->apiUrl().'/'.$order->getKey().'/status');
        } catch (Throwable $exception) {
            Log::warning('Unable to query Midtrans transaction status.', [
                'order_id' => $order->getKey(),
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('Midtrans transaction status request failed.', [
                'order_id' => $order->getKey(),
                'http_status' => $response->status(),
            ]);

            return null;
        }

        $payload = $response->json();

        if (! is_array($payload) || (string) ($payload['order_id'] ?? '') !== (string) $order->getKey()) {
            return null;
        }

        $grossAmount = $payload['gross_amount'] ?? null;

        if (! is_numeric($grossAmount) || abs((float) $grossAmount - (float) $order->grand_total) > 0.01) {
            Log::warning('Midtrans transaction amount did not match the order.', [
                'order_id' => $order->getKey(),
                'gross_amount' => $grossAmount,
            ]);

            return null;
        }

        return $payload;
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

    private function apiUrl(): string
    {
        return rtrim($this->requiredConfig('api_url'), '/');
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
