<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\Engagement;
use App\Models\User;
use Illuminate\Database\Seeder;

class EngagementSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = User::where('role', 'instructor')->first();
        $cohort = Cohort::first();

        // [type, start, end, hours] keyed on type so re-seeding won't duplicate
        $engagements = [
            ['lab', '2026-06-01', '2026-09-01', 3],
            ['lecture', '2026-07-01', '2026-10-15', 2],
        ];

        foreach ($engagements as [$type, $start, $end, $hours]) {
            Engagement::firstOrCreate(
                ['instructor_id' => $instructor->id, 'engagement_type' => $type],
                ['cohort_id' => $cohort?->id, 'start_date' => $start, 'end_date' => $end, 'scheduled_hours_per_session' => $hours],
            );
        }
    }
}
