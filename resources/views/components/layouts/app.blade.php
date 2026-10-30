@props(['title' => 'Laverie Nails'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }} | {{ config('app.name') }}</title>

        <meta name="laverie-i18n" content="{{ json_encode(__('ui.js'), JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS) }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#F8FAFC] font-sans text-[#0C1C39] antialiased">
        <div class="flex min-h-screen flex-col">
            <x-storefront.announcement />
            <x-navbar />

            @if (auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                <x-admin.navigation />
            @endif

            <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
                {{ $slot }}
            </main>

            <x-storefront.cart-drawer />

            <x-storefront.footer />
        </div>

        @stack('scripts')
    </body>
</html>