<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Measurement;
use App\Models\NailCatalog;
use App\Models\User;
use App\Policies\MeasurementPolicy;
use App\Policies\NailCatalogPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Measurement::class, MeasurementPolicy::class);
        Gate::policy(NailCatalog::class, NailCatalogPolicy::class);
        Gate::define('access-admin', static fn (User $user): bool => $user->hasRole(UserRole::Admin));
    }
}
