<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MarketplaceService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly MarketplaceService $marketplaceService) {}

    public function index(): View
    {
        return view('welcome', [
            'catalogs' => $this->marketplaceService->products(null)->take(8),
            'reviews' => $this->marketplaceService->featuredReviews(),
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
