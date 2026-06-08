<?php

namespace Tests\Unit;

use App\Models\User;
use App\Policies\AnnouncementPolicy;
use App\Support\Engagement\EngagementWindow;
use Tests\TestCase;

class AnnouncementPolicyTest extends TestCase
{
    public function test_branch_manager_can_create(): void
    {
        $this->assertTrue(app(AnnouncementPolicy::class)->create(new User(['role' => 'branch_manager'])));
    }

    public function test_track_admin_can_create(): void
    {
        $this->assertTrue(app(AnnouncementPolicy::class)->create(new User(['role' => 'track_admin'])));
    }

    public function test_student_cannot_create(): void
    {
        $this->assertFalse(app(AnnouncementPolicy::class)->create(new User(['role' => 'student'])));
    }

    public function test_instructor_denied_when_window_closed(): void
    {
        // default NullEngagementWindow returns false
        $this->assertFalse(app(AnnouncementPolicy::class)->create(new User(['role' => 'instructor'])));
    }

    public function test_instructor_allowed_when_window_open(): void
    {
        $this->app->bind(EngagementWindow::class, fn () => new class implements EngagementWindow {
            public function instructorHasActiveWindow(User $instructor): bool
            {
                return true;
            }
        });

        $this->assertTrue(app(AnnouncementPolicy::class)->create(new User(['role' => 'instructor'])));
    }
}
