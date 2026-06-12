<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CourseComponent;
use App\Models\ComponentGrade;
use App\Services\GradingService;

class ComponentGradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student    = User::where('role', 'student')->first();
        $instructor = User::where('role', 'instructor')->first();
        $component  = CourseComponent::first();

        // course_components is seeded by another domain; skip cleanly if it isn't present yet.
        if (! $student || ! $instructor || ! $component) {
            $this->command?->warn('ComponentGradeSeeder skipped: needs a student, an instructor, and a course component.');
            return;
        }

        $rawScore   = min(63, (float) $component->raw_max);
        $normalized = app(GradingService::class)->normalize($rawScore, $component->raw_max, $component->weight);

        // entered_by / normalized_score are set outside fillable — never mass-assigned.
        $grade = new ComponentGrade([
            'course_component_id' => $component->id,
            'student_id'          => $student->id,
            'raw_score'           => $rawScore,
        ]);
        $grade->normalized_score = $normalized;
        $grade->entered_by       = $instructor->id;
        $grade->save();
    }
}
