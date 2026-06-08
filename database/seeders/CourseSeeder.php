<?php

namespace Database\Seeders;

use App\Models\Cohort;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Cohort::all() as $cohort) {
            $course = $cohort->courses()->firstOrCreate(
                ['name' => 'Laravel Fundamentals'],
                ['total_points' => 100],
            );

            $components = [
                ['component_type' => 'lab', 'weight' => 40, 'raw_max' => 70],
                ['component_type' => 'exam', 'weight' => 60, 'raw_max' => 100],
            ];

            foreach ($components as $component) {
                $course->components()->firstOrCreate(
                    ['component_type' => $component['component_type']],
                    $component,
                );
            }
        }
    }
}
