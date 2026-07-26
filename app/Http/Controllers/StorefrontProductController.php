<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CatalogSize;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class StorefrontProductController extends Controller
{
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'primaryImage', 'images']);

        return view('storefront.products.show', [
            'product' => $product,
            'standardSizes' => CatalogSize::cases(),
        ]);
    }
}
