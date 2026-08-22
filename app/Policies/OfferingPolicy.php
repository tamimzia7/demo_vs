<?php

namespace App\Policies;

use App\Models\Offering;
use App\Models\User;

class OfferingPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'company_owner', 'marketer', 'sales_executive']);
    }

    public function view(User $user, Offering $offering): bool
    {
        return $user->tenant_id === $offering->tenant_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'company_owner', 'marketer']);
    }

    public function update(User $user, Offering $offering): bool
    {
        return in_array($user->role, ['super_admin', 'company_owner', 'marketer'])
            && $user->tenant_id === $offering->tenant_id;
    }

    public function delete(User $user, Offering $offering): bool
    {
        return in_array($user->role, ['super_admin', 'company_owner'])
            && $user->tenant_id === $offering->tenant_id;
    }
}
