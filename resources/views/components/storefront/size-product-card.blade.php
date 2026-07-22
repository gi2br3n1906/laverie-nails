@props(['catalog'])

<article class="group min-w-0 text-center" data-homepage-size-product>
    <a class="block aspect-square overflow-hidden bg-[#EAF0F6]" href="{{ route('products.show', $catalog) }}">
        <img class="size-full object-cover transition duration-700 group-hover:scale-[1.03]" src="{{ Storage::disk('public')->url($catalog->images[0]) }}" alt="{{ $catalog->title }}" loading="lazy">
    </a>
    <a class="mt-4 block" href="{{ route('products.show', $catalog) }}">
        <h3 class="font-display text-lg leading-tight text-[#0C1C39] sm:text-xl">{{ $catalog->title }}</h3>
    </a>
    <p class="mt-2 text-sm font-medium tabular-nums text-[#0C1C39]">Rp {{ number_format((float) $catalog->price, 2, ',', '.') }}</p>
</article>