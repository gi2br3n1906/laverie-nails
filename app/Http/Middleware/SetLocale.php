<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys((array) config('laverie.locales', []));
        $locale = $request->session()->get('locale')
            ?? $request->cookie('locale')
            ?? config('app.locale');

        if (! is_string($locale) || ! in_array($locale, $supported, true)) {
            $locale = (string) config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
