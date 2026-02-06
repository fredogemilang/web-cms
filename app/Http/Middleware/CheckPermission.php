<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        $user = auth()->user();

        // Debug logging
        \Log::info('CheckPermission Middleware', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'required_permissions' => $permissions,
            'is_super_admin' => $user->isSuperAdmin(),
            'user_roles' => $user->roles->pluck('name')->toArray(),
        ]);

        // Super admin bypass
        if ($user->isSuperAdmin()) {
            \Log::info('User is super admin - bypassing permission check');
            return $next($request);
        }

        // Check if user has any of the required permissions
        foreach ($permissions as $permission) {
            $hasPermission = $user->hasPermission($permission);
            \Log::info('Checking permission', [
                'permission' => $permission,
                'has_permission' => $hasPermission,
            ]);
            
            if ($hasPermission) {
                \Log::info('Permission granted', ['permission' => $permission]);
                return $next($request);
            }
        }

        \Log::warning('Permission denied - redirecting', [
            'required_permissions' => $permissions,
            'url' => $request->url(),
        ]);

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
