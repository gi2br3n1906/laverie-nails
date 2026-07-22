<x-layouts.app title="Hasil Klasifikasi">
    @php
        $isCustom = $measurement->classified_size_right === 'Custom' || $measurement->classified_size_left === 'Custom';
        $consultationMessage = rawurlencode('Halo Laverie Nails, saya ingin berkonsultasi mengenai hasil ukuran Custom press-on nails saya.');
        $rightCatalogSize = \App\Enums\CatalogSize::tryFrom($measurement->classified_size_right);
        $leftCatalogSize = $measurement->classified_size_left ? \App\Enums\CatalogSize::tryFrom($measurement->classified_size_left) : null;
    @endphp

    <div class="mx-auto max-w-6xl">
        <div class="text-center">
            <span class="inline-flex rounded-full bg-emerald-100 px-4 py-2 text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">Pengukuran berhasil</span>
            <h1 class="mt-5 font-serif text-5xl font-semibold tracking-tight">Hasil Klasifikasi</h1>
            <p class="mx-auto mt-4 max-w-2xl leading-7 text-stone-600">Hasil dihitung dari lima ukuran nail plate menggunakan standar aktif Laverie Nails.</p>
        </div>

        <div class="mt-10 grid gap-6">
            <x-hand-result-card title="Tangan kanan" :data="$measurement->right_hand_data" :size="$measurement->classified_size_right" :confidence="$measurement->confidence_score_right" />
            @if ($measurement->left_hand_data)
                <x-hand-result-card title="Tangan kiri" :data="$measurement->left_hand_data" :size="$measurement->classified_size_left" :confidence="$measurement->confidence_score_left" />
            @endif
        </div>

        @if ($isCustom)
            <section class="mt-8 overflow-hidden rounded-3xl bg-[#0C1C39] p-6 text-white shadow-2xl sm:p-8">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="max-w-2xl"><p class="text-sm font-bold uppercase tracking-[0.2em] text-stone-300">Butuh perhatian personal</p><h2 class="mt-2 font-serif text-3xl font-semibold">Ukuran Custom terdeteksi</h2><p class="mt-3 leading-7 text-stone-300">Perbedaan ukuran berada di luar toleransi standar. Konsultasikan hasil Anda sebelum memilih press-on nails.</p></div>
                    <a class="shrink-0 rounded-full bg-emerald-500 px-6 py-4 text-center font-bold text-white transition hover:-translate-y-0.5 hover:bg-emerald-600" href="https://wa.me/?text={{ $consultationMessage }}" target="_blank" rel="noopener noreferrer">Konsultasikan ukuran Custom</a>
                </div>
            </section>
        @endif

        @if ($rightCatalogSize || $leftCatalogSize)
            <section class="mt-8 rounded-3xl border border-stone-200 bg-white p-6 text-center shadow-sm sm:p-8"><p class="text-sm font-bold uppercase tracking-[0.2em] text-stone-600">Temukan press-on nails Anda</p><h2 class="mt-2 font-serif text-3xl font-semibold">Belanja sesuai hasil ukuran</h2><div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">@if ($rightCatalogSize)<a class="rounded-full bg-stone-900 px-6 py-3 font-semibold text-white hover:bg-stone-800" href="{{ route('products.index', ['size' => $rightCatalogSize->value]) }}">Produk size {{ $rightCatalogSize->value }} untuk tangan kanan</a>@endif @if ($leftCatalogSize && $leftCatalogSize !== $rightCatalogSize)<a class="rounded-full border border-stone-300 bg-white px-6 py-3 font-semibold text-stone-800 hover:bg-stone-100" href="{{ route('products.index', ['size' => $leftCatalogSize->value]) }}">Produk size {{ $leftCatalogSize->value }} untuk tangan kiri</a>@endif</div></section>
        @endif

        <div class="mt-10"><x-size-reference-chart :size-standards="$sizeStandards" /></div>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
            <a class="rounded-full border border-stone-200 bg-white px-6 py-3 text-center font-semibold text-stone-700 hover:border-stone-300 hover:text-stone-800" href="{{ route('measurements.create') }}">Ukur ulang</a>
            @auth<a class="rounded-full bg-stone-900 px-6 py-3 text-center font-semibold text-white hover:bg-stone-800" href="{{ route('history.index') }}">Lihat riwayat</a>@endauth
        </div>
    </div>
</x-layouts.app>