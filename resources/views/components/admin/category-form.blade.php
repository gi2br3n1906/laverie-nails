@props(['category' => null])

<div>
    <label class="text-sm font-semibold" for="name">Nama kategori</label>
    <input class="mt-2 w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 outline-none focus:border-stone-500 focus:ring-4 focus:ring-stone-200" id="name" name="name" value="{{ old('name', $category?->name) }}" required maxlength="100">
    <p class="mt-2 text-xs text-stone-500">Slug URL dibuat otomatis dari nama kategori.</p>
    <x-input-error :messages="$errors->get('name')" />
    <x-input-error :messages="$errors->get('slug')" />
</div>