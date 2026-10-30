@props([
    'provinces' => [],
    'selected' => [],
    'idPrefix' => 'region',
])

@php
    $selectedProvince = old('province_code', $selected['province_code'] ?? null);
    $selectedRegency = old('regency_code', $selected['regency_code'] ?? null);
    $selectedDistrict = old('district_code', $selected['district_code'] ?? null);
    $selectedVillage = old('village_code', $selected['village_code'] ?? null);

    $selectedProvinceName = old('province_name', $selected['province_name'] ?? '');
    $selectedRegencyName = old('regency_name', $selected['regency_name'] ?? '');
    $selectedDistrictName = old('district_name', $selected['district_name'] ?? '');
    $selectedVillageName = old('village_name', $selected['village_name'] ?? '');

    $selectClass = 'mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20 disabled:cursor-not-allowed disabled:opacity-60';
@endphp

<div class="grid gap-5 sm:grid-cols-2" data-region-form>
    <div>
        <label class="block text-sm font-semibold text-[#0C1C39]" for="{{ $idPrefix }}-province">{{ __('ui.region.province') }}</label>
        <select
            class="{{ $selectClass }}"
            id="{{ $idPrefix }}-province"
            name="province_code"
            data-searchable-select
            data-region-level="province"
            data-child-url="{{ route('regions.regencies', ['province' => '__CODE__']) }}"
            autocomplete="address-level1"
            required
        >
            <option value="">{{ __('ui.region.select_province') }}</option>
            @foreach ($provinces as $code => $name)
                <option value="{{ $code }}" @selected((string) $selectedProvince === (string) $code)>{{ $name }}</option>
            @endforeach
        </select>
        <p class="mt-2 hidden text-sm font-semibold text-[#385273]" data-region-feedback></p>
        @error('province_code')<p class="mt-2 text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
        @error('province_name')<p class="mt-2 text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
        <input type="hidden" name="province_name" value="{{ $selectedProvinceName }}" data-region-name="province_code">
    </div>

    <div>
        <label class="block text-sm font-semibold text-[#0C1C39]" for="{{ $idPrefix }}-regency">{{ __('ui.region.regency') }}</label>
        <select
            class="{{ $selectClass }}"
            id="{{ $idPrefix }}-regency"
            name="regency_code"
            data-searchable-select
            data-region-level="regency"
            data-parent="province_code"
            data-child-url="{{ route('regions.districts', ['regency' => '__CODE__']) }}"
            autocomplete="address-level2"
            @disabled(blank($selectedProvince))
            required
        >
            <option value="">{{ __('ui.region.select_regency') }}</option>
            @if ($selectedRegency)
                <option value="{{ $selectedRegency }}" selected>{{ $selectedRegencyName }}</option>
            @endif
        </select>
        <p class="mt-2 hidden text-sm font-semibold text-[#385273]" data-region-feedback></p>
        @error('regency_code')<p class="mt-2 text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
        @error('regency_name')<p class="mt-2 text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
        <input type="hidden" name="regency_name" value="{{ $selectedRegencyName }}" data-region-name="regency_code">
    </div>

    <div>
        <label class="block text-sm font-semibold text-[#0C1C39]" for="{{ $idPrefix }}-district">{{ __('ui.region.district') }}</label>
        <select
            class="{{ $selectClass }}"
            id="{{ $idPrefix }}-district"
            name="district_code"
            data-searchable-select
            data-region-level="district"
            data-parent="regency_code"
            data-child-url="{{ route('regions.villages', ['district' => '__CODE__']) }}"
            autocomplete="address-level3"
            @disabled(blank($selectedRegency))
            required
        >
            <option value="">{{ __('ui.region.select_district') }}</option>
            @if ($selectedDistrict)
                <option value="{{ $selectedDistrict }}" selected>{{ $selectedDistrictName }}</option>
            @endif
        </select>
        <p class="mt-2 hidden text-sm font-semibold text-[#385273]" data-region-feedback></p>
        @error('district_code')<p class="mt-2 text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
        @error('district_name')<p class="mt-2 text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
        <input type="hidden" name="district_name" value="{{ $selectedDistrictName }}" data-region-name="district_code">
    </div>

    <div>
        <label class="block text-sm font-semibold text-[#0C1C39]" for="{{ $idPrefix }}-village">{{ __('ui.region.village') }}</label>
        <select
            class="{{ $selectClass }}"
            id="{{ $idPrefix }}-village"
            name="village_code"
            data-searchable-select
            data-region-level="village"
            data-parent="district_code"
            autocomplete="address-level4"
            @disabled(blank($selectedDistrict))
            required
        >
            <option value="">{{ __('ui.region.select_village') }}</option>
            @if ($selectedVillage)
                <option value="{{ $selectedVillage }}" selected>{{ $selectedVillageName }}</option>
            @endif
        </select>
        <p class="mt-2 hidden text-sm font-semibold text-[#385273]" data-region-feedback></p>
        @error('village_code')<p class="mt-2 text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
        @error('village_name')<p class="mt-2 text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
        <input type="hidden" name="village_name" value="{{ $selectedVillageName }}" data-region-name="village_code">
    </div>
</div>
