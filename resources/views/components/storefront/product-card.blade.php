@props(['product', 'badge' => 'Laverie edit', 'homepageCard' => true])

<article class="group min-w-0" @if ($homepageCard) data-homepage-product-card @endif @if ($homepageCard === false) data-recommended-product-card @endif>
    <div class="relative overflow-hidden rounded-[1.5rem] bg-[#EAF0F6]">
        <a class="block aspect-square sm:aspect-[4/5]" href="{{ route('storefront.products.show', $product) }}" aria-label="Lihat {{ $product->name }}">
            @if ($product->primaryImage)
                <img class="h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]" src="{{ Storage::disk('public')->url($product->primaryImage->image_path) }}" alt="{{ $product->name }}" loading="lazy">
            @else
                <span class="grid size-full place-items-center bg-gradient-to-br from-[#EAF0F6] via-white to-[#DDE6F0] text-xs font-semibold uppercase tracking-[0.12em] text-stone-400" data-product-image-fallback>No image</span>
            @endif
        </a>
        <span class="absolute left-3 top-3 rounded-full bg-[#DDE6F0] px-3 py-1.5 text-[0.65rem] font-semibold uppercase tracking-[0.1em] text-[#0C1C39] sm:left-4 sm:top-4">{{ $badge }}</span>
        <button class="absolute right-3 top-3 grid size-9 place-items-center rounded-full bg-white/90 text-stone-700 shadow-sm transition hover:bg-white hover:text-stone-900 sm:right-4 sm:top-4" type="button" aria-label="Simpan {{ $product->name }} ke favorit">
            <svg class="size-4.5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.8 9c0 5.2-8.8 10.2-8.8 10.2S3.2 14.2 3.2 9A4.7 4.7 0 0 1 12 6.65 4.7 4.7 0 0 1 20.8 9Z" /></svg>
        </button>
    </div>
    <div class="px-1 pt-4">
        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-stone-400">{{ $product->category->name }}</p>
        <h3 class="mt-2 font-display text-xl leading-tight tracking-[-0.02em] text-[#0C1C39] sm:text-2xl"><a class="transition hover:text-[#60738C]" href="{{ route('storefront.products.show', $product) }}">{{ $product->name }}</a></h3>
        <p class="mt-2 text-sm font-semibold text-stone-800">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>
        @if (($product->reviews_count ?? 0) > 0)<p class="mt-1 text-xs font-medium text-stone-500">★ {{ number_format((float) $product->reviews_avg_rating, 1) }}</p>@endif
        <p class="mt-1 text-xs uppercase tracking-[0.12em] text-stone-400">{{ $product->stock > 0 ? $product->stock.' in stock' : 'Out of stock' }}</p>
        <a class="mt-4 inline-flex rounded-full bg-[#0C1C39] px-4 py-2 text-xs font-semibold uppercase tracking-[0.1em] text-white" href="{{ route('storefront.products.show', $product) }}">View / Add</a>
    </div>
</article>