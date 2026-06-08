<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => \App\Models\User::factory(),
            'cohort_id' => $this->faker->numberBetween(1, 10),
        ];
    }
}
