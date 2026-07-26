<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class ProfileService
{
    /** @param array{name: string, email: string, phone?: string|null} $attributes */
    public function updateInformation(User $user, array $attributes): void
    {
        if ($user->email !== $attributes['email']) {
            $user->email_verified_at = null;
        }

        $user->fill($attributes)->save();
    }

    /** @param array{address: string, province_id: string, city_id: string} $attributes */
    public function updateAddress(User $user, array $attributes): void
    {
        $user->update($attributes);
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->update(['password' => $password]);
    }
}
