<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateCouponRequest extends StoreCouponRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $coupon = $this->route('coupon');

        return array_replace(parent::rules(), [
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('coupons', 'code')->ignore($coupon)],
        ]);
    }
}
