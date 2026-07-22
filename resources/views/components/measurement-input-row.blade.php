@props(['hand', 'finger', 'label', 'translation', 'required' => true])

@php($field = $hand.'_hand_data.'.$finger)

<div class="grid gap-3 rounded-2xl border border-[#92A1B5]/40 bg-white p-4 shadow-sm transition focus-within:border-[#92A1B5] focus-within:shadow-md sm:grid-cols-[1fr_9rem] sm:items-center">
    <label for="{{ $hand }}_{{ $finger }}">
        <span class="block font-semibold text-[#0C1C39]">{{ $label }} <em class="font-normal text-[#92A1B5]">({{ $translation }})</em></span>
    </label>
    <div>
        <div class="relative">
            <input class="w-full rounded-xl border border-[#92A1B5]/40 bg-[#F8FAFC] px-4 py-3 pr-11 text-right font-semibold tabular-nums text-[#0C1C39] outline-none transition placeholder:text-stone-300 focus:border-[#92A1B5] focus:bg-white focus:ring-4 focus:ring-[#92A1B5]/20" id="{{ $hand }}_{{ $finger }}" name="{{ $hand }}_hand_data[{{ $finger }}]" type="number" min="0" max="25" step="0.1" inputmode="decimal" placeholder="0.0" value="{{ old($field) }}" data-measurement-input @required($required)>
            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs font-semibold text-[#92A1B5]">mm</span>
        </div>
        <x-input-error :messages="$errors->get($field)" />
    </div>
</div>