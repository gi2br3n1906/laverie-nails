<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ShippingRateService;
use Illuminate\View\View;

class InformationalPageController extends Controller
{
    public function about(): View
    {
        return view('about');
    }

    public function faq(): View
    {
        return view('faq');
    }

    public function shippingPolicy(ShippingRateService $shippingRates): View
    {
        return view('shipping-policy', [
            'shippingRates' => $shippingRates->rates(),
        ]);
    }
}
