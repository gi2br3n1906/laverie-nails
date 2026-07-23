@props(['sizeStandards'])

<section class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="size-reference-heading">
    <div class="max-w-2xl">
        <h2 class="font-serif text-3xl font-semibold" id="size-reference-heading">Size Chart</h2>
    </div>
    <div class="mt-6 overflow-hidden rounded-2xl border border-stone-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-100 text-left text-sm">
                <thead class="bg-stone-100 text-xs uppercase tracking-wider text-stone-800">
                    <tr>
                        <th class="px-4 py-4" scope="col">SIZE</th><th class="px-4 py-4" scope="col">THUMB</th><th class="px-4 py-4" scope="col">INDEX</th><th class="px-4 py-4" scope="col">MIDDLE</th><th class="px-4 py-4" scope="col">RING</th><th class="px-4 py-4" scope="col">PINKY</th><th class="px-4 py-4" scope="col">TOLERANCE</th>
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