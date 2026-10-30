@component('mail::table')
| Produk | Ukuran | Qty | Subtotal |
|:-------|:-------|:---:|---------:|
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->size_type === \App\Enums\CartSizeType::Standard ? 'Size '.($item->size_payload['size'] ?? '-') : 'Custom' }} | {{ $item->quantity }} | Rp {{ number_format($item->product_price * $item->quantity, 0, ',', '.') }} |
@endforeach
@endcomponent

Subtotal: Rp {{ number_format($order->subtotal, 0, ',', '.') }}<br>
Ongkos kirim: Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}<br>
@if ($order->discount_amount > 0)
Diskon{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}: −Rp {{ number_format($order->discount_amount, 0, ',', '.') }}<br>
@endif
**Total: Rp {{ number_format($order->grand_total, 0, ',', '.') }}**
