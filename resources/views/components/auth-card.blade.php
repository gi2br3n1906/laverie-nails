@props([
    'title',
    'subtitle',
])

<section class="w-full max-w-md rounded-3xl border border-stone-200 bg-white p-8 shadow-xl shadow-stone-200/60">
    <header class="mb-8 text-center">
        <p class="mb-2 text-sm font-semibold uppercase tracking-[0.24em] text-stone-600">Laverie Nails</p>
        <h1 class="font-serif text-3xl font-bold tracking-tight text-stone-900">{{ $title }}</h1>
        <p class="mt-3 text-sm leading-6 text-stone-600">{{ $subtitle }}</p>
    </header>

    {{ $slot }}
</section>