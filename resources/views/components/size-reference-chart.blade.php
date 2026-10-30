@props(['sizeStandards'])

<section class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="size-reference-heading">
    <div class="max-w-2xl">
        <h2 class="font-serif text-3xl font-semibold" id="size-reference-heading">{{ __('ui.measurements.size_chart') }}</h2>
    </div>
    <div class="mt-6 overflow-hidden rounded-2xl border border-stone-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-100 text-left text-sm">
                <thead class="bg-stone-100 text-xs uppercase tracking-wider text-stone-800">
                    <tr>
                        <th class="px-4 py-4" scope="col">{{ __('ui.measurements.col_size') }}</th><th class="px-4 py-4" scope="col">{{ __('ui.common.finger_thumb') }}</th><th class="px-4 py-4" scope="col">{{ __('ui.common.finger_index') }}</th><th class="px-4 py-4" scope="col">{{ __('ui.common.finger_middle') }}</th><th class="px-4 py-4" scope="col">{{ __('ui.common.finger_ring') }}</th><th class="px-4 py-4" scope="col">{{ __('ui.common.finger_pinky') }}</th><th class="px-4 py-4" scope="col">{{ __('ui.measurements.col_tolerance') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 bg-white">
                    @forelse ($sizeStandards as $standard)
                        <tr class="transition hover:bg-stone-100/50">
                            <th class="px-4 py-4 font-bold text-stone-800" scope="row">{{ $standard->size_name }}</th>
                            <td class="px-4 py-4 tabular-nums">{{ $standard->jempol }} mm</td><td class="px-4 py-4 tabular-nums">{{ $standard->telunjuk }} mm</td><td class="px-4 py-4 tabular-nums">{{ $standard->tengah }} mm</td><td class="px-4 py-4 tabular-nums">{{ $standard->manis }} mm</td><td class="px-4 py-4 tabular-nums">{{ $standard->kelingking }} mm</td><td class="px-4 py-4 tabular-nums">±{{ $standard->tolerance }} mm</td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-8 text-center text-stone-500" colspan="7">{{ __('ui.measurements.size_chart_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>