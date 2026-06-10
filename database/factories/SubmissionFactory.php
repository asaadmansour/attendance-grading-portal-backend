<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'student_id' => User::factory()->state(['role' => UserRole::Student->value]),
            'url' => fake()->url(),
            'file_path' => null,
            'submitted_at' => now(),
        ];
    }
}
