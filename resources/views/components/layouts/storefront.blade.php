@props([
    'title' => 'Laverie Nails',
    'overlayNavigation' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ __('ui.meta.description') }}">

        <title>{{ $title }} | {{ config('app.name') }}</title>

        <meta name="laverie-i18n" content="{{ json_encode(__('ui.js'), JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS) }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#F8FAFC] font-sans text-[#0C1C39] antialiased">
        <x-storefront.announcement />

        @if ($overlayNavigation)
            <div class="relative">
                <x-navbar overlay />
                <main>
                    {{ $slot }}
                </main>
            </div>
        @else
            <x-navbar />
            <main>
                {{ $slot }}
            </main>
        @endif

        <x-storefront.cart-drawer />
        <x-storefront.footer />
    </body>
</html>