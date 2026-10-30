<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesRegion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCheckoutRequest extends FormRequest
{
    use ValidatesRegion;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...$this->regionRules(),
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email:rfc', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^[0-9+()\-\s]{8,30}$/'],
            'shipping_address' => ['required', 'string', 'min:5', 'max:1000'],
            'order_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $this->addRegionHierarchyErrors($validator);
        }];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'customer_name.required' => __('ui.validation.name_required'),
            'customer_email.required' => __('ui.validation.email_required'),
            'customer_email.email' => __('ui.validation.email_invalid'),
            'customer_phone.required' => __('ui.validation.phone_required'),
            'customer_phone.regex' => __('ui.validation.phone_invalid'),
            'shipping_address.required' => __('ui.validation.address_required'),
            'shipping_address.min' => __('ui.validation.address_min'),
        ];
    }
}
