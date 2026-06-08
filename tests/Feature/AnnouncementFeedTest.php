<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnnouncementFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_lists_paginated_feed(): void
    {
        Announcement::factory()->count(20)->create();
        Sanctum::actingAs(User::factory()->create(['role' => 'student']));

        $this->getJson('/api/v1/announcements?per_page=5')
            ->assertStatus(200)
            ->assertJsonCount(5, 'data.items')
            ->assertJsonPath('data.meta.per_page', 5)
            ->assertJsonPath('data.meta.total', 20)
            ->assertJsonPath('data.meta.last_page', 4);
    }

    public function test_per_page_is_capped_at_100(): void
    {
        Announcement::factory()->count(3)->create();
        Sanctum::actingAs(User::factory()->create(['role' => 'student']));

        $this->getJson('/api/v1/announcements?per_page=500')
            ->assertStatus(200)
            ->assertJsonPath('data.meta.per_page', 100);
    }

    public function test_guest_cannot_list_feed(): void
    {
        $this->getJson('/api/v1/announcements')->assertStatus(401);
    }

    public function test_can_view_single_announcement(): void
    {
        $announcement = Announcement::factory()->create(['title' => 'Hello']);
        Sanctum::actingAs(User::factory()->create(['role' => 'student']));

        $this->getJson("/api/v1/announcements/{$announcement->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Hello');
    }
}
