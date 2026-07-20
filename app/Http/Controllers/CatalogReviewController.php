<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Marketplace\StoreCatalogReviewRequest;
use App\Models\NailCatalog;
use App\Services\CatalogReviewService;
use App\Services\MarketplaceService;
use Illuminate\Http\RedirectResponse;

class CatalogReviewController extends Controller
{
    public function __construct(
        private readonly CatalogReviewService $reviewService,
        private readonly MarketplaceService $marketplaceService,
    ) {}

    public function store(StoreCatalogReviewRequest $request, NailCatalog $catalog): RedirectResponse
    {
        $catalog = $this->marketplaceService->publicProduct($catalog);
        $this->reviewService->create($request->user(), $catalog, $request->validated());

        return redirect()->route('products.show', $catalog)->with('status', 'Ulasan Anda berhasil disimpan.');
    }
}
