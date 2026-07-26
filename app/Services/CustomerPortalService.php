<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\ValueObjects\CartSize;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerPortalService
{
    /** @return LengthAwarePaginator<int, Order> */
    public function orders(User $user): LengthAwarePaginator
    {
        return $user->orders()->latest()->paginate(10);
    }

    public function order(User $user, Order $order): Order
    {
        abort_unless($order->user_id === $user->getKey(), 404);

        return $order->load('items');
    }

    /** @param array<string, mixed> $validated */
    public function updateMeasurements(User $user, array $validated): void
    {
        $size = CartSize::fromValidated([
            'size_type' => 'custom',
            'custom_measurements' => $validated['custom_measurements'],
        ]);

        $user->update(['default_size_payload' => $size->payload]);
    }
}
