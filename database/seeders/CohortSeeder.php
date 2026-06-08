<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Seeder;

class CohortSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::where('role', 'branch_manager')->first();
        $trackAdmin = User::where('role', 'track_admin')->first();

        // one active cohort per track; firstOrCreate keeps the one-active rule intact
        foreach (Track::all() as $track) {
            $cohort = Cohort::firstOrCreate(
                ['track_id' => $track->id, 'status' => 'active'],
                ['name' => $track->name.' — Intake 45', 'created_by' => $manager?->id],
            );

            $cohort->tas()->syncWithoutDetaching([$trackAdmin->id]);
        }
    }
}
