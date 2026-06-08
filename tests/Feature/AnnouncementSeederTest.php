<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_announcements_for_the_track_admin(): void
    {
        $this->seed(DatabaseSeeder::class);

        $author = User::where('email', 'trackadmin@example.com')->first();

        $this->assertNotNull($author);
        $this->assertSame(3, Announcement::where('author_id', $author->id)->count());
    }
}
