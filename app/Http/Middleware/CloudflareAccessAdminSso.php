<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\CloudflareAccessTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CloudflareAccessAdminSso
{
    public function __construct(private readonly CloudflareAccessTokenService $tokens) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $assertion = $request->header('Cf-Access-Jwt-Assertion') ?: $request->cookie('CF_Authorization');

        if (! is_string($assertion) || $assertion === '') {
            if (Auth::guard('web')->check()) {
                abort_unless($request->user()?->hasRole(UserRole::Admin), 403);

                return $next($request);
            }

            abort_if($this->tokens->isConfigured(), 403, 'Cloudflare Access assertion is missing.');

            return redirect()->guest(route('login'));
        }

        $email = $this->tokens->email($assertion);

        abort_if($email === null, 403, 'Cloudflare Access authentication failed.');

        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        abort_if($user === null || ! $user->hasRole(UserRole::Admin), 403);

        if (Auth::guard('web')->check()) {
            abort_unless($request->user()?->is($user), 403);

            return $next($request);
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return $next($request);
    }
}
