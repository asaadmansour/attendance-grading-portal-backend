<?php

namespace App\Support\Engagement;

use App\Models\User;

interface EngagementWindow
{
    public function instructorHasActiveWindow(User $instructor): bool;
}
