@props(['hand', 'finger', 'label', 'hint', 'required' => true])

@php($field = $hand.'_hand_data.'.$finger)

<div class="grid gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm transition focus-within:border-stone-300 focus-within:shadow-md sm:grid-cols-[1fr_9rem] sm:items-center">
    <label for="{{ $hand }}_{{ $finger }}">
        <span class="block font-semibold text-stone-800">{{ $label }}</span>
        <span class="mt-1 block text-xs leading-5 text-stone-500">{{ $hint }}</span>
    </label>
    <div>
        <div class="relative">
            <input class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 pr-11 text-right font-semibold tabular-nums outline-none transition placeholder:text-stone-300 focus:border-stone-500 focus:bg-white focus:ring-4 focus:ring-stone-200" id="{{ $hand }}_{{ $finger }}" name="{{ $hand }}_hand_data[{{ $finger }}]" type="number" min="0" max="25" step="0.1" inputmode="decimal" placeholder="0.0" value="{{ old($field) }}" data-measurement-input @required($required)>
            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs font-semibold text-stone-400">mm</span>
        </div>
        <x-input-error :messages="$errors->get($field)" />
    </div>
</div>