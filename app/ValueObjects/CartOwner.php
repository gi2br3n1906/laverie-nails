<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final readonly class CartOwner
{
    private function __construct(
        public ?int $userId,
        public ?string $sessionId,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $userId = $request->user()?->getAuthIdentifier();

        if ($userId !== null) {
            return new self((int) $userId, null);
        }

        $guestId = $request->session()->get('cart.guest_id');

        if (! is_string($guestId) || $guestId === '') {
            $guestId = (string) Str::uuid();
            $request->session()->put('cart.guest_id', $guestId);
        }

        return new self(null, $guestId);
    }

    /** @return array{user_id: int|null, session_id: string|null} */
    public function attributes(): array
    {
        return [
            'user_id' => $this->userId,
            'session_id' => $this->sessionId,
        ];
    }

    /** @return array{user_id: int|null, guest_id: string|null} */
    public function orderAttributes(): array
    {
        return [
            'user_id' => $this->userId,
            'guest_id' => $this->sessionId,
        ];
    }

    public function ownsOrder(Order $order): bool
    {
        if ($this->userId !== null) {
            return $order->user_id === $this->userId && $order->guest_id === null;
        }

        return $order->user_id === null && hash_equals((string) $this->sessionId, (string) $order->guest_id);
    }

    /** @param  Builder<CartItem>  $query */
    public function scope(Builder $query): Builder
    {
        if ($this->userId !== null) {
            return $query->where('user_id', $this->userId)->whereNull('session_id');
        }

        return $query->whereNull('user_id')->where('session_id', $this->sessionId);
    }
}
