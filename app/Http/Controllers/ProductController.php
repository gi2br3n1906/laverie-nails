<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CatalogSize;
use App\Http\Requests\Marketplace\FilterProductsRequest;
use App\Models\NailCatalog;
use App\Policies\CatalogReviewPolicy;
use App\Services\MarketplaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly MarketplaceService $marketplaceService) {}

    public function index(FilterProductsRequest $request): View
    {
        $size = $request->filled('size') ? CatalogSize::from($request->validated('size')) : null;

        return view('products.index', [
            'catalogs' => $this->marketplaceService->products($size),
            'selectedSize' => $size,
            'sizes' => CatalogSize::cases(),
        ]);
    }

    public function show(Request $request, NailCatalog $catalog, CatalogReviewPolicy $reviewPolicy): View
    {
        $catalog = $this->marketplaceService->publicProduct($catalog);

        return view('products.show', [
            'catalog' => $catalog,
            'canReview' => $request->user() !== null && $reviewPolicy->create($request->user(), $catalog),
        ]);
    }
}
