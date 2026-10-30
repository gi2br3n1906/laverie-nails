<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Exceptions\GoogleAuthException;
use App\Http\Controllers\Controller;
use App\Services\Auth\GoogleAuthService;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleAuthController extends Controller
{
    public function __construct(
        private readonly GoogleAuthService $googleAuthService,
        private readonly CartService $cartService,
    ) {}

    public function redirect(): SymfonyRedirectResponse
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => __('ui.auth.google_not_configured')]);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $guestSessionId = $request->session()->get('cart.guest_id');

        try {
            $googleUser = Socialite::driver('google')->user();
            $user = $this->googleAuthService->findOrCreateUser($googleUser);
        } catch (GoogleAuthException $exception) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            Log::warning('Google sign-in failed.', [
                'exception' => $exception,
            ]);

            return redirect()
                ->route('login')
                ->withErrors(['email' => __('ui.auth.google_failed')]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        $this->cartService->claimGuestCart(
            $user->id,
            is_string($guestSessionId) ? $guestSessionId : null,
        );
        $request->session()->forget('cart.guest_id');

        return redirect()->intended(route($user->accountHomeRouteName()));
    }
}
