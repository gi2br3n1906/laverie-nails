<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('coupons', 'code')],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_percentage' => ['required_if:discount_type,percentage', 'nullable', 'integer', 'between:1,100'],
            'discount_amount' => ['required_if:discount_type,fixed', 'nullable', 'integer', 'min:1', 'max:100000000'],
            'minimum_order_amount' => ['required', 'integer', 'min:0', 'max:100000000'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'discount_percentage' => $this->input('discount_type') === 'percentage' ? $this->input('discount_percentage') : null,
            'discount_amount' => $this->input('discount_type') === 'fixed' ? $this->input('discount_amount') : null,
        ]);
    }
}
