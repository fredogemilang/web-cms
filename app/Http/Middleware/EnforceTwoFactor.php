<?php

namespace App\Http\Middleware;

use App\Services\TwoFactorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceTwoFactor
{
    public function __construct(protected TwoFactorService $tfa) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }
        if ($this->tfa->isEnabled($user)) {
            return $next($request);
        }
        if (! $this->tfa->isEnforcedFor($user)) {
            return $next($request);
        }

        // Allow access to profile page (where 2FA setup happens) + logout.
        $allowed = ['admin.profile.index', 'logout', 'admin.profile.two-factor.enable',
            'admin.profile.two-factor.confirm', 'admin.profile.two-factor.disable'];
        if (in_array($request->route()?->getName(), $allowed, true)) {
            return $next($request);
        }

        return redirect()->route('admin.profile.index')
            ->with('warning', 'Anda diwajibkan mengaktifkan Two-Factor Authentication sebelum melanjutkan.');
    }
}
