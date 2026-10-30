<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Exceptions\GoogleAuthException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class GoogleAuthService
{
    public function findOrCreateUser(SocialiteUser $googleUser): User
    {
        $providerId = trim((string) $googleUser->getId());
        $email = Str::lower(trim((string) $googleUser->getEmail()));
        $name = trim((string) $googleUser->getName());

        if ($providerId === '' || $email === '') {
            throw new GoogleAuthException('We could not read your Google account details. Please try again.');
        }

        if (! $this->hasVerifiedEmail($googleUser)) {
            throw new GoogleAuthException('Your Google account email is not verified.');
        }

        return DB::transaction(function () use ($providerId, $email, $name): User {
            $user = User::query()
                ->where('provider', 'google')
                ->where('provider_id', $providerId)
                ->first();

            if ($user !== null) {
                $this->ensureCustomerCanUseGoogle($user);

                return $user;
            }

            $user = User::query()->where('email', $email)->first();

            if ($user !== null) {
                $this->ensureCustomerCanUseGoogle($user);
                $this->ensureEmailCanBeLinked($user, $providerId);

                $user->forceFill([
                    'provider' => 'google',
                    'provider_id' => $providerId,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                return $user;
            }

            $user = User::query()->create([
                'name' => $name !== '' ? $name : Str::before($email, '@'),
                'email' => $email,
                'password' => null,
                'provider' => 'google',
                'provider_id' => $providerId,
                'roles' => [UserRole::User->value],
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();

            return $user;
        });
    }

    private function ensureCustomerCanUseGoogle(User $user): void
    {
        if ($user->hasRole(UserRole::Admin)) {
            throw new GoogleAuthException('Admin accounts must sign in with email and password.');
        }
    }

    private function ensureEmailCanBeLinked(User $user, string $providerId): void
    {
        if (filled($user->provider) && $user->provider !== 'google') {
            throw new GoogleAuthException('This email is already linked to a different sign-in method.');
        }

        if (filled($user->provider_id) && (string) $user->provider_id !== $providerId) {
            throw new GoogleAuthException('This email is already linked to a different Google account.');
        }
    }

    private function hasVerifiedEmail(SocialiteUser $googleUser): bool
    {
        $raw = $googleUser->getRaw();

        return filter_var(
            $raw['email_verified'] ?? $raw['verified_email'] ?? false,
            FILTER_VALIDATE_BOOL,
        );
    }
}
