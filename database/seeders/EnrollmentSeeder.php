<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        // a few students so the rosters and groups aren't sitting empty
        $students = collect(range(1, 4))->map(fn ($n) => User::firstOrCreate(
            ['email' => "student{$n}@example.com"],
            ['name' => "Student {$n}", 'role' => 'student', 'password' => 'password'],
        ));

        // drop each student into every cohort and its lab group
        foreach (Cohort::with('labGroups')->get() as $cohort) {
            $group = $cohort->labGroups->first();

            foreach ($students as $student) {
                Enrollment::firstOrCreate(
                    ['student_id' => $student->id, 'cohort_id' => $cohort->id],
                    ['lab_group_id' => $group?->id],
                );
            }
        }
    }
}
