<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HeroBanner;
use App\Models\Product;
use App\Services\MarketplaceService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly MarketplaceService $marketplaceService) {}

    public function index(): View
    {
        return view('welcome', [
            'catalogs' => $this->marketplaceService->mainProducts(null)->take(9),
            'reviews' => $this->marketplaceService->featuredReviews(),
            'heroBanners' => HeroBanner::query()->activeOrdered()->get(),
            'styleCategories' => Category::query()
                ->whereHas('products', fn ($query) => $query->where('is_active', true))
                ->with(['products' => fn ($query) => $query->active()->with('primaryImage')->latest()])
                ->orderBy('name')
                ->take(5)
                ->get(),
            'editorialProducts' => Product::query()
                ->active()
                ->with(['category', 'primaryImage'])
                ->latest()
                ->take(4)
                ->get(),
        ]);
    }

    public function dashboard(): View
    {
        return view('dashboard');
    }

    public function adminDashboard(): View
    {
        return view('admin.dashboard');
    }
}
