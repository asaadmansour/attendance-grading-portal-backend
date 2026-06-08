<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->state(['role' => UserRole::Student->value]),
            'cohort_id' => $this->faker->numberBetween(1, 10),
        ];
    }
}
