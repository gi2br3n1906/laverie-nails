@props(['title', 'data', 'size', 'confidence'])

@php
    $labels = [
        'jempol' => 'Thumb',
        'telunjuk' => 'Index',
        'tengah' => 'Middle',
        'manis' => 'Ring',
        'kelingking' => 'Pinky',
    ];
@endphp

<article class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-xl shadow-stone-200/50">
    <div class="bg-gradient-to-br from-[#0C1C39] to-[#344761] p-6 text-white sm:p-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-stone-200">recommendation</p>
                <p class="mt-1 font-serif text-5xl font-semibold">{{ $size }}</p>
            </div>
            <div class="rounded-2xl bg-white/15 px-4 py-3 text-right backdrop-blur">
                <p class="text-xs uppercase tracking-wider text-stone-200">Confidence</p>
                <p class="mt-1 text-2xl font-bold tabular-nums">{{ number_format((float) $confidence, 2) }}%</p>
            </div>
        </div>
    </div>
    <dl class="grid grid-cols-2 gap-px bg-stone-200 sm:grid-cols-5">
        @foreach ($labels as $finger => $label)
            <div class="bg-white p-4 text-center">
                <dt class="text-xs font-medium text-stone-500">{{ $label }}</dt>
                <dd class="mt-2 font-semibold tabular-nums text-stone-900">{{ number_format((float) $data[$finger], 1) }} mm</dd>
            </div>
        @endforeach
    </dl>
</article>