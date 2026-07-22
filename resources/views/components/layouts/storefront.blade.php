@props(['title' => 'Laverie Nails'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Laverie Nails — press-on nails elegan, reusable, dan sesuai ukuran kuku Anda.">

        <title>{{ $title }} | {{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#F8FAFC] font-sans text-[#0C1C39] antialiased">
        <x-storefront.announcement />
        <x-navbar />

        <main>
            {{ $slot }}
        </main>

        <x-storefront.footer />
    </body>
</html>