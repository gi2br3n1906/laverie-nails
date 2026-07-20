<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\NailCatalog;
use App\Models\User;

class CatalogReviewPolicy
{
    public function create(User $user, NailCatalog $catalog): bool
    {
        return $user->hasRole(UserRole::User)
            && ! $user->hasRole(UserRole::Admin)
            && $catalog->is_active
            && ! $catalog->reviews()->whereBelongsTo($user)->exists();
    }
}
