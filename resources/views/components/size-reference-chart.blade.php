@props(['sizeStandards'])

<section class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="size-reference-heading">
    <div class="max-w-2xl">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-600">Referensi aktif</p>
        <h2 class="mt-2 font-serif text-3xl font-semibold" id="size-reference-heading">Perbandingan ukuran</h2>
        <p class="mt-3 text-sm leading-6 text-stone-600">Seluruh angka berikut diambil langsung dari standar ukuran aktif di database.</p>
    </div>
    <div class="mt-6 overflow-hidden rounded-2xl border border-stone-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-100 text-left text-sm">
                <thead class="bg-stone-100 text-xs uppercase tracking-wider text-stone-800">
                    <tr>
                        <th class="px-4 py-4" scope="col">Ukuran</th><th class="px-4 py-4" scope="col">Jempol</th><th class="px-4 py-4" scope="col">Telunjuk</th><th class="px-4 py-4" scope="col">Tengah</th><th class="px-4 py-4" scope="col">Manis</th><th class="px-4 py-4" scope="col">Kelingking</th><th class="px-4 py-4" scope="col">Toleransi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 bg-white">
                    @forelse ($sizeStandards as $standard)
                        <tr class="transition hover:bg-stone-100/50">
                            <th class="px-4 py-4 font-bold text-stone-800" scope="row">{{ $standard->size_name }}</th>
                            <td class="px-4 py-4 tabular-nums">{{ $standard->jempol }} mm</td><td class="px-4 py-4 tabular-nums">{{ $standard->telunjuk }} mm</td><td class="px-4 py-4 tabular-nums">{{ $standard->tengah }} mm</td><td class="px-4 py-4 tabular-nums">{{ $standard->manis }} mm</td><td class="px-4 py-4 tabular-nums">{{ $standard->kelingking }} mm</td><td class="px-4 py-4 tabular-nums">±{{ $standard->tolerance }} mm</td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-8 text-center text-stone-500" colspan="7">Belum ada standar ukuran aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>