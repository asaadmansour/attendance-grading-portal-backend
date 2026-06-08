<?php

namespace App\Support\Engagement;

use App\Models\User;

// engagements table (Sameh, ENG-3) not in this repo yet — deny until it lands.
class NullEngagementWindow implements EngagementWindow
{
    public function instructorHasActiveWindow(User $instructor): bool
    {
        return false;
    }
}
