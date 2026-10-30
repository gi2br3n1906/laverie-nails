@component('mail::message')
# Pesanan baru masuk

Pesanan **{{ $order->id }}** baru saja dibuat dan perlu diproses.

**Pelanggan:** {{ $order->customer_name }}<br>
**Email:** {{ $order->customer_email }}<br>
**Telepon:** {{ $order->customer_phone }}<br>
**Status pembayaran:** {{ $order->payment_status->label() }}

@include('mail.orders._summary', ['order' => $order])

@component('mail::button', ['url' => $adminUrl])
Buka pesanan di admin
@endcomponent

Alamat pengiriman:<br>
{{ $order->fullShippingAddress() }}

@if (filled($order->order_notes))
**Catatan pelanggan:**<br>
{{ $order->order_notes }}
@endif

@component('mail::panel')
Email ini dikirim otomatis saat ada pesanan baru. Jangan balas ke pelanggan dari alamat pengirim ini.
@endcomponent
@endcomponent
