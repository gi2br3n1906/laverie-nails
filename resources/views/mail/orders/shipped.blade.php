@component('mail::message')
# Pesanan sedang dikirim

Halo {{ $order->customer_name }},

Pesanan **{{ $order->id }}** sudah kami serahkan ke kurir.

@if ($order->tracking_number)
**Kurir:** {{ strtoupper(str_replace(':', ' ', $order->courier)) }}<br>
**Nomor resi:** {{ $order->tracking_number }}
@else
**Kurir:** {{ strtoupper(str_replace(':', ' ', $order->courier)) }}
@endif

@include('mail.orders._summary', ['order' => $order])

Dikirim ke:<br>
{{ $order->shipping_address }}

@component('mail::panel')
Nomor resi kadang baru aktif beberapa jam setelah paket dijemput. Kalau paket belum bergerak lebih dari 2 hari, balas email ini.
@endcomponent

Salam,<br>
{{ config('app.name') }}
@endcomponent
