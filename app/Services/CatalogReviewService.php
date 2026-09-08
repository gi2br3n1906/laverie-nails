<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CatalogReview;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\ConnectionInterface;

class CatalogReviewService
{
    public function __construct(private readonly ConnectionInterface $database) {}

    /** @param array{rating: int|string, comment: string} $attributes */
    public function create(User $user, Product $product, array $attributes): CatalogReview
    {
        return $this->database->transaction(fn (): CatalogReview => CatalogReview::query()->create([
            ...$attributes,
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]));
    }
}
