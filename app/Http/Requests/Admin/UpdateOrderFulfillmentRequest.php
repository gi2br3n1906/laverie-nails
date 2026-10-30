<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\FulfillmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderFulfillmentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'courier' => is_string($this->courier)
                ? trim($this->courier)
                : $this->courier,
            'tracking_number' => is_string($this->tracking_number)
                ? trim($this->tracking_number)
                : $this->tracking_number,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'fulfillment_status' => ['required', 'string', Rule::enum(FulfillmentStatus::class)],
            'courier' => [
                'nullable',
                'string',
                'max:50',
                Rule::requiredIf($this->string('fulfillment_status')->toString() === FulfillmentStatus::Shipped->value),
            ],
            'tracking_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf($this->string('fulfillment_status')->toString() === FulfillmentStatus::Shipped->value),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'courier.required' => 'Nama kurir wajib diisi saat pesanan dikirim.',
            'tracking_number.required' => 'Nomor resi wajib diisi saat pesanan dikirim.',
        ];
    }
}
