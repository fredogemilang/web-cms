<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Handle ?locale=xx switch — persist to cookie and clean URL
        if ($request->has('locale')) {
            $candidate = $request->query('locale');
            if (static::isAllowed($candidate)) {
                cookie()->queue('locale', $candidate, 60 * 24 * 365);
                app()->setLocale($candidate);
            }
        } else {
            // Resolve precedence: session > cookie > stored default > app config
            $locale = session('locale')
                ?? $request->cookie('locale')
                ?? setting('default_locale', config('app.locale', 'en'));

            if (static::isAllowed($locale)) {
                app()->setLocale($locale);
            }
        }

        // Fallback locale comes from settings if set
        $fallback = setting('fallback_locale', null);
        if ($fallback && static::isAllowed($fallback)) {
            app()->setFallbackLocale($fallback);
        }

        return $next($request);
    }

    protected static function isAllowed(?string $candidate): bool
    {
        if (! $candidate) {
            return false;
        }
        $available = array_filter(array_map('trim', explode(',', (string) setting('available_locales', 'id,en'))));

        return in_array($candidate, $available, true);
    }
}
