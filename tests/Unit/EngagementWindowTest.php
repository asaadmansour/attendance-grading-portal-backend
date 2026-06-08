<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\Engagement\EngagementWindow;
use App\Support\Engagement\NullEngagementWindow;
use Tests\TestCase;

class EngagementWindowTest extends TestCase
{
    public function test_default_binding_resolves_null_window_and_denies_instructors(): void
    {
        $window = app(EngagementWindow::class);

        $this->assertInstanceOf(NullEngagementWindow::class, $window);
        $this->assertFalse($window->instructorHasActiveWindow(new User(['role' => 'instructor'])));
    }
}
