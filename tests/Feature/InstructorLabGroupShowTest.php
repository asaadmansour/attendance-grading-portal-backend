<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Cohort;
use App\Models\Course;
use App\Models\CourseComponent;
use App\Models\Enrollment;
use App\Models\LabGroup;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorLabGroupShowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a lab group (with cohort -> courses -> components and one enrolled
     * student) taught by the given instructor.
     */
    private function makeLabGroupFor(User $instructor): LabGroup
    {
        $branch = Branch::create(['name' => 'Cairo']);
        $track  = Track::create(['branch_id' => $branch->id, 'name' => 'Web']);
        $cohort = Cohort::create(['track_id' => $track->id, 'name' => 'Web Dev - Spring 2026']);

        $course = Course::create(['cohort_id' => $cohort->id, 'name' => 'Frontend', 'total_points' => 100]);
        CourseComponent::create(['course_id' => $course->id, 'component_type' => 'lab',  'weight' => 20, 'raw_max' => 100]);
        CourseComponent::create(['course_id' => $course->id, 'component_type' => 'exam', 'weight' => 40, 'raw_max' => 100]);

        $labGroup = LabGroup::create([
            'cohort_id'     => $cohort->id,
            'instructor_id' => $instructor->id,
            'name'          => 'Lab Group A',
            'capacity'      => 15,
        ]);

        $student = User::factory()->create(['role' => 'student']);
        Enrollment::create([
            'student_id'   => $student->id,
            'cohort_id'    => $cohort->id,
            'lab_group_id' => $labGroup->id,
        ]);

        return $labGroup;
    }

    public function test_instructor_can_view_a_lab_group_they_teach(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $labGroup   = $this->makeLabGroupFor($instructor);

        $this->actingAs($instructor)
            ->getJson("/api/v1/my/lab-groups/{$labGroup->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $labGroup->id)
            ->assertJsonPath('data.name', 'Lab Group A')
            ->assertJsonPath('data.instructor_id', $instructor->id)
            ->assertJsonCount(1, 'data.students')
            ->assertJsonCount(1, 'data.cohort.courses')
            ->assertJsonCount(2, 'data.cohort.courses.0.components')
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'capacity', 'cohort_id', 'instructor_id',
                    'students' => [['id', 'name', 'email']],
                    'cohort' => [
                        'id', 'name',
                        'courses' => [[
                            'id', 'cohort_id', 'name', 'total_points',
                            'components' => [['id', 'course_id', 'component_type', 'weight', 'raw_max']],
                        ]],
                    ],
                ],
            ]);
    }

    public function test_instructor_cannot_view_a_lab_group_they_do_not_teach(): void
    {
        $owner    = User::factory()->create(['role' => 'instructor']);
        $labGroup = $this->makeLabGroupFor($owner);

        $other = User::factory()->create(['role' => 'instructor']);

        $this->actingAs($other)
            ->getJson("/api/v1/my/lab-groups/{$labGroup->id}")
            ->assertForbidden();
    }

    public function test_student_cannot_use_the_instructor_endpoint(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $labGroup   = $this->makeLabGroupFor($instructor);

        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->getJson("/api/v1/my/lab-groups/{$labGroup->id}")
            ->assertForbidden();
    }
}
