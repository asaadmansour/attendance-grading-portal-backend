<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $ta = User::where('email', 'trackadmin@example.com')->first();
        $student = User::where('email', 'student@example.com')->first();

        if (! $ta || ! $student) {
            return;
        }

        $assignment = Assignment::firstOrCreate(
            ['title' => 'Laravel mini-project'],
            [
                'course_id' => Course::value('id'),
                'due_at' => now()->addWeeks(2),
                'created_by' => $ta->id,
            ],
        );

        Submission::firstOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $student->id],
            [
                'url' => 'https://github.com/example/mini-project',
                'submitted_at' => now(),
            ],
        );
    }
}
