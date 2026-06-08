<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'payload' => json_encode([]),
            'last_activity' => now()->timestamp,
        ];
    }
}