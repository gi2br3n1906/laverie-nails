<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\CloudflareAccessTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CloudflareAccessAdminSso
{
    public function __construct(private readonly CloudflareAccessTokenService $tokens) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessHeader = $request->header('Cf-Access-Jwt-Assertion');

        // A stale Access cookie must not override a valid Laravel password session.
        if (! filled($accessHeader) && Auth::guard('web')->check()) {
            if (! $request->user()?->hasRole(UserRole::Admin)) {
                $this->logRejection('authenticated_user_not_admin', $request, null);
                abort(403);
            }

            return $next($request);
        }

        $assertion = $accessHeader ?: $request->cookie('CF_Authorization');

        if (! is_string($assertion) || $assertion === '') {
            if ($this->tokens->isConfigured()) {
                $this->logRejection('assertion_missing', $request, $assertion);
                abort(403, 'Cloudflare Access assertion is missing.');
            }

            return redirect()->guest(route('login'));
        }

        $email = $this->tokens->email($assertion);

        if ($email === null) {
            $this->logRejection('assertion_invalid', $request, $assertion);
            abort(403, 'Cloudflare Access authentication failed.');
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user === null || ! $user->hasRole(UserRole::Admin)) {
            $this->logRejection('access_identity_not_admin', $request, $assertion);
            abort(403);
        }

        if (Auth::guard('web')->check()) {
            if (! $request->user()?->is($user)) {
                $this->logRejection('access_identity_session_mismatch', $request, $assertion);
                abort(403);
            }

            return $next($request);
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return $next($request);
    }

    private function logRejection(string $reason, Request $request, mixed $assertion): void
    {
        Log::warning('Cloudflare Access admin request rejected.', [
            'reason' => $reason,
            'path' => $request->path(),
            'has_access_header' => filled($request->header('Cf-Access-Jwt-Assertion')),
            'has_access_cookie' => filled($request->cookie('CF_Authorization')),
            'assertion_source' => filled($request->header('Cf-Access-Jwt-Assertion')) ? 'header' : (filled($assertion) ? 'cookie' : 'none'),
        ]);
    }
}
