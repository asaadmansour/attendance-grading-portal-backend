<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ComponentGrade;
use App\Models\GradeOverride;

class GradeOverrideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grade = ComponentGrade::with('courseComponent')->first();
        $admin = User::where('role', 'track_admin')->first();

        // Depends on a graded component (ComponentGradeSeeder) and a track admin.
        if (! $grade || ! $admin) {
            $this->command?->warn('GradeOverrideSeeder skipped: needs a component grade and a track admin.');
            return;
        }

        // Override to the component's full weight (max valid normalized score).
        $newValue = (float) $grade->courseComponent->weight;

        // component_grade_id / original_value / overridden_by are set outside fillable.
        $override = new GradeOverride([
            'note'      => 'Seeded override: re-marked after a regrade request.',
            'new_value' => $newValue,
        ]);
        $override->component_grade_id = $grade->id;
        $override->original_value     = $grade->normalized_score;
        $override->overridden_by      = $admin->id;
        $override->save();

        // Keep the grade consistent with its latest override (mirrors the override endpoint).
        $grade->normalized_score = $newValue;
        $grade->save();
    }
}
