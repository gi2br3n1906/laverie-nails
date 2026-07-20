@props(['name', 'variant'])

@php
    $rounding = match ($variant) {
        'almond' => 'rounded-t-[70%] rounded-b-[42%]',
        'coffin' => 'rounded-t-[28%] rounded-b-[42%]',
        'oval' => 'rounded-[48%]',
        'squoval' => 'rounded-[34%]',
        default => 'rounded-[18%]',
    };
@endphp

<a class="group flex min-w-24 flex-col items-center gap-4 text-center" href="{{ route('products.index') }}" data-homepage-shape-card>
    <span class="grid size-24 place-items-center rounded-full bg-[#efe9e5] transition duration-300 group-hover:bg-[#e8ded8] sm:size-32">
        <span @class(['h-16 w-9 bg-gradient-to-b from-[#f7c9c3] via-[#e8a9a0] to-[#d78f89] shadow-inner sm:h-20 sm:w-11', $rounding])></span>
    </span>
    <span class="font-serif text-lg tracking-[-0.02em] text-stone-900 sm:text-xl">{{ $name }}</span>
</a>