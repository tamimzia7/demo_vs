<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to users holding one of the supplied roles (BDR-020).
 *
 * V1 active roles are Super Admin and Company Owner / Marketer. Future roles
 * (Sales Executive, Manager, Marketing Officer) are intentionally NOT accepted
 * here until the Team Edition activates them.
 */
class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return Redirect::route('login');
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
