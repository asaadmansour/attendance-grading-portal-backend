<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EngagementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'instructor_id' => User::factory()->state(['role' => UserRole::Instructor->value]),
            'cohort_id' => $this->faker->numberBetween(1, 10),
            'engagement_type' => $this->faker->randomElement(['lecture', 'lab', 'business']),
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'scheduled_hours_per_session' => $this->faker->randomFloat(2, 1, 8),
        ];
    }
}
