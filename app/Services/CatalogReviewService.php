<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CatalogReview;
use App\Models\NailCatalog;
use App\Models\User;
use Illuminate\Database\ConnectionInterface;

class CatalogReviewService
{
    public function __construct(private readonly ConnectionInterface $database) {}

    /** @param array{rating: int|string, comment: string} $attributes */
    public function create(User $user, NailCatalog $catalog, array $attributes): CatalogReview
    {
        return $this->database->transaction(fn (): CatalogReview => CatalogReview::query()->create([
            ...$attributes,
            'catalog_id' => $catalog->id,
            'user_id' => $user->id,
        ]));
    }
}
