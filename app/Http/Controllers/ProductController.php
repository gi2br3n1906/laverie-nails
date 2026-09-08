<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CatalogSize;
use App\Http\Requests\Marketplace\FilterProductsRequest;
use App\Services\MarketplaceService;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly MarketplaceService $marketplaceService) {}

    public function index(FilterProductsRequest $request): View
    {
        $size = $request->filled('size') ? CatalogSize::from($request->validated('size')) : null;

        return view('products.index', [
            'catalogs' => $this->marketplaceService->mainProducts($size),
            'selectedSize' => $size,
            'sizes' => CatalogSize::cases(),
        ]);
    }
}
