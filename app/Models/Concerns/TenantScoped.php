<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Enforces tenant isolation at the persistence boundary (BDR-021).
 *
 * A Super Admin has global visibility across tenants. Every other authenticated
 * user only sees records belonging to their own tenant. When no user is
 * authenticated (e.g. seeders, console commands, guest fallbacks) the scope is
 * not applied so that non-request contexts behave normally; web requests are
 * already gated by the `auth` middleware before any scoped query runs.
 */
trait TenantScoped
{
    protected static function bootTenantScoped(): void
    {
        static::addGlobalScope('tenant', function (Builder $query): void {
            if (! Auth::check()) {
                return;
            }

            $user = Auth::user();

            if ($user->isSuperAdmin()) {
                return;
            }

            $query->where('tenant_id', $user->tenant_id);
        });
    }
}
