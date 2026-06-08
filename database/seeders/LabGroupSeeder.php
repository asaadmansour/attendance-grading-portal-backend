<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\User;
use Illuminate\Database\Seeder;

class LabGroupSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = User::where('role', 'instructor')->first();

        // give each cohort a single lab group with the instructor at the helm
        foreach (Cohort::all() as $cohort) {
            $cohort->labGroups()->firstOrCreate(
                ['name' => 'Group 1'],
                ['instructor_id' => $instructor?->id, 'capacity' => 15],
            );
        }
    }
}
