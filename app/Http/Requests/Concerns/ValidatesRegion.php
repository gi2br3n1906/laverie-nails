<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesRegion
{
    /** @return array<string, mixed> */
    protected function regionRules(): array
    {
        return [
            'province_code' => ['required', 'string', Rule::in(array_keys(config('regions.provinces', [])))],
            'province_name' => ['required', 'string', 'max:100', Rule::in(array_values(config('regions.provinces', [])))],
            'regency_code' => ['required', 'string', 'regex:/^\d{2}\.\d{2}$/'],
            'regency_name' => ['required', 'string', 'max:100'],
            'district_code' => ['required', 'string', 'regex:/^\d{2}\.\d{2}\.\d{2}$/'],
            'district_name' => ['required', 'string', 'max:100'],
            'village_code' => ['required', 'string', 'regex:/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/'],
            'village_name' => ['required', 'string', 'max:100'],
        ];
    }

    protected function addRegionHierarchyErrors(Validator $validator): void
    {
        $province = (string) $this->input('province_code', '');
        $provinceName = (string) $this->input('province_name', '');
        $regency = (string) $this->input('regency_code', '');
        $district = (string) $this->input('district_code', '');
        $village = (string) $this->input('village_code', '');

        $expectedProvinceName = config('regions.provinces.'.$province);

        if (is_string($expectedProvinceName) && $provinceName !== '' && $provinceName !== $expectedProvinceName) {
            $validator->errors()->add('province_name', 'Nama provinsi tidak sesuai dengan kode provinsi.');
        }

        if ($province !== '' && $regency !== '' && ! str_starts_with($regency, $province.'.')) {
            $validator->errors()->add('regency_code', 'Kabupaten/kota tidak sesuai dengan provinsi.');
        }

        if ($regency !== '' && $district !== '' && ! str_starts_with($district, $regency.'.')) {
            $validator->errors()->add('district_code', 'Kecamatan tidak sesuai dengan kabupaten/kota.');
        }

        if ($district !== '' && $village !== '' && ! str_starts_with($village, $district.'.')) {
            $validator->errors()->add('village_code', 'Kelurahan/desa tidak sesuai dengan kecamatan.');
        }
    }
}
