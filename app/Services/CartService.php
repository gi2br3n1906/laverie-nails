<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\ValueObjects\CartOwner;
use App\ValueObjects\CartSize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function add(CartOwner $owner, int $productId, CartSize $size, int $quantity): CartItem
    {
        return DB::transaction(function () use ($owner, $productId, $size, $quantity): CartItem {
            $product = Product::query()->lockForUpdate()->find($productId);

            if (! $product || ! $product->is_active) {
                throw ValidationException::withMessages([
                    'product_id' => 'Produk tidak tersedia untuk dibeli.',
                ]);
            }

            $existingItem = $owner->scope(CartItem::query())
                ->where('product_id', $product->id)
                ->where('size_type', $size->type->value)
                ->where('size_signature', $size->signature)
                ->lockForUpdate()
                ->first();
            $combinedQuantity = ($existingItem?->quantity ?? 0) + $quantity;

            $this->ensureStock($product, $combinedQuantity);

            if ($existingItem) {
                $existingItem->update(['quantity' => $combinedQuantity]);

                return $existingItem->refresh();
            }

            return CartItem::query()->create([
                ...$owner->attributes(),
                'product_id' => $product->id,
                'quantity' => $quantity,
                'size_type' => $size->type,
                'size_payload' => $size->payload,
                'size_signature' => $size->signature,
            ]);
        });
    }

    public function updateQuantity(CartOwner $owner, int $cartItemId, int $quantity): CartItem
    {
        return DB::transaction(function () use ($owner, $cartItemId, $quantity): CartItem {
            $cartItem = $this->ownedQuery($owner)
                ->whereKey($cartItemId)
                ->lockForUpdate()
                ->firstOrFail();
            $product = Product::query()->lockForUpdate()->findOrFail($cartItem->product_id);

            $this->ensureStock($product, $quantity);
            $cartItem->update(['quantity' => $quantity]);

            return $cartItem->refresh();
        });
    }

    public function remove(CartOwner $owner, int $cartItemId): void
    {
        DB::transaction(function () use ($owner, $cartItemId): void {
            $this->ownedQuery($owner)
                ->whereKey($cartItemId)
                ->lockForUpdate()
                ->firstOrFail()
                ->delete();
        });
    }

    /** @return Collection<int, CartItem> */
    public function items(CartOwner $owner): Collection
    {
        return $this->ownedQuery($owner)
            ->with(['product.category', 'product.primaryImage'])
            ->latest('id')
            ->get();
    }

    public function quantity(CartOwner $owner): int
    {
        return (int) $this->ownedQuery($owner)->sum('quantity');
    }

    /** @param  Collection<int, CartItem>  $items */
    public function grandTotalInCents(Collection $items): int
    {
        return $items->sum(fn (CartItem $item): int => $item->subtotalInCents());
    }

    /** @return Builder<CartItem> */
    private function ownedQuery(CartOwner $owner): Builder
    {
        return $owner->scope(CartItem::query());
    }

    private function ensureStock(Product $product, int $quantity): void
    {
        if ($quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Stok tidak mencukupi. Maksimal {$product->stock} item.",
            ]);
        }
    }
}
