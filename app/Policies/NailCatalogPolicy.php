<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class NailCatalogPolicy
{
    public function manage(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
