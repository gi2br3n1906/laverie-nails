<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;

class CatalogReviewPolicy
{
    public function create(User $user, Product $product): bool
    {
        return $user->hasRole(UserRole::User)
            && ! $user->hasRole(UserRole::Admin)
            && $product->is_active
            && ! $product->reviews()->whereBelongsTo($user)->exists();
    }
}
