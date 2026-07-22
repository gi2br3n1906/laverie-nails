@props([
    'title',
    'subtitle',
])

<section class="w-full max-w-md rounded-3xl border border-[#92A1B5]/40 bg-white p-8 shadow-xl shadow-[#0C1C39]/5">
    <header class="mb-8 text-center">
        <h1 class="font-display text-3xl font-bold tracking-tight text-[#0C1C39]">{{ $title }}</h1>
        <p class="mt-3 text-sm leading-6 text-stone-600">{{ $subtitle }}</p>
    </header>

    {{ $slot }}
</section>