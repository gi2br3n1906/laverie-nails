<x-layouts.app title="Riwayat Pengukuran">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-bold uppercase tracking-[0.2em] text-stone-600">Arsip personal</p><h1 class="mt-3 font-serif text-5xl font-semibold">Riwayat Pengukuran</h1><p class="mt-4 max-w-2xl text-stone-600">Tinjau kembali hasil pengukuran yang tersimpan dengan aman.</p></div>
        <a class="rounded-full bg-stone-900 px-6 py-3 text-center font-semibold text-white shadow-lg shadow-stone-300 hover:bg-stone-800" href="{{ route('measurements.create') }}">Pengukuran baru</a>
    </div>

    @if (session('status'))<div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800" role="status">{{ session('status') }}</div>@endif

    <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($measurements as $measurement)
            <article class="group rounded-3xl border border-stone-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-stone-200">
                <div class="flex items-center justify-between gap-4"><span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-800">#{{ $measurement->id }}</span><time class="text-xs text-stone-400" datetime="{{ $measurement->created_at?->toAtomString() }}">{{ $measurement->created_at?->translatedFormat('d M Y, H.i') }}</time></div>
                <div class="mt-6 grid grid-cols-2 gap-3"><div class="rounded-2xl bg-stone-100 p-4"><p class="text-xs text-stone-500">Tangan kanan</p><p class="mt-1 font-serif text-3xl font-semibold text-stone-800">{{ $measurement->classified_size_right }}</p></div><div class="rounded-2xl bg-stone-50 p-4"><p class="text-xs text-stone-500">Tangan kiri</p><p class="mt-1 font-serif text-3xl font-semibold">{{ $measurement->classified_size_left ?? '—' }}</p></div></div>
                <a class="mt-6 inline-flex items-center gap-2 font-semibold text-stone-900 group-hover:text-stone-800" href="{{ route('history.show', $measurement) }}">Lihat detail <span aria-hidden="true">→</span></a>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-stone-300 bg-white p-10 text-center sm:col-span-2 xl:col-span-3"><p class="font-serif text-2xl font-semibold">Belum ada riwayat</p><p class="mt-2 text-stone-500">Mulai pengukuran pertama Anda untuk melihat hasil di sini.</p></div>
        @endforelse
    </div>
</x-layouts.app>