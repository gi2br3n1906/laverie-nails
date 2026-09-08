<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CatalogSize;
use App\Models\CatalogReview;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class MarketplaceService
{
    /** @return Collection<int, Product> */
    public function products(?CatalogSize $size): Collection
    {
        return $this->mainProducts($size);
    }

    /** @return Collection<int, Product> */
    public function mainProducts(?CatalogSize $size): Collection
    {
        return Product::query()->active()
            ->when($size, fn ($query) => $query->whereJsonContains('available_sizes', $size->value))
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->with(['category', 'primaryImage'])->latest()->get();
    }

    /** @return Collection<int, CatalogReview> */
    public function featuredReviews(int $limit = 3): Collection
    {
        return CatalogReview::query()
            ->whereHas('product', fn ($query) => $query->active())
            ->with(['user', 'product'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
