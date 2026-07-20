@props([
    'action',
    'method' => 'POST',
    'sizeStandard' => null,
    'submitLabel',
])

@php
    $fingerFields = [
        'jempol' => 'Thumb / Jempol',
        'telunjuk' => 'Index / Telunjuk',
        'tengah' => 'Middle / Tengah',
        'manis' => 'Ring / Manis',
        'kelingking' => 'Pinky / Kelingking',
    ];
@endphp

<form class="space-y-8" method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label class="mb-2 block text-sm font-semibold text-stone-700" for="size_name">Canonical size</label>
        <select class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none focus:border-stone-600 focus:ring-4 focus:ring-stone-200" id="size_name" name="size_name" required>
            <option value="">Select a size</option>
            @foreach (['XS', 'S', 'M', 'L'] as $sizeName)
                <option value="{{ $sizeName }}" @selected(old('size_name', $sizeStandard?->size_name) === $sizeName)>{{ $sizeName }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('size_name')" />
    </div>

    <div>
        <h2 class="font-serif text-lg font-bold text-stone-900">Finger widths</h2>
        <p class="mt-1 text-sm text-stone-600">Enter each empirical width in millimeters with at most one decimal place.</p>

        <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($fingerFields as $field => $label)
                <div>
                    <label class="mb-2 block text-sm font-semibold text-stone-700" for="{{ $field }}">{{ $label }} (mm)</label>
                    <input
                        class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none focus:border-stone-600 focus:ring-4 focus:ring-stone-200"
                        id="{{ $field }}"
                        name="{{ $field }}"
                        type="number"
                        min="0"
                        max="25"
                        step="0.1"
                        value="{{ old($field, $sizeStandard?->{$field}) }}"
                        required
                    >
                    <x-input-error :messages="$errors->get($field)" />
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-stone-700" for="tolerance">Tolerance (mm)</label>
            <input
                class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none focus:border-stone-600 focus:ring-4 focus:ring-stone-200"
                id="tolerance"
                name="tolerance"
                type="number"
                min="0"
                max="25"
                step="0.1"
                value="{{ old('tolerance', $sizeStandard?->tolerance ?? '1.0') }}"
                required
            >
            <x-input-error :messages="$errors->get('tolerance')" />
        </div>

        <div class="flex items-end pb-3">
            <label class="flex items-center gap-3 text-sm font-semibold text-stone-700">
                <input type="hidden" name="is_active" value="0">
                <input
                    class="size-5 rounded border-stone-300 text-stone-900 focus:ring-stone-600"
                    name="is_active"
                    type="checkbox"
                    value="1"
                    @checked((bool) old('is_active', $sizeStandard?->is_active ?? true))
                >
                Active classification standard
            </label>
            <x-input-error :messages="$errors->get('is_active')" />
        </div>
    </div>

    <div class="flex flex-wrap gap-3 border-t border-stone-200 pt-6">
        <button class="rounded-xl bg-stone-900 px-5 py-3 font-semibold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-stone-300" type="submit">
            {{ $submitLabel }}
        </button>
        <a class="rounded-xl border border-stone-300 bg-white px-5 py-3 font-semibold text-stone-700 transition hover:bg-stone-50" href="{{ route('admin.size-standards.index') }}">
            Cancel
        </a>
    </div>
</form>