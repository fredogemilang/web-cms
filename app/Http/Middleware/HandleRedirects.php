<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only intercept safe methods — POST/PUT/etc shouldn't be redirected
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        // Skip admin path entirely so admin UX isn't accidentally redirected
        $adminPath = trim(config('admin.path', 'admin'), '/');
        if ($adminPath !== '' && str_starts_with(ltrim($request->path(), '/'), $adminPath)) {
            return $next($request);
        }

        $path = '/'.ltrim($request->path(), '/');

        $rule = Redirect::matchRequestPath($path);
        if (! $rule) {
            return $next($request);
        }

        $target = $rule->resolveTarget($path);

        // Preserve query string unless target already has one
        if ($request->getQueryString() && ! str_contains($target, '?')) {
            $target .= '?'.$request->getQueryString();
        }

        // Update counter in a non-blocking way — failure should never break the redirect
        try {
            $rule->recordHit();
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->away($target, $rule->status_code);
    }
}
