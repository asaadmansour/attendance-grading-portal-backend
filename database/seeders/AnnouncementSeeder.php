<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'trackadmin@example.com')->first();

        if (! $author) {
            return;
        }

        Announcement::factory()->count(3)->create(['author_id' => $author->id]);
    }
}
