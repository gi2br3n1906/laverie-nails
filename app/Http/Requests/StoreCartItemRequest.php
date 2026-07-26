<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CartSizeType;
use App\Enums\CatalogSize;
use App\ValueObjects\CartSize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $fingerKeys = implode(',', CartSize::FINGERS);
        $rules = [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('is_active', true),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'size_type' => ['required', Rule::enum(CartSizeType::class)],
            'standard_size' => ['nullable', 'required_if:size_type,standard', Rule::enum(CatalogSize::class)],
            'custom_measurements' => ['nullable', 'required_if:size_type,custom', 'array:right_hand,left_hand'],
            'custom_measurements.right_hand' => ['nullable', 'required_if:size_type,custom', 'array:'.$fingerKeys],
            'custom_measurements.left_hand' => ['nullable', 'required_if:size_type,custom', 'array:'.$fingerKeys],
        ];

        foreach (CartSize::HANDS as $hand) {
            foreach (CartSize::FINGERS as $finger) {
                $rules["custom_measurements.{$hand}.{$finger}"] = [
                    'nullable',
                    'required_if:size_type,custom',
                    'numeric',
                    'min:0',
                    'max:25',
                ];
            }
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('quantity')) {
            $this->merge(['quantity' => 1]);
        }
    }
}
