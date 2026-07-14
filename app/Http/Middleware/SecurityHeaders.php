<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Defensive HTTP response headers applied to every web response.
 *
 * The defaults are intentionally permissive enough to not break existing inline
 * Alpine.js / Livewire usage (no strict CSP). Tighten via the `security.csp`
 * setting once you've audited inline scripts.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Don't decorate downloads / streamed responses
        if ($response instanceof BinaryFileResponse
            || $response instanceof StreamedResponse) {
            return $response;
        }

        $headers = $response->headers;

        // Always-on defensive headers
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('X-XSS-Protection', '0'); // CSP is the modern replacement

        // Feature/permissions policy — disable surfaces we don't use
        $headers->set('Permissions-Policy', implode(', ', [
            'camera=()',
            'microphone=()',
            'geolocation=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()',
        ]));

        // HSTS — only when we're actually on HTTPS and not local
        if ($request->secure() && app()->environment('production', 'staging')) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Conservative CSP — allow self + inline (Alpine/Livewire need 'unsafe-inline'),
        // plus the fonts.googleapis CDN that the admin layout uses, plus storage img.
        // Disabled by default; opt-in via env to avoid breaking existing themes.
        if (config('security.csp_enabled', false)) {
            $headers->set('Content-Security-Policy', $this->buildCsp());
        }

        return $response;
    }

    protected function buildCsp(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: blob: https:",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
    }
}
