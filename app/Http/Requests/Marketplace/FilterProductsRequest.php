<?php

declare(strict_types=1);

namespace App\Http\Requests\Marketplace;

use App\Enums\CatalogSize;
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
        return ['size' => ['nullable', Rule::enum(CatalogSize::class)]];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->view('errors.422', [
            'errors' => $validator->errors(),
        ], 422));
    }
}
