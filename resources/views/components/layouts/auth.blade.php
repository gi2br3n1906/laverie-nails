@props(['title' => 'Authentication'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Authentication' }} | {{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#fbfaf8] font-sans text-stone-950 antialiased">
        <div class="flex min-h-screen flex-col">
            <x-storefront.announcement />
            <x-navbar />
            <main class="flex flex-1 items-center justify-center px-4 py-12 sm:py-16">
                {{ $slot }}
            </main>
            <x-storefront.footer />
        </div>
    </body>
</html>