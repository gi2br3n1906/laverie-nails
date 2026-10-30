<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, (array) config('laverie.locales', [])), 404);

        $request->session()->put('locale', $locale);
        app()->setLocale($locale);

        return redirect()
            ->back(302, [], route('home'))
            ->withCookie(cookie()->forever('locale', $locale));
    }
}
