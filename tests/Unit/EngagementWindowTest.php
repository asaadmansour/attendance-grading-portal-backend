<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\Engagement\EngagementWindow;
use App\Support\Engagement\NullEngagementWindow;
use Tests\TestCase;

class EngagementWindowTest extends TestCase
{
    public function test_default_binding_resolves_null_window(): void
    {
        $this->assertInstanceOf(NullEngagementWindow::class, app(EngagementWindow::class));
    }

    public function test_null_window_denies_instructors(): void
    {
        $window = app(EngagementWindow::class);

        $this->assertFalse($window->instructorHasActiveWindow(new User(['role' => UserRole::Instructor->value])));
    }
}
