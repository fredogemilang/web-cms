<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gzip-compresses textual responses when:
 *   - settings.pageopt_gzip_enabled is on
 *   - the client sent Accept-Encoding: gzip
 *   - the response is text-ish and >= 1KB
 *   - the response isn't already encoded
 *
 * Most production hosting (NGINX/Apache) does this at the web-server layer.
 * Enable this only when your host doesn't.
 */
class CompressResponse
{
    protected const MIN_BYTES = 1024;

    protected const COMPRESSIBLE_TYPES = [
        'text/html',
        'text/plain',
        'text/css',
        'text/xml',
        'application/json',
        'application/javascript',
        'application/xml',
        'image/svg+xml',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldCompress($request, $response)) {
            return $response;
        }

        $body = (string) $response->getContent();
        if (strlen($body) < self::MIN_BYTES) {
            return $response;
        }

        $compressed = gzencode($body, 6);
        if ($compressed === false) {
            return $response;
        }

        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Length', (string) strlen($compressed));
        $response->headers->set('Vary', $this->mergeVary($response->headers->get('Vary'), 'Accept-Encoding'));

        return $response;
    }

    protected function shouldCompress(Request $request, Response $response): bool
    {
        if (! setting('pageopt_gzip_enabled', false)) {
            return false;
        }
        if (! str_contains((string) $request->header('Accept-Encoding', ''), 'gzip')) {
            return false;
        }
        if ($response->headers->has('Content-Encoding')) {
            return false;
        }

        $type = (string) $response->headers->get('Content-Type', '');
        foreach (self::COMPRESSIBLE_TYPES as $compType) {
            if (str_contains($type, $compType)) {
                return true;
            }
        }

        return false;
    }

    protected function mergeVary(?string $existing, string $append): string
    {
        if (! $existing) {
            return $append;
        }
        $parts = array_map('trim', explode(',', $existing));
        if (! in_array(strtolower($append), array_map('strtolower', $parts), true)) {
            $parts[] = $append;
        }

        return implode(', ', $parts);
    }
}
