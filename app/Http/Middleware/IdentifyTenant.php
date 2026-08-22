<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active tenant for the current request (BDR-021).
 *
 * In the V1 Solo Edition each user belongs to exactly one tenant; the tenant
 * is therefore derived from the authenticated user. The resolved tenant is
 * bound into the service container and exposed on the request so that services
 * and tenant-scoped queries can reference it without re-deriving it.
 */
class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $tenant = Auth::user()->tenant;

            if ($tenant instanceof Tenant) {
                $request->attributes->set('tenant', $tenant);
                app()->instance(Tenant::class, $tenant);
            }
        }

        return $next($request);
    }
}
