<?php

declare(strict_types=1);

namespace App\Http\Requests\Marketplace;

use App\Models\Product;
use App\Policies\CatalogReviewPolicy;
use Illuminate\Foundation\Http\FormRequest;

class StoreCatalogReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        if ($product instanceof Product && ! $product->is_active) {
            abort(404);
        }

        return $product instanceof Product
            && $this->user() !== null
            && app(CatalogReviewPolicy::class)->create($this->user(), $product);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:3', 'max:2000'],
        ];
    }
}
