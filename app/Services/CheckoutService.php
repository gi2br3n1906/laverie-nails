<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\ValueObjects\CartOwner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly LogisticsService $logisticsService,
        private readonly PaymentService $paymentService,
    ) {}

    /** @param  array<string, string|null>  $customerData */
    public function placeOrder(CartOwner $owner, array $customerData): Order
    {
        $quantity = (int) $owner->scope(CartItem::query())->sum('quantity');

        if ($quantity < 1) {
            throw ValidationException::withMessages(['cart' => 'Keranjang belanja Anda masih kosong.']);
        }

        $shippingOptions = $this->logisticsService->shippingOptions(
            $customerData['city_id'],
            $quantity * $this->logisticsService->itemWeightGrams(),
        );
        $selectedShipping = collect($shippingOptions)
            ->firstWhere('key', $customerData['shipping_option']);

        if (! is_array($selectedShipping)) {
            throw ValidationException::withMessages([
                'shipping_option' => 'Layanan pengiriman tidak lagi tersedia. Silakan pilih kembali.',
            ]);
        }

        return DB::transaction(function () use ($owner, $customerData, $selectedShipping, $quantity): Order {
            $cartItems = $owner->scope(CartItem::query())
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty() || (int) $cartItems->sum('quantity') !== $quantity) {
                throw ValidationException::withMessages([
                    'cart' => 'Isi keranjang berubah. Silakan periksa kembali sebelum checkout.',
                ]);
            }

            $products = Product::query()
                ->whereKey($cartItems->pluck('product_id')->unique()->sort()->values())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = $this->validateAndCalculateSubtotal($cartItems, $products);
            $shippingCost = (int) $selectedShipping['cost'];
            $order = Order::query()->create([
                ...$owner->orderAttributes(),
                'customer_name' => $customerData['customer_name'],
                'customer_email' => $customerData['customer_email'],
                'customer_phone' => $customerData['customer_phone'],
                'shipping_address' => $customerData['shipping_address'],
                'order_notes' => $customerData['order_notes'] ?? null,
                'province_id' => $customerData['province_id'],
                'city_id' => $customerData['city_id'],
                'courier' => (string) $selectedShipping['key'],
                'shipping_cost' => $shippingCost,
                'subtotal' => $subtotal,
                'grand_total' => $subtotal + $shippingCost,
            ]);

            foreach ($cartItems as $cartItem) {
                $product = $products->get($cartItem->product_id);

                if (! $product instanceof Product) {
                    throw ValidationException::withMessages(['cart' => 'Salah satu produk tidak lagi tersedia.']);
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $this->priceInRupiah((string) $product->price),
                    'quantity' => $cartItem->quantity,
                    'size_type' => $cartItem->size_type,
                    'size_payload' => $cartItem->size_payload,
                ]);
                $product->decrement('stock', $cartItem->quantity);
            }

            $owner->scope(CartItem::query())->delete();
            $order->setRelation('items', $order->items()->get());
            $order->update(['snap_token' => $this->paymentService->createSnapToken($order)]);

            return $order->refresh()->load('items');
        }, 3);
    }

    /**
     * @param  Collection<int, CartItem>  $cartItems
     * @param  Collection<int, Product>  $products
     */
    private function validateAndCalculateSubtotal(Collection $cartItems, Collection $products): int
    {
        $subtotal = 0;

        foreach ($cartItems as $cartItem) {
            $product = $products->get($cartItem->product_id);

            if (! $product instanceof Product || ! $product->is_active) {
                throw ValidationException::withMessages(['cart' => 'Salah satu produk tidak lagi tersedia.']);
            }

            if ($cartItem->quantity > $product->stock) {
                throw ValidationException::withMessages([
                    'cart' => "Stok {$product->name} tidak mencukupi. Tersedia {$product->stock} item.",
                ]);
            }

            $subtotal += $this->priceInRupiah((string) $product->price) * $cartItem->quantity;
        }

        return $subtotal;
    }

    private function priceInRupiah(string $price): int
    {
        [$whole, $fraction] = array_pad(explode('.', $price, 2), 2, '0');

        if ((int) str_pad(substr($fraction, 0, 2), 2, '0') !== 0) {
            throw ValidationException::withMessages([
                'cart' => 'Harga produk harus menggunakan nominal Rupiah utuh.',
            ]);
        }

        return (int) $whole;
    }
}
