<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Services\CartService;
use App\ValueObjects\CartOwner;
use App\ValueObjects\CartSize;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function index(Request $request): View
    {
        $items = $this->cartService->items(CartOwner::fromRequest($request));

        return view('cart.index', [
            'items' => $items,
            'grandTotalInCents' => $this->cartService->grandTotalInCents($items),
        ]);
    }

    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->cartService->add(
            CartOwner::fromRequest($request),
            (int) $validated['product_id'],
            CartSize::fromValidated($validated),
            (int) $validated['quantity'],
        );

        return to_route('cart.index')->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        $this->cartService->updateQuantity(
            CartOwner::fromRequest($request),
            $cartItem->id,
            (int) $request->validated('quantity'),
        );

        return to_route('cart.index')->with('status', 'Jumlah produk diperbarui.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->cartService->remove(CartOwner::fromRequest($request), $cartItem->id);

        return to_route('cart.index')->with('status', 'Produk dihapus dari keranjang.');
    }
}
