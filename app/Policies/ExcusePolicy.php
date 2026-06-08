<?php

namespace App\Policies;

use App\Models\ExcuseRequest;
use App\Models\User;

class ExcusePolicy
{
    /**
     * Create a new policy instance.
     */
    public function create(User $user): bool
    {
        return $user->role === 'student';
    }

    public function view(User $user, ExcuseRequest $excuse): bool
    {
        if (in_array($user->role, ['track_admin', 'branch_manager'])) {
            return true;
        }

        return $user->id === $excuse->student_id;
    }

    public function review(User $user, ExcuseRequest $excuse): bool
    {
        return $user->role === 'track_admin';
    }
}