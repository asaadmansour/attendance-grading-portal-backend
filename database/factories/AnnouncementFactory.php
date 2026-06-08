<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'author_id' => User::factory()->state(['role' => UserRole::TrackAdmin->value]),
            'title' => fake()->sentence(),
            'body' => fake()->paragraph(),
        ];
    }
}
