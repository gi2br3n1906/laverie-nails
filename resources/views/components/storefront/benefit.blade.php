@props(['title', 'icon'])

<div class="flex min-w-max items-center justify-center gap-2.5 px-5 py-4 text-xs font-medium uppercase tracking-[0.12em] text-stone-700 sm:min-w-0 sm:px-4">
    @if ($icon === 'shield')
        <svg class="size-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linejoin="round" d="M12 3.5 19 6v5.3c0 4.3-2.7 7.65-7 9.2-4.3-1.55-7-4.9-7-9.2V6l7-2.5Z" /><path stroke-linecap="round" d="m8.8 12 2.1 2.1 4.4-4.4" /></svg>
    @elseif ($icon === 'reuse')
        <svg class="size-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 8.5A7.5 7.5 0 0 0 6.1 6.1L4 8m1-4v4h4M5 15.5a7.5 7.5 0 0 0 12.9 2.4L20 16m-1 4v-4h-4" /></svg>
    @else
        <svg class="size-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linejoin="round" d="m12 3 2.3 5.2L20 9l-4.1 3.9 1 5.6-4.9-2.8-4.9 2.8 1-5.6L4 9l5.7-.8L12 3Z" /></svg>
    @endif
    <span>{{ $title }}</span>
</div>