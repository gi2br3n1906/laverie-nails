<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDefaultSizeRequest;
use App\Models\Order;
use App\Models\User;
use App\Services\CustomerPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    public function __construct(private readonly CustomerPortalService $service) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('dashboard', ['user' => $user, 'orders' => $this->service->orders($user)]);
    }

    public function show(Request $request, Order $order): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('customer.orders.show', ['order' => $this->service->order($user, $order)]);
    }

    public function updateMeasurements(UpdateDefaultSizeRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->service->updateMeasurements($user, $request->validated());

        return to_route('dashboard')->with('status', 'Profil ukuran berhasil disimpan.');
    }
}
