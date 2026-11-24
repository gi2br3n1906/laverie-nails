@props(['initials', 'name', 'title', 'text', 'tone' => 'rose', 'isDemo' => false, 'scrollCard' => false])

@php
    $toneClass = match ($tone) {
        'sand' => 'from-[#F8FAFC] to-[#DDE6F0] text-[#0C1C39]',
        'sage' => 'from-[#EAF0F6] to-[#C7D0DC] text-[#0C1C39]',
        default => 'from-[#D9E4F0] to-[#EAF0F6] text-[#0C1C39]',
    };
@endphp

<article @class(['w-[66%] shrink-0 snap-start rounded-[1.75rem] border border-stone-200 bg-white p-4 sm:w-[46%] md:w-auto md:overflow-visible md:p-5' => $scrollCard, 'rounded-[1.75rem] border border-stone-200 bg-white p-5 sm:p-6' => ! $scrollCard]) data-homepage-review-card>
    <div @class(['grid aspect-[4/3] place-items-center rounded-2xl bg-gradient-to-br md:aspect-[4/3]', $toneClass])>
        <span @class(['grid place-items-center rounded-full border border-white/70 bg-white/50 font-serif', 'size-14 text-lg md:size-20 md:text-3xl' => $scrollCard, 'size-20 text-3xl' => ! $scrollCard])>{{ $initials }}</span>
    </div>
    <div @class(['tracking-[0.08em] text-amber-500', 'mt-3' => $scrollCard, 'mt-5' => ! $scrollCard]) aria-label="5 dari 5 bintang">★★★★★</div>
    <div @class(['flex flex-wrap items-center gap-2', 'mt-1.5' => $scrollCard, 'mt-3' => ! $scrollCard])><span class="font-semibold text-stone-900">{{ $name }}</span>@if ($isDemo)<span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.08em] text-amber-800" data-review-demo>Demo</span>@else<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.08em] text-emerald-700" data-review-verified><svg class="size-3" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.6l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" /></svg>Verified</span>@endif</div>
    <h3 @class(['font-display leading-tight', 'mt-3 text-base sm:text-lg md:mt-4 md:text-2xl' => $scrollCard, 'mt-4 text-2xl' => ! $scrollCard])>{{ $title }}</h3>
    <p @class(['text-stone-600', 'mt-1.5 line-clamp-3 text-xs leading-relaxed sm:text-sm md:mt-3 md:leading-6' => $scrollCard, 'mt-3 text-sm leading-6' => ! $scrollCard])>{{ $text }}</p>
</article>