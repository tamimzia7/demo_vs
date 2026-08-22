<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visitor;

/**
 * Visitor Workspace authorization (MOD-001, BDR-020).
 *
 * V1 active roles are Super Admin and Company Owner / Marketer; Team Edition
 * roles (Manager, Sales Executive) are not activated. Visitors are never owned
 * by a user (BDR-003) — authorization is role + tenant scoped, not ownership
 * scoped. Tenant isolation is enforced separately by the TenantScoped scope.
 */
class VisitorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Visitor $visitor): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Visitor $visitor): bool
    {
        return $user->isAdmin();
    }

    public function archive(User $user, Visitor $visitor): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Visitor $visitor): bool
    {
        return $user->isAdmin();
    }
}
