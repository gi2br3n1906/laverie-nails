<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'address' => ['required', 'string', 'max:2000'],
            'province_id' => ['required', 'string', 'max:50'],
            'city_id' => ['required', 'string', 'max:50'],
        ];
    }
}
