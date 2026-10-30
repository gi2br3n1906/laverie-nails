<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\Http\Requests\Concerns\ValidatesRegion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateShippingAddressRequest extends FormRequest
{
    use ValidatesRegion;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...$this->regionRules(),
            'address' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $this->addRegionHierarchyErrors($validator);
        }];
    }
}
