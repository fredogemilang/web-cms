<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PageCache
{
    protected const TAG = 'page-cache';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldCache($request)) {
            return $next($request);
        }

        $key = $this->cacheKey($request);
        $ttl = (int) setting('page_cache_ttl', 3600);

        // Cache hit
        if ($cached = Cache::get($key)) {
            return response($cached['content'], $cached['status'], $cached['headers'] + ['X-Page-Cache' => 'HIT']);
        }

        /** @var Response $response */
        $response = $next($request);

        // Only cache HTML 200s
        if ($response->getStatusCode() === 200
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {

            Cache::put($key, [
                'content' => $response->getContent(),
                'status' => 200,
                'headers' => ['Content-Type' => $response->headers->get('Content-Type')],
            ], $ttl);
        }

        $response->headers->set('X-Page-Cache', 'MISS');

        return $response;
    }

    protected function shouldCache(Request $request): bool
    {
        if (! setting('page_cache_enabled', false)) {
            return false;
        }
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return false;
        }
        if (auth()->check()) {
            return false;
        }

        // Skip admin path
        $adminPath = trim(config('admin.path', 'admin'), '/');
        if ($adminPath !== '' && str_starts_with(ltrim($request->path(), '/'), $adminPath)) {
            return false;
        }

        // Skip excluded paths
        $excluded = array_filter(array_map('trim', explode("\n", (string) setting('page_cache_excluded_paths', ''))));
        $path = '/'.ltrim($request->path(), '/');
        foreach ($excluded as $pattern) {
            $regex = '#^'.str_replace(['\*', '\?'], ['.*', '.'], preg_quote($pattern, '#')).'$#';
            if (preg_match($regex, $path) === 1) {
                return false;
            }
        }

        return true;
    }

    protected function cacheKey(Request $request): string
    {
        $version = Cache::get('page-cache:version', 1);

        return "page:v{$version}:".md5($request->fullUrl());
    }

    public static function purgeAll(): void
    {
        // Without cache tags (memcached/redis) we bump a version prefix used in every key,
        // making all previously cached entries unreachable.
        $current = (int) Cache::get('page-cache:version', 1);
        Cache::put('page-cache:version', $current + 1, now()->addYears(5));
    }
}
