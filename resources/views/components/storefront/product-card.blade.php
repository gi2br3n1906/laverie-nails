@props(['catalog', 'badge' => 'Best seller'])

<article class="group min-w-0" data-homepage-product-card>
    <div class="relative overflow-hidden rounded-[1.5rem] bg-[#EAF0F6]">
        <a class="block aspect-[4/5]" href="{{ route('products.show', $catalog) }}">
            <img class="h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]" src="{{ Storage::disk('public')->url($catalog->images[0]) }}" alt="{{ $catalog->title }}" loading="lazy">
        </a>
        <span class="absolute left-3 top-3 rounded-full bg-[#DDE6F0] px-3 py-1.5 text-[0.65rem] font-semibold uppercase tracking-[0.1em] text-[#0C1C39] sm:left-4 sm:top-4">{{ $badge }}</span>
        <button class="absolute right-3 top-3 grid size-9 place-items-center rounded-full bg-white/90 text-stone-700 shadow-sm transition hover:bg-white hover:text-stone-900 sm:right-4 sm:top-4" type="button" aria-label="Simpan {{ $catalog->title }} ke favorit">
            <svg class="size-4.5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.8 9c0 5.2-8.8 10.2-8.8 10.2S3.2 14.2 3.2 9A4.7 4.7 0 0 1 12 6.65 4.7 4.7 0 0 1 20.8 9Z" /></svg>
        </button>
    </div>
    <div class="px-1 pt-4">
        <div class="flex items-center gap-2 text-xs"><span class="tracking-[0.06em] text-amber-500">★★★★★</span><span class="font-medium text-stone-600">{{ number_format((float) ($catalog->reviews_avg_rating ?? 0), 1) }}</span><span class="text-stone-400">({{ $catalog->reviews_count ?? 0 }})</span></div>
        <a href="{{ route('products.show', $catalog) }}"><h3 class="mt-2 font-display text-xl leading-tight tracking-[-0.02em] text-[#0C1C39] sm:text-2xl">{{ $catalog->title }}</h3></a>
        <p class="mt-2 text-sm font-semibold text-stone-800">Rp {{ number_format((float) $catalog->price, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs uppercase tracking-[0.12em] text-stone-400">Size {{ $catalog->size->value }}</p>
    </div>
</article>