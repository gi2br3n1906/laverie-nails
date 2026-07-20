@props(['initials', 'name', 'title', 'text', 'tone' => 'rose'])

@php
    $toneClass = match ($tone) {
        'sand' => 'from-amber-100 to-stone-200 text-amber-900',
        'sage' => 'from-emerald-100 to-stone-200 text-emerald-900',
        default => 'from-stone-200 to-stone-200 text-stone-900',
    };
@endphp

<article class="rounded-[1.75rem] border border-stone-200 bg-white p-5 sm:p-6" data-homepage-review-card>
    <div @class(['grid aspect-[4/3] place-items-center rounded-2xl bg-gradient-to-br', $toneClass])>
        <span class="grid size-20 place-items-center rounded-full border border-white/70 bg-white/50 font-serif text-3xl">{{ $initials }}</span>
    </div>
    <div class="mt-5 tracking-[0.08em] text-amber-500" aria-label="5 dari 5 bintang">★★★★★</div>
    <div class="mt-3 flex flex-wrap items-center gap-2"><span class="font-semibold text-stone-900">{{ $name }}</span><span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.08em] text-emerald-700"><svg class="size-3" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.6l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" /></svg>Verified</span></div>
    <h3 class="mt-4 font-serif text-2xl leading-tight tracking-[-0.02em]">{{ $title }}</h3>
    <p class="mt-3 text-sm leading-6 text-stone-600">{{ $text }}</p>
</article>