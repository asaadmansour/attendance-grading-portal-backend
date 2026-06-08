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
            ->assertJsonPath('data.meta.per_page', 100)
            ->assertJsonCount(3, 'data.items');
    }

    public function test_per_page_is_floored_at_1(): void
    {
        Announcement::factory()->count(3)->create();
        Sanctum::actingAs(User::factory()->create(['role' => 'student']));

        $this->getJson('/api/v1/announcements?per_page=0')
            ->assertStatus(200)
            ->assertJsonPath('data.meta.per_page', 1)
            ->assertJsonCount(1, 'data.items');
    }

    public function test_feed_orders_newest_first_by_default(): void
    {
        $this->seedTwoDatedAnnouncements();
        Sanctum::actingAs(User::factory()->create(['role' => 'student']));

        $this->getJson('/api/v1/announcements')
            ->assertStatus(200)
            ->assertJsonPath('data.items.0.title', 'newer');
    }

    public function test_feed_sort_oldest_ascends(): void
    {
        $this->seedTwoDatedAnnouncements();
        Sanctum::actingAs(User::factory()->create(['role' => 'student']));

        $this->getJson('/api/v1/announcements?sort=oldest')
            ->assertStatus(200)
            ->assertJsonPath('data.items.0.title', 'older');
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

    private function seedTwoDatedAnnouncements(): void
    {
        Announcement::factory()->create(['title' => 'older'])
            ->forceFill(['created_at' => now()->subDays(2)])->save();
        Announcement::factory()->create(['title' => 'newer'])
            ->forceFill(['created_at' => now()])->save();
    }
}
