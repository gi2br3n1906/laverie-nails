<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ __('ui.measurements.print_title', ['id' => $measurement->id]) }} | {{ config('app.name') }}</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
    <body class="bg-white font-sans text-stone-900 antialiased">
        <div class="print:hidden">
            <x-storefront.announcement />
            <x-navbar />
        </div>
        <main class="mx-auto max-w-4xl p-8">
            <div class="mb-6 flex justify-end print:hidden"><button class="rounded-full bg-stone-900 px-5 py-3 text-sm font-semibold text-white" type="button" data-print-trigger>{{ __('ui.measurements.print_result') }}</button></div>
            <header class="flex items-end justify-between border-b border-stone-200 pb-6"><div><p class="font-serif text-3xl font-semibold text-stone-800">Laverie Nails</p><p class="mt-1 text-sm text-stone-500">{{ __('ui.measurements.print_summary') }}</p></div><div class="text-right text-sm text-stone-500"><p>#{{ $measurement->id }}</p><p>{{ $measurement->created_at?->translatedFormat('d F Y, H.i') }}</p></div></header>
            <section class="mt-10 grid gap-8"><x-hand-result-card :title="__('ui.measurements.right_hand')" :data="$measurement->right_hand_data" :size="$measurement->classified_size_right" :confidence="$measurement->confidence_score_right" />@if ($measurement->left_hand_data)<x-hand-result-card :title="__('ui.measurements.left_hand')" :data="$measurement->left_hand_data" :size="$measurement->classified_size_left" :confidence="$measurement->confidence_score_left" />@endif</section>
            <p class="mt-10 border-t border-stone-200 pt-5 text-xs leading-5 text-stone-500">{{ __('ui.measurements.print_note') }}</p>
        </main>
    </body>
</html>