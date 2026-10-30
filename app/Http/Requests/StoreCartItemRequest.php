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
            'length' => ['nullable', 'string', Rule::in(['Short', 'Medium', 'Long'])],
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

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'size_type.required' => 'Tipe ukuran wajib dipilih.',
            'size_type.enum' => 'Tipe ukuran tidak dikenali.',
            'standard_size.required_if' => 'Ukuran standar wajib dipilih.',
            'standard_size.enum' => 'Ukuran standar tidak dikenali.',
            'custom_measurements.required_if' => 'Ukuran custom wajib diisi.',
            'product_id.exists' => 'Produk tidak tersedia.',
            'quantity.min' => 'Jumlah minimal 1 item.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'size_type' => 'tipe ukuran',
            'standard_size' => 'ukuran standar',
            'custom_measurements' => 'ukuran custom',
            'product_id' => 'produk',
            'quantity' => 'jumlah',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('quantity')) {
            $this->merge(['quantity' => 1]);
        }
    }
}
