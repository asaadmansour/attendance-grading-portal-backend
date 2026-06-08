<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Engagement\EngagementWindow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnnouncementStoreTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Exam moved',
            'body' => 'Final exam moved to Monday.',
        ], $overrides);
    }

    public function test_track_admin_can_create(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'track_admin']));

        $this->postJson('/api/v1/announcements', $this->payload())
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'Exam moved');

        $this->assertDatabaseHas('announcements', ['title' => 'Exam moved']);
    }

    public function test_branch_manager_can_create(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'branch_manager']));

        $this->postJson('/api/v1/announcements', $this->payload())->assertStatus(201);
    }

    public function test_instructor_denied_when_window_closed(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'instructor']));

        $this->postJson('/api/v1/announcements', $this->payload())->assertStatus(403);
    }

    public function test_instructor_allowed_when_window_open(): void
    {
        $this->app->bind(EngagementWindow::class, fn () => new class implements EngagementWindow {
            public function instructorHasActiveWindow(User $instructor): bool
            {
                return true;
            }
        });
        Sanctum::actingAs(User::factory()->create(['role' => 'instructor']));

        $this->postJson('/api/v1/announcements', $this->payload())->assertStatus(201);
    }

    public function test_student_cannot_create(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'student']));

        $this->postJson('/api/v1/announcements', $this->payload())->assertStatus(403);
    }

    public function test_guest_cannot_create(): void
    {
        $this->postJson('/api/v1/announcements', $this->payload())->assertStatus(401);
    }

    public function test_validation_requires_title_and_body(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'track_admin']));

        $this->postJson('/api/v1/announcements', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'body']);
    }
}
