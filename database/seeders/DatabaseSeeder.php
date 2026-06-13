<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // firstOrCreate everywhere so re-seeding is additive — nothing gets wiped
        $users = [
            ['Branch Manager', 'bm@example.com', 'branch_manager'],
            ['Track Admin', 'trackadmin@example.com', 'track_admin'],
            ['Instructor', 'instructor@example.com', 'instructor'],
            ['Student', 'student@example.com', 'student'],
        ];

        $branchPhone = [
            'bm@example.com' => ['branch' => 'Cairo Branch', 'phone' => '+20 100 123 4567'],
            'trackadmin@example.com' => ['branch' => 'Cairo Branch', 'phone' => '+20 100 234 5678'],
            'instructor@example.com' => ['branch' => 'Cairo Branch', 'phone' => '+20 100 345 6789'],
            'student@example.com' => ['branch' => 'Cairo Branch', 'phone' => '+20 100 456 7890'],
        ];

        foreach ($users as [$name, $email, $role]) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role' => $role,
                    'password' => 'password',
                    ...$branchPhone[$email] ?? [],
                ],
            );
        }

        $this->call([
            TrackSeeder::class,
            CohortSeeder::class,
            CourseSeeder::class,
            LabGroupSeeder::class,
            EnrollmentSeeder::class,
            EngagementSeeder::class,
        ]);
        $this->call(TagSeeder::class);
        $this->call(StudentNoteSeeder::class);

        $this->call(AnnouncementSeeder::class);
        $this->call(StudentTagSeeder::class);
        $this->call(AttendanceSeeder::class);
        $this->call(AssignmentSeeder::class);

        // Grading domain (must run after course components exist).
        $this->call(ComponentGradeSeeder::class);
        $this->call(GradeOverrideSeeder::class);
    }
}
