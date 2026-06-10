<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        return [
            'course_id' => null,
            'title' => fake()->sentence(3),
            'due_at' => now()->addWeeks(2),
            'created_by' => User::factory()->state(['role' => UserRole::TrackAdmin->value]),
        ];
    }
}
