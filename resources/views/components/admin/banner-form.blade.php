@props(['banner' => null])

@php($editing = $banner !== null)

<div class="grid gap-6">
    @if ($editing)
        <div>
            <p class="text-sm font-semibold">Current banner</p>
            <img class="mt-3 aspect-video w-full rounded-3xl bg-stone-100 object-cover" src="{{ Storage::disk('public')->url($banner->image_path) }}" alt="Current hero banner">
        </div>
    @endif

    <div>
        <label class="text-sm font-semibold" for="image">{{ $editing ? 'Replace image (optional)' : 'Banner image' }}</label>
        <input class="mt-2 block w-full rounded-2xl border border-dashed border-stone-300 bg-stone-100 px-4 py-6 text-sm" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @required(! $editing)>
        <p class="mt-2 text-xs text-stone-500">JPG, PNG, or WebP up to 5 MB. A wide landscape image is recommended.</p>
        <x-input-error :messages="$errors->get('image')" />
    </div>

    <div>
        <label class="text-sm font-semibold" for="sequence">Display sequence</label>
        <input class="mt-2 w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 outline-none focus:border-stone-500 focus:ring-4 focus:ring-stone-200" id="sequence" name="sequence" type="number" min="0" max="10000" step="1" value="{{ old('sequence', $banner?->sequence ?? 0) }}" required>
        <p class="mt-2 text-xs text-stone-500">Lower numbers appear first in the homepage carousel.</p>
        <x-input-error :messages="$errors->get('sequence')" />
    </div>

    <label class="flex items-center gap-3">
        <input class="size-5 rounded border-stone-300 text-stone-900 focus:ring-stone-300" name="is_active" type="checkbox" value="1" @checked(old('is_active', $banner?->is_active ?? true))>
        <span class="text-sm font-semibold">Show this banner on the homepage</span>
    </label>
</div>