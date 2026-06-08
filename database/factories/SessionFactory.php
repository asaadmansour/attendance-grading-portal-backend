<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'engagement_id' => \App\Models\Engagement::factory(),
            'session_date' => now()->toDateString(),
            'scheduled_hours' => $this->faker->randomFloat(2, 1, 8),
            'delivered_hours' => 0,
            'is_delivered' => false,
        ];
    }
}