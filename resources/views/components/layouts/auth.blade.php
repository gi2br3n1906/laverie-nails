@props(['title' => 'Authentication'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? __('ui.auth.authentication_title') }} | {{ config('app.name') }}</title>

        <meta name="laverie-i18n" content="{{ json_encode(__('ui.js'), JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS) }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#F8FAFC] font-sans text-[#0C1C39] antialiased">
        <div class="flex min-h-screen flex-col">
            <x-storefront.announcement />
            <x-navbar />
            <main class="flex flex-1 items-center justify-center px-4 py-12 sm:py-16">
                {{ $slot }}
            </main>
            <x-storefront.cart-drawer />
            <x-storefront.footer />
        </div>
    </body>
</html>