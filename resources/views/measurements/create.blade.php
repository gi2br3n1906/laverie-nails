<x-layouts.app title="Input Data Pengukuran">
    @php
        $fingers = [
            'jempol' => ['Jempol', 'thumb'],
            'telunjuk' => ['Telunjuk', 'index'],
            'tengah' => ['Jari tengah', 'middle'],
            'manis' => ['Jari manis', 'ring'],
            'kelingking' => ['Kelingking', 'pinky'],
        ];
    @endphp

    <div class="mx-auto max-w-5xl" data-measurement-form>
        <div class="text-center">
            <p class="font-script text-4xl text-[#92A1B5] sm:text-5xl">Find your perfect fit</p>
            <h1 class="mt-2 font-display text-5xl font-semibold tracking-[0.08em] text-[#0C1C39] sm:text-6xl">SIZING</h1>
            <p class="mx-auto mt-5 max-w-2xl lowercase leading-7 text-stone-600">enter the nail measurement in milimeters</p>
        </div>

        <form class="mt-10 space-y-8" method="POST" action="{{ route('measurements.store') }}" data-measurement-form-element novalidate>
            @csrf
            <section class="rounded-[2rem] border border-[#92A1B5]/40 bg-[#EAF0F6]/60 p-5 sm:p-8" aria-labelledby="right-hand-heading">
                <div class="flex items-center gap-4"><span class="grid size-12 place-items-center rounded-2xl bg-[#0C1C39] font-display text-xl font-bold text-white">R</span><h2 class="font-display text-3xl font-semibold text-[#0C1C39]" id="right-hand-heading">Tangan kanan</h2></div>
                <div class="mt-6 grid gap-4">@foreach ($fingers as $finger => [$label, $translation])<x-measurement-input-row hand="right" :finger="$finger" :label="$label" :translation="$translation" />@endforeach</div>
            </section>

            <section class="rounded-[2rem] border border-[#92A1B5]/40 bg-white p-5 shadow-sm sm:p-8">
                <label class="flex cursor-pointer items-start gap-4">
                    <input class="peer sr-only" name="hands_are_different" type="checkbox" value="1" data-hand-toggle @checked(old('hands_are_different') || old('left_hand_data'))>
                    <span class="relative mt-0.5 h-7 w-12 shrink-0 rounded-full bg-stone-200 transition peer-checked:bg-[#0C1C39] peer-focus-visible:ring-4 peer-focus-visible:ring-[#92A1B5]/40 after:absolute after:left-1 after:top-1 after:size-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                    <span class="block font-semibold text-[#0C1C39]">Ukuran tangan kanan dan kiri berbeda</span>
                </label>

                <div @class(['grid transition-[grid-template-rows,opacity,margin] duration-500 ease-out', 'grid-rows-[1fr] opacity-100' => old('hands_are_different') || old('left_hand_data'), 'grid-rows-[0fr] opacity-0' => ! old('hands_are_different') && ! old('left_hand_data')]) data-left-hand-panel aria-hidden="{{ old('hands_are_different') || old('left_hand_data') ? 'false' : 'true' }}">
                    <div class="overflow-hidden">
                        <div class="mt-8 border-t border-[#92A1B5]/30 pt-8">
                            <div class="flex items-center gap-4"><span class="grid size-12 place-items-center rounded-2xl bg-[#0C1C39] font-display text-xl font-bold text-white">L</span><h2 class="font-display text-3xl font-semibold text-[#0C1C39]">Tangan kiri</h2></div>
                            <div class="mt-6 grid gap-4">@foreach ($fingers as $finger => [$label, $translation])<x-measurement-input-row hand="left" :finger="$finger" :label="$label" :translation="$translation" :required="false" />@endforeach</div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-900" data-validation-summary hidden role="alert"></div>
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between"><a class="text-center text-sm font-semibold text-stone-500 hover:text-[#0C1C39]" href="{{ route('guidance') }}">← Baca kembali panduan</a><button class="rounded-full bg-[#0C1C39] px-8 py-4 font-semibold text-white shadow-xl shadow-[#92A1B5]/30 transition hover:-translate-y-0.5 hover:bg-[#192B48] focus:outline-none focus:ring-4 focus:ring-[#92A1B5]/40" type="submit">Lihat hasil klasifikasi</button></div>
        </form>
    </div>
</x-layouts.app>