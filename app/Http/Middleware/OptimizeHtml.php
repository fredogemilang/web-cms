<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies Page Optimization + CDN URL rewriting to public HTML responses.
 * - HTML minify (whitespace + comments)
 * - Removes ?ver= query strings from asset URLs
 * - Rewrites configured local paths to a CDN base URL
 * - Adds loading="lazy" to <img> tags
 */
class OptimizeHtml
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldProcess($request, $response)) {
            return $response;
        }

        $html = $response->getContent();

        if (setting('cdn_enabled', false) && setting('cdn_base_url', '')) {
            $html = $this->rewriteCdn($html);
        }

        if (setting('pageopt_remove_query_strings', false)) {
            $html = $this->stripQueryStrings($html);
        }

        if (setting('img_lazy_load', true)) {
            $html = $this->lazyLoadImages($html);
        }

        if (setting('pageopt_defer_external_scripts', false)) {
            $html = $this->deferExternalScripts($html);
        }

        if ($critical = trim((string) setting('pageopt_critical_css', ''))) {
            $html = $this->inlineCriticalCss($html, $critical);
            $html = $this->deferStylesheets($html);
        }

        if (setting('pageopt_minify_html', false)) {
            $html = $this->minify($html);
        }

        $response->setContent($html);

        return $response;
    }

    protected function deferExternalScripts(string $html): string
    {
        $excludes = array_filter(array_map('trim', explode("\n", (string) setting('pageopt_defer_exclude', ''))));

        return preg_replace_callback(
            '/<script\b([^>]*\bsrc=("|\')([^"\']+)\2[^>]*)>/i',
            function ($m) use ($excludes) {
                $attrs = $m[1];
                $src = $m[3];

                // Skip if already has defer/async
                if (preg_match('/\b(defer|async)\b/i', $attrs)) {
                    return $m[0];
                }

                // Skip if matches an exclude pattern
                foreach ($excludes as $pattern) {
                    if ($pattern !== '' && str_contains($src, $pattern)) {
                        return $m[0];
                    }
                }

                // Skip module scripts (already deferred by default)
                if (preg_match('/type=["\']module["\']/i', $attrs)) {
                    return $m[0];
                }

                return '<script'.$attrs.' defer>';
            },
            $html,
        ) ?? $html;
    }

    protected function inlineCriticalCss(string $html, string $css): string
    {
        // Strip line comments from CSS to keep payload lean, but preserve /* ... */ for safety
        $tag = '<style data-critical>'.$css.'</style>';

        // Insert just before </head>; fall back to prepend if no </head>
        if (stripos($html, '</head>') !== false) {
            return preg_replace('/<\/head>/i', $tag.'</head>', $html, 1) ?? $html;
        }

        return $tag.$html;
    }

    protected function deferStylesheets(string $html): string
    {
        $patterns = array_filter(array_map('trim', explode("\n", (string) setting('pageopt_deferred_stylesheets', ''))));
        if (empty($patterns)) {
            return $html;
        }

        // Use the rel=preload + onload swap trick for non-blocking CSS
        return preg_replace_callback(
            '/<link\b([^>]*\brel=("|\')stylesheet\2[^>]*\bhref=("|\')([^"\']+)\3[^>]*)\/?>/i',
            function ($m) use ($patterns) {
                $href = $m[4];
                foreach ($patterns as $pat) {
                    if ($pat !== '' && str_contains($href, $pat)) {
                        $rest = preg_replace('/\brel=("|\')stylesheet\1/', 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"', $m[1]);

                        // Provide a <noscript> fallback for users without JS
                        return '<link'.$rest.'><noscript><link rel="stylesheet" href="'.htmlspecialchars($href, ENT_QUOTES).'"></noscript>';
                    }
                }

                return $m[0];
            },
            $html,
        ) ?? $html;
    }

    protected function shouldProcess(Request $request, Response $response): bool
    {
        if ($response->getStatusCode() !== 200) {
            return false;
        }
        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return false;
        }

        // Skip admin
        $adminPath = trim(config('admin.path', 'admin'), '/');
        if ($adminPath !== '' && str_starts_with(ltrim($request->path(), '/'), $adminPath)) {
            return false;
        }

        return true;
    }

    protected function rewriteCdn(string $html): string
    {
        $base = rtrim((string) setting('cdn_base_url', ''), '/');
        if ($base === '') {
            return $html;
        }

        $paths = array_filter(array_map('trim', explode("\n", (string) setting('cdn_paths_to_rewrite', ''))));
        $appUrl = rtrim((string) config('app.url'), '/');

        foreach ($paths as $path) {
            $needle = $appUrl.'/'.ltrim($path, '/');
            $html = str_replace($needle, $base.'/'.ltrim($path, '/'), $html);
            // Also rewrite root-relative
            $html = preg_replace(
                '#(["\'(])(/'.preg_quote(ltrim($path, '/'), '#').')#',
                '$1'.$base.'$2',
                $html,
            ) ?? $html;
        }

        return $html;
    }

    protected function stripQueryStrings(string $html): string
    {
        return preg_replace_callback(
            '/(href|src)=(["\'])([^"\']+\.(?:js|css))\?[^"\']*\2/i',
            fn ($m) => "{$m[1]}={$m[2]}{$m[3]}{$m[2]}",
            $html,
        ) ?? $html;
    }

    protected function lazyLoadImages(string $html): string
    {
        return preg_replace_callback(
            '/<img\b(?![^>]*\bloading=)([^>]*)>/i',
            fn ($m) => '<img'.$m[1].' loading="lazy">',
            $html,
        ) ?? $html;
    }

    protected function minify(string $html): string
    {
        // Strip HTML comments (preserve IE conditionals)
        $html = preg_replace('/<!--(?!\[if).*?-->/s', '', $html) ?? $html;
        // Collapse whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html) ?? $html;
        // Collapse runs of whitespace inside text nodes
        $html = preg_replace('/\s{2,}/', ' ', $html) ?? $html;

        return trim($html);
    }
}
