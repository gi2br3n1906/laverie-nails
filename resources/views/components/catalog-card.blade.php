@props(['catalog'])

<article class="group overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-stone-200">
    <a href="{{ route('products.show', $catalog) }}">
        <div class="aspect-[4/3] overflow-hidden bg-stone-100">
            <img class="h-full w-full object-cover transition duration-500 group-hover:scale-105" src="{{ Storage::disk('public')->url($catalog->images[0]) }}" alt="{{ $catalog->title }}" loading="lazy">
        </div>
        <div class="p-5">
            <div class="flex items-center justify-between gap-3"><span class="rounded-full bg-stone-200 px-3 py-1 text-xs font-bold text-stone-800">Size {{ $catalog->size->value }}</span><span class="text-xs font-semibold text-amber-600">★ {{ number_format((float) ($catalog->reviews_avg_rating ?? 0), 1) }}</span></div>
            <h2 class="mt-4 font-serif text-2xl font-semibold text-stone-900">{{ $catalog->title }}</h2>
            <p class="mt-2 line-clamp-2 text-sm leading-6 text-stone-500">{{ $catalog->description }}</p>
            <p class="mt-5 font-bold text-stone-800">Rp {{ number_format((float) $catalog->price, 0, ',', '.') }}</p>
        </div>
    </a>
</article>