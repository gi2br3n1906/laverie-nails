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

    /** @param array{address: string, province_code: string, province_name: string, regency_code: string, regency_name: string, district_code: string, district_name: string, village_code: string, village_name: string} $attributes */
    public function updateAddress(User $user, array $attributes): void
    {
        $user->update($attributes);
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->update(['password' => $password]);
    }
}
