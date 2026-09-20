<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;
use App\Security\CrmPermission;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(CrmPermission::BRANCHES_VIEW);
    }

    public function view(User $user, Branch $branch): bool
    {
        if (! $user->hasPermission(CrmPermission::BRANCHES_VIEW)) {
            return false;
        }

        if ($user->hasPermission(CrmPermission::BRANCHES_SCOPE_ALL)) {
            return true;
        }

        return $user->branch_id !== null && (int) $user->branch_id === (int) $branch->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(CrmPermission::BRANCHES_MANAGE);
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->hasPermission(CrmPermission::BRANCHES_MANAGE);
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $user->hasPermission(CrmPermission::BRANCHES_MANAGE);
    }
}
