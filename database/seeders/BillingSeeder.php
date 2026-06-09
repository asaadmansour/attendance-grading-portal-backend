<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\Engagement;
use App\Models\Session;
use App\Models\User;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        $cohort = Cohort::first();
        $instructor = User::where('role', 'instructor')->first();
        $admin = User::where('role', 'track_admin')->first();

        if (! $cohort || ! $instructor) {
            return;
        }

        // give them pay so the billing engine has rates to apply
        $instructor->update(['compensation_type' => 'external', 'hourly_rate' => 200]);
        $admin?->update(['compensation_type' => 'internal', 'hourly_rate' => 150, 'monthly_salary' => 8000]);

        // an engagement tied to the cohort, with a couple of delivered sessions to bill
        foreach (array_filter([$instructor, $admin]) as $teacher) {
            $engagement = Engagement::firstOrCreate(
                ['instructor_id' => $teacher->id, 'cohort_id' => $cohort->id, 'engagement_type' => 'lab'],
                ['start_date' => '2026-06-01', 'end_date' => '2026-09-01', 'scheduled_hours_per_session' => 3],
            );

            foreach ([['2026-06-02', 4], ['2026-06-09', 6]] as [$date, $hours]) {
                Session::firstOrCreate(
                    ['engagement_id' => $engagement->id, 'session_date' => $date],
                    ['scheduled_hours' => $hours, 'delivered_hours' => $hours, 'is_delivered' => true],
                );
            }
        }
    }
}
