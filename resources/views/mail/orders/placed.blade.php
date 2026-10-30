@component('mail::message')
# Pesanan sudah kami terima

Halo {{ $order->customer_name }},

Terima kasih sudah berbelanja di {{ config('app.name') }}. Pesanan **{{ $order->id }}** sudah masuk dan stoknya kami tahan untukmu sampai pembayaran selesai.

@include('mail.orders._summary', ['order' => $order])

@component('mail::button', ['url' => $paymentUrl])
Selesaikan pembayaran
@endcomponent

Tautan pembayaran ini berlaku 7 hari. Kalau kamu sudah membayar, abaikan email ini.

Alamat pengiriman:<br>
{{ $order->fullShippingAddress() }}

@component('mail::panel')
Butuh bantuan? Balas email ini dan sertakan nomor pesanan **{{ $order->id }}**.
@endcomponent

Salam,<br>
{{ config('app.name') }}
@endcomponent
