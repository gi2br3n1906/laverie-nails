<?php

declare(strict_types=1);

namespace App\Http\Requests\Marketplace;

use App\Models\NailCatalog;
use App\Policies\CatalogReviewPolicy;
use Illuminate\Foundation\Http\FormRequest;

class StoreCatalogReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $catalog = $this->route('catalog');

        if ($catalog instanceof NailCatalog && ! $catalog->is_active) {
            abort(404);
        }

        return $catalog instanceof NailCatalog
            && $this->user() !== null
            && app(CatalogReviewPolicy::class)->create($this->user(), $catalog);
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
