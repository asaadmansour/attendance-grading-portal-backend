<?php

namespace App\Policies;

use App\Models\Session;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Create a new policy instance.
     */
    public function checkIn(User $user, Session $session): bool
    {
        if (! in_array($user->role, ['instructor', 'track_admin'])) {
            return false;
        }

        if ($user->role === 'instructor') {
            return true;
        }

        return true;
    }

    public function checkOut(User $user, Session $session): bool
    {
        return $this->checkIn($user, $session);
    }
}