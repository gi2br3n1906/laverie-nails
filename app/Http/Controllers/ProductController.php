<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CatalogSize;
use App\Http\Requests\Marketplace\FilterProductsRequest;
use App\Models\Category;
use App\Services\MarketplaceService;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly MarketplaceService $marketplaceService) {}

    public function index(FilterProductsRequest $request): View
    {
        $validated = $request->validated();
        $size = $validated['size'] ?? null;
        $category = $validated['category'] ?? null;
        $search = $validated['search'] ?? null;

        $selectedSize = is_string($size) ? CatalogSize::tryFrom($size) : null;

        return view('products.index', [
            'categories' => Category::query()->orderBy('name')->get(),
            'catalogs' => $this->marketplaceService->filteredProducts(
                search: $search,
                category: $category,
                size: $selectedSize?->value,
            ),
            'selectedCategory' => $category,
            'selectedSize' => $selectedSize,
            'search' => $search,
            'sizes' => CatalogSize::cases(),
        ]);
    }
}
