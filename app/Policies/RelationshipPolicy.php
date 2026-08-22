<?php

namespace App\Policies;

use App\Models\Relationship;
use App\Models\User;

/**
 * Relationship Center authorization (MOD-003, BDR-019, BDR-020).
 *
 * V1 active roles are Super Admin and Company Owner / Marketer (BDR-020).
 * - Assign / request transfer: V1 in-scope admin (Company Owner; Super Admin is
 *   platform). Team Edition roles (Manager, Sales Executive) are not activated.
 * - Approve / reject transfer: Company Owner only in V1 (BDR-019); Super Admin is
 *   a platform role and does NOT authorize transfers per the permission matrix.
 * - View: admins, plus the previous owner (transferred_from) retains read-only
 *   access to preserved history (BDR-004).
 * Tenant isolation is enforced separately by the TenantScoped scope.
 */
class RelationshipPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Relationship $relationship): bool
    {
        return $user->isAdmin()
            || $user->id === $relationship->transferred_from_id;
    }

    public function assign(User $user): bool
    {
        return $user->isAdmin();
    }

    public function requestTransfer(User $user, Relationship $relationship): bool
    {
        return $user->isAdmin();
    }

    public function approve(User $user, Relationship $relationship): bool
    {
        return $user->isCompanyOwner();
    }

    public function reject(User $user, Relationship $relationship): bool
    {
        return $user->isCompanyOwner();
    }
}
