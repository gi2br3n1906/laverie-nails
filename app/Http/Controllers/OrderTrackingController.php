<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TrackOrderRequest;
use App\Services\OrderManagementService;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function __construct(private readonly OrderManagementService $orders) {}

    public function create(): View
    {
        return view('orders.tracking', ['order' => null, 'lookupFailed' => false]);
    }

    public function store(TrackOrderRequest $request): View
    {
        $data = $request->validated();
        $order = $this->orders->track($data['order_id'], $data['email']);

        return view('orders.tracking', [
            'order' => $order,
            'lookupFailed' => $order === null,
        ]);
    }
}
