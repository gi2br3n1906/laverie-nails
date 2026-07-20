<x-layouts.app title="Input Data Pengukuran">
    @php
        $fingers = [
            'jempol' => ['Jempol', 'Ukur bagian nail plate yang paling lebar.'],
            'telunjuk' => ['Telunjuk', 'Pastikan penggaris tidak mengikuti lengkung kuku.'],
            'tengah' => ['Jari tengah', 'Catat angka hingga satu desimal bila diperlukan.'],
            'manis' => ['Jari manis', 'Jaga posisi tangan tetap rileks dan rata.'],
            'kelingking' => ['Kelingking', 'Periksa kembali titik awal penggaris pada 0 mm.'],
        ];
    @endphp

    <div class="mx-auto max-w-5xl" data-measurement-form>
        <div class="text-center">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-stone-600">Langkah pengukuran</p>
            <h1 class="mt-3 font-serif text-5xl font-semibold tracking-tight">Ukur Kuku Anda</h1>
            <p class="mx-auto mt-5 max-w-2xl leading-7 text-stone-600">Masukkan lebar nail plate dalam milimeter. Sistem hanya memproses angka yang Anda ukur secara manual.</p>
        </div>

        <form class="mt-10 space-y-8" method="POST" action="{{ route('measurements.store') }}" data-measurement-form-element novalidate>
            @csrf
            <section class="rounded-[2rem] border border-stone-200 bg-stone-100/60 p-5 sm:p-8" aria-labelledby="right-hand-heading">
                <div class="flex items-center gap-4"><span class="grid size-12 place-items-center rounded-2xl bg-stone-900 font-serif text-xl font-bold text-white">R</span><div><h2 class="font-serif text-3xl font-semibold" id="right-hand-heading">Tangan kanan</h2><p class="mt-1 text-sm text-stone-600">Wajib diisi lengkap untuk lima jari.</p></div></div>
                <div class="mt-6 grid gap-4">@foreach ($fingers as $finger => [$label, $hint])<x-measurement-input-row hand="right" :finger="$finger" :label="$label" :hint="$hint" />@endforeach</div>
            </section>

            <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-sm sm:p-8">
                <label class="flex cursor-pointer items-start gap-4">
                    <input class="peer sr-only" name="hands_are_different" type="checkbox" value="1" data-hand-toggle @checked(old('hands_are_different') || old('left_hand_data'))>
                    <span class="relative mt-0.5 h-7 w-12 shrink-0 rounded-full bg-stone-200 transition peer-checked:bg-stone-900 peer-focus-visible:ring-4 peer-focus-visible:ring-stone-300 after:absolute after:left-1 after:top-1 after:size-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                    <span><span class="block font-semibold text-stone-900">Ukuran tangan kanan dan kiri berbeda</span><span class="mt-1 block text-sm leading-6 text-stone-500">Aktifkan untuk mengukur tangan kiri secara independen.</span></span>
                </label>

                <div @class(['grid transition-[grid-template-rows,opacity,margin] duration-500 ease-out', 'grid-rows-[1fr] opacity-100' => old('hands_are_different') || old('left_hand_data'), 'grid-rows-[0fr] opacity-0' => ! old('hands_are_different') && ! old('left_hand_data')]) data-left-hand-panel aria-hidden="{{ old('hands_are_different') || old('left_hand_data') ? 'false' : 'true' }}">
                    <div class="overflow-hidden">
                        <div class="mt-8 border-t border-stone-200 pt-8">
                            <div class="flex items-center gap-4"><span class="grid size-12 place-items-center rounded-2xl bg-stone-900 font-serif text-xl font-bold text-white">L</span><div><h2 class="font-serif text-3xl font-semibold">Tangan kiri</h2><p class="mt-1 text-sm text-stone-600">Isi lengkap ketika toggle diaktifkan.</p></div></div>
                            <div class="mt-6 grid gap-4">@foreach ($fingers as $finger => [$label, $hint])<x-measurement-input-row hand="left" :finger="$finger" :label="$label" :hint="$hint" :required="false" />@endforeach</div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-900" data-validation-summary hidden role="alert"></div>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between"><a class="text-center text-sm font-semibold text-stone-500 hover:text-stone-800" href="{{ route('guidance') }}">← Baca kembali panduan</a><button class="rounded-full bg-stone-900 px-8 py-4 font-semibold text-white shadow-xl shadow-stone-300 transition hover:-translate-y-0.5 hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-stone-300" type="submit">Lihat hasil klasifikasi</button></div>
        </form>
    </div>
</x-layouts.app>