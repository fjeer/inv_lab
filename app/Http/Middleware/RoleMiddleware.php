<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage: Route::middleware('role:admin,asisten')
     *
     * Supports both dynamic roles (via role_id → roles.name)
     * and legacy enum roles (admin_lab, asisten_lab, pengguna).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Check dynamic roles first
        if ($request->user()->role_id && $request->user()->roleRelation) {
            $dynamicRoleName = $request->user()->roleRelation->name;
            if (in_array($dynamicRoleName, $roles, true)) {
                return $next($request);
            }
        }

        // Fallback to legacy enum role
        if ($request->user()->hasRole(...$roles)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
