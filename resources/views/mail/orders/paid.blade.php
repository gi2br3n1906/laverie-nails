@component('mail::message')
# Pembayaran diterima

Halo {{ $order->customer_name }},

Pembayaran untuk pesanan **{{ $order->id }}** sudah kami terima. Pesananmu sekarang masuk antrean untuk kami siapkan.

@include('mail.orders._summary', ['order' => $order])

Kami akan mengirim email lagi begitu pesanan diserahkan ke kurir, lengkap dengan nomor resi.

@component('mail::panel')
Simpan email ini sebagai bukti pembayaran. Kalau ada yang ingin diubah, balas cepat sebelum pesanan kami kemas.
@endcomponent

Salam,<br>
{{ config('app.name') }}
@endcomponent
