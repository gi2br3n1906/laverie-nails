<x-layouts.app title="Hero Banners">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-bold uppercase tracking-[0.2em] text-stone-600">Homepage carousel</p><h1 class="mt-3 font-serif text-5xl font-semibold">Hero Banners</h1><p class="mt-4 text-stone-600">Upload, order, activate, and replace homepage carousel images.</p></div>
        <a class="rounded-full bg-stone-900 px-6 py-3 text-center font-semibold text-white" href="{{ route('admin.banners.create') }}">Add banner</a>
    </div>

    @if (session('status'))<div class="mt-6 rounded-2xl bg-emerald-50 px-5 py-4 text-emerald-800">{{ session('status') }}</div>@endif

    <div class="mt-10 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-100 text-left text-sm">
                <thead class="bg-stone-100 text-xs uppercase tracking-wider text-stone-800"><tr><th class="px-5 py-4">Preview</th><th class="px-5 py-4">Sequence</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Actions</th></tr></thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($banners as $banner)
                        <tr>
                            <td class="px-5 py-4"><img class="h-20 w-36 rounded-xl bg-stone-100 object-cover" src="{{ Storage::disk('public')->url($banner->image_path) }}" alt="Hero banner sequence {{ $banner->sequence }}"></td>
                            <td class="px-5 py-4 font-semibold tabular-nums">{{ $banner->sequence }}</td>
                            <td class="px-5 py-4">{{ $banner->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="px-5 py-4"><div class="flex items-center gap-4"><a class="font-semibold text-stone-900" href="{{ route('admin.banners.edit', $banner) }}">Edit</a><form method="POST" action="{{ route('admin.banners.destroy', $banner) }}">@csrf @method('DELETE')<button class="font-semibold text-stone-600" type="submit">Delete</button></form></div></td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-8 text-center text-stone-500" colspan="4">No hero banners have been uploaded. The static fallback remains active.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>