<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $guestSessionId = $request->session()->get('cart.guest_id');
        $request->authenticate();
        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();
        $this->cartService->claimGuestCart($user->id, is_string($guestSessionId) ? $guestSessionId : null);
        $request->session()->forget('cart.guest_id');

        return redirect()->intended(route($user->accountHomeRouteName()));
    }

    public function destroy(LogoutRequest $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
