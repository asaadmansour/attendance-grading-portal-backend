<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_announcement_belongs_to_its_author(): void
    {
        $author = User::factory()->create(['role' => UserRole::TrackAdmin->value]);
        $announcement = Announcement::factory()->create(['author_id' => $author->id]);

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'author_id' => $author->id,
        ]);
        $this->assertTrue($announcement->author->is($author));
        $this->assertTrue($author->announcements->contains($announcement));
    }
}
