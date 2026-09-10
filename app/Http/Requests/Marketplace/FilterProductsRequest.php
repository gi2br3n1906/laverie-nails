<?php

declare(strict_types=1);

namespace App\Http\Requests\Marketplace;

use App\Enums\CatalogSize;
use App\Models\Category;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class FilterProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:191', function (string $attribute, mixed $value, \Closure $fail): void {
                if (is_null($value) || $value === '') {
                    return;
                }

                if (! Category::query()
                    ->where('id', $value)
                    ->orWhere('slug', $value)
                    ->exists()) {
                    $fail('The selected category is invalid.');
                }
            }],
            'size' => ['nullable', Rule::in(array_column(CatalogSize::cases(), 'value'))],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => $this->filled('search') ? trim((string) $this->input('search')) : null,
            'category' => $this->filled('category') ? trim((string) $this->input('category')) : null,
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->view('errors.422', [
            'errors' => $validator->errors(),
        ], 422));
    }
}
