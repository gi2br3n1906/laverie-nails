<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CatalogSize;
use App\Models\CatalogReview;
use App\Models\NailCatalog;
use Illuminate\Database\Eloquent\Collection;

class MarketplaceService
{
    /** @return Collection<int, NailCatalog> */
    public function products(?CatalogSize $size): Collection
    {
        return NailCatalog::query()
            ->publiclyVisible()
            ->forSize($size)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->get();
    }

    /** @return Collection<int, CatalogReview> */
    public function featuredReviews(int $limit = 3): Collection
    {
        return CatalogReview::query()
            ->whereHas('catalog', fn ($query) => $query->publiclyVisible())
            ->with(['user', 'catalog'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function publicProduct(NailCatalog $catalog): NailCatalog
    {
        return NailCatalog::query()
            ->publiclyVisible()
            ->with('reviews.user')
            ->withAvg('reviews', 'rating')
            ->findOrFail($catalog->id);
    }
}
