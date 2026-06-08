<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $caller): bool
    {
        return in_array(UserRole::from($caller->role), [
            UserRole::BranchManager,
            UserRole::TrackAdmin,
        ]);
    }

    public function view(User $caller, User $target): bool
    {
        if ($caller->id === $target->id) {
            return true;
        }

        $callerRole = UserRole::from($caller->role);
        $targetRole = UserRole::from($target->role);

        return in_array($targetRole, $callerRole->canCreate());
    }

    public function create(User $caller): bool
    {
        return !empty(UserRole::from($caller->role)->canCreate());
    }

    public function update(User $caller, User $target): bool
    {
        if ($caller->id === $target->id) {
            return true;
        }

        $callerRole = UserRole::from($caller->role);
        $targetRole = UserRole::from($target->role);

        return in_array($targetRole, $callerRole->canCreate());
    }

    public function delete(User $caller, User $target): bool
    {
        $callerRole = UserRole::from($caller->role);
        $targetRole = UserRole::from($target->role);

        return in_array($targetRole, $callerRole->canCreate());
    }
}
