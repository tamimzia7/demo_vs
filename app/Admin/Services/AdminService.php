<?php

namespace App\Admin\Services;

use App\Models\Role;
use App\Models\SystemTag;
use App\Models\User;

class AdminService
{
    public function getUsers()
    {
        return User::with('tenant')->get();
    }

    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function updateUser(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }

    public function getRoles()
    {
        return Role::all();
    }

    public function createRole(array $data): Role
    {
        return Role::create($data);
    }

    public function getSystemTags()
    {
        return SystemTag::all();
    }

    public function createSystemTag(array $data): SystemTag
    {
        return SystemTag::create($data);
    }

    public function deleteSystemTag(SystemTag $tag): bool
    {
        return false;
    }
}
