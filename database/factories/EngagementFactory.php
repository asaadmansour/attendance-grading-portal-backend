<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EngagementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cohort_id' => $this->faker->numberBetween(1, 10),
            'instructor_id' => \App\Models\User::factory(),
        ];
    }
}
