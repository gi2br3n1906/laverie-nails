<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Marketplace\StoreCatalogReviewRequest;
use App\Models\Product;
use App\Services\CatalogReviewService;
use Illuminate\Http\RedirectResponse;

class CatalogReviewController extends Controller
{
    public function __construct(
        private readonly CatalogReviewService $reviewService,
    ) {}

    public function store(StoreCatalogReviewRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);
        $this->reviewService->create($request->user(), $product, $request->validated());

        return redirect()->route('storefront.products.show', $product)->with('status', 'Ulasan Anda berhasil disimpan.');
    }
}
