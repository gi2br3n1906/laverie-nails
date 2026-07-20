<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Measurement;
use App\Models\User;

class MeasurementPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Measurement $measurement): bool
    {
        return $this->owns($user, $measurement) || $user->hasRole(UserRole::Admin);
    }

    public function print(User $user, Measurement $measurement): bool
    {
        return $this->owns($user, $measurement);
    }

    public function delete(User $user, Measurement $measurement): bool
    {
        return $this->owns($user, $measurement);
    }

    private function owns(User $user, Measurement $measurement): bool
    {
        return $measurement->user_id !== null && $user->id === $measurement->user_id;
    }
}
