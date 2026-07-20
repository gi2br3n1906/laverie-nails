<x-layouts.app title="Size Standards">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-600">Single source of truth</p>
            <h1 class="font-serif mt-2 text-3xl font-bold tracking-tight">Size standards</h1>
            <p class="mt-3 max-w-2xl text-stone-600">Manage the empirical millimeter values used by the future classification service.</p>
        </div>

        <a class="rounded-xl bg-stone-900 px-5 py-3 text-center font-semibold text-white transition hover:bg-stone-800" href="{{ route('admin.size-standards.create') }}">
            Add standard
        </a>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-8 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-left text-sm">
                <thead class="bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                    <tr>
                        <th class="px-5 py-4" scope="col">Size</th>
                        <th class="px-5 py-4" scope="col">Thumb</th>
                        <th class="px-5 py-4" scope="col">Index</th>
                        <th class="px-5 py-4" scope="col">Middle</th>
                        <th class="px-5 py-4" scope="col">Ring</th>
                        <th class="px-5 py-4" scope="col">Pinky</th>
                        <th class="px-5 py-4" scope="col">Tolerance</th>
                        <th class="px-5 py-4" scope="col">Status</th>
                        <th class="px-5 py-4 text-right" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($sizeStandards as $sizeStandard)
                        <tr>
                            <td class="px-5 py-4 font-bold text-stone-900">{{ $sizeStandard->size_name }}</td>
                            <td class="px-5 py-4">{{ $sizeStandard->jempol }} mm</td>
                            <td class="px-5 py-4">{{ $sizeStandard->telunjuk }} mm</td>
                            <td class="px-5 py-4">{{ $sizeStandard->tengah }} mm</td>
                            <td class="px-5 py-4">{{ $sizeStandard->manis }} mm</td>
                            <td class="px-5 py-4">{{ $sizeStandard->kelingking }} mm</td>
                            <td class="px-5 py-4">{{ $sizeStandard->tolerance }} mm</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-800' => $sizeStandard->is_active,
                                    'bg-stone-100 text-stone-600' => ! $sizeStandard->is_active,
                                ])>
                                    {{ $sizeStandard->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-3">
                                    <a class="font-semibold text-stone-900 hover:text-stone-800" href="{{ route('admin.size-standards.show', $sizeStandard) }}">View</a>
                                    <a class="font-semibold text-stone-600 hover:text-stone-900" href="{{ route('admin.size-standards.edit', $sizeStandard) }}">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-5 py-12 text-center text-stone-500" colspan="9">No size standards are available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>