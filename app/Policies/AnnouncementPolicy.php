<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\Engagement\EngagementWindow;

class AnnouncementPolicy
{
    public function __construct(private EngagementWindow $window) {}

    public function create(User $user): bool
    {
        return match (UserRole::from($user->role)) {
            UserRole::BranchManager, UserRole::TrackAdmin => true,
            UserRole::Instructor => $this->window->instructorHasActiveWindow($user),
            default => false,
        };
    }
}
