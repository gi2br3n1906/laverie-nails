<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\ValueObjects\CartOwner;
use App\ValueObjects\CartSize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
                'length' => $size->payload['length'] ?? null,
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

    public function setSelected(CartOwner $owner, int $cartItemId, bool $isSelected): CartItem
    {
        return DB::transaction(function () use ($owner, $cartItemId, $isSelected): CartItem {
            $cartItem = $this->ownedQuery($owner)
                ->whereKey($cartItemId)
                ->lockForUpdate()
                ->firstOrFail();

            $cartItem->update(['is_selected' => $isSelected]);

            return $cartItem->refresh();
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

    /** @return array{items: list<array<string, int|string|null>>, quantity: int, total: int} */
    public function state(CartOwner $owner, ?Coupon $coupon = null): array
    {
        $items = $this->items($owner);
        $selectedItems = $items->where('is_selected', true);
        $subtotal = $this->grandTotalInCents($selectedItems);
        $subtotalInRupiah = intdiv($subtotal, 100);
        $discountInRupiah = $coupon?->discountFor($subtotalInRupiah) ?? 0;
        $appliedCoupon = $discountInRupiah > 0 ? $coupon : null;
        $discount = $discountInRupiah * 100;

        return [
            'items' => $items->map(function (CartItem $item): array {
                $product = $item->product;
                $standardSize = $item->size_payload['size'] ?? null;
                $length = $item->length ?? ($item->size_payload['length'] ?? null);

                return [
                    'id' => $item->id,
                    'name' => $product->name,
                    'product_url' => route('storefront.products.show', $product),
                    'image_url' => $product->primaryImage
                        ? Storage::disk('public')->url($product->primaryImage->image_path)
                        : null,
                    'size_label' => $standardSize ? 'SIZE: '.strtoupper((string) $standardSize) : 'SIZE: CUSTOM',
                    'length' => $length,
                    'length_label' => $length ? 'LENGTH: '.strtoupper((string) $length) : null,
                    'unit_price' => intdiv($item->subtotalInCents(), $item->quantity),
                    'subtotal' => $item->subtotalInCents(),
                    'quantity' => $item->quantity,
                    'max_quantity' => $product->stock,
                    'update_url' => route('cart.update', $item),
                    'selection_url' => route('cart.selection', $item),
                    'is_selected' => (bool) $item->is_selected,
                    'remove_url' => route('cart.destroy', $item),
                ];
            })->values()->all(),
            'quantity' => (int) $selectedItems->sum('quantity'),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $subtotal - $discount,
            'coupon' => $appliedCoupon ? [
                'code' => $appliedCoupon->code,
                'discount_percentage' => $appliedCoupon->discount_percentage,
                'discount_type' => $appliedCoupon->discount_type,
                'discount_label' => $appliedCoupon->discount_type === 'fixed'
                    ? 'Rp '.number_format($appliedCoupon->discount_amount, 0, ',', '.')
                    : $appliedCoupon->discount_percentage.'%',
            ] : null,
        ];
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
