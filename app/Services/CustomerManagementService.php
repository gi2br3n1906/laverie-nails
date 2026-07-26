<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerManagementService
{
    /** @return LengthAwarePaginator<int, User> */
    public function customers(): LengthAwarePaginator
    {
        return User::query()
            ->whereJsonContains('roles', UserRole::User->value)
            ->withCount('orders')
            ->withSum([
                'orders as total_spent' => fn ($query) => $query->where('payment_status', PaymentStatus::Paid->value),
            ], 'grand_total')
            ->latest()
            ->paginate(15);
    }
}