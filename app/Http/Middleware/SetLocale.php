<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = array_keys(config('app.available_locales'));
        $locale = $request->session()->get('locale')
            ?? $request->user()?->locale
            ?? config('app.locale');

        $locale = in_array($locale, $availableLocales, true) ? $locale : config('app.locale');

        App::setLocale($locale);
        Date::setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }
}
