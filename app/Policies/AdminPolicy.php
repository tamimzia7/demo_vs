<?php

namespace App\Policies;

use App\Models\User;

/**
 * Foundation access-control policy for platform administration (MOD-012, BDR-020).
 *
 * V1 access model: Super Admin (platform) and Company Owner / Marketer (company)
 * may administer users, roles and System Tags. The detailed role/permission
 * matrix is an Open Question and must not be invented here.
 */
class AdminPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user): bool
    {
        return $user->isAdmin();
    }

    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }
}
