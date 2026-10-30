<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegisterUserService;
use App\Services\CartService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(
        private readonly RegisterUserService $registerUserService,
        private readonly CartService $cartService,
    ) {}

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $guestSessionId = $request->session()->get('cart.guest_id');
        $validated = $request->validated();

        $user = $this->registerUserService->register($validated);

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();
        $this->cartService->claimGuestCart($user->id, is_string($guestSessionId) ? $guestSessionId : null);
        $request->session()->forget('cart.guest_id');

        return redirect()->intended(route('dashboard'));
    }
}
