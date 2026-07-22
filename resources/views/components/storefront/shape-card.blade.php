@props(['name', 'variant', 'shape' => null])

@php
    $rounding = match ($variant) {
        'almond' => 'rounded-t-[70%] rounded-b-[42%]',
        'coffin' => 'rounded-t-[28%] rounded-b-[42%]',
        'oval' => 'rounded-[48%]',
        'squoval' => 'rounded-[34%]',
        default => 'rounded-[18%]',
    };
@endphp

<a class="group flex min-w-24 flex-col items-center gap-4 text-center" href="{{ route('products.index') }}" aria-label="{{ $shape ? $name.' style, '.$shape.' shape' : $name }}" data-homepage-shape-card>
    <span class="grid size-24 place-items-center rounded-full bg-[#EAF0F6] transition duration-300 group-hover:bg-[#DDE6F0] sm:size-32">
        <span @class(['h-16 w-9 bg-gradient-to-b from-[#D9E4F0] via-[#AEBED0] to-[#92A1B5] shadow-inner sm:h-20 sm:w-11', $rounding])></span>
    </span>
    <span class="font-display text-lg tracking-[-0.02em] text-[#0C1C39] sm:text-xl">{{ $name }}</span>
</a>