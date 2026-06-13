<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Cohort;
use App\Models\Course;
use App\Models\Engagement;
use App\Models\Enrollment;
use App\Models\ExcuseRequest;
use App\Models\Session;
use App\Models\Submission;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PortalMeTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private Cohort $cohort;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::factory()->create(['role' => 'student']);
        $this->cohort = $this->makeCohort();
        Enrollment::create(['student_id' => $this->student->id, 'cohort_id' => $this->cohort->id]);
    }

    private function makeCohort(): Cohort
    {
        $branch = Branch::create(['name' => 'Cairo']);
        $track = Track::create(['branch_id' => $branch->id, 'name' => 'Web']);

        return Cohort::create(['track_id' => $track->id, 'name' => 'Intake 45', 'status' => 'active']);
    }

    public function test_me_assignments_returns_cohort_assignments_with_submission(): void
    {
        $course = Course::create(['cohort_id' => $this->cohort->id, 'name' => 'Vue', 'total_points' => 100]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'title' => 'Portal SPA',
            'due_at' => now()->subDays(3),
        ]);
        Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'url' => 'https://github.com/x/portal',
            'file_path' => null,
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->student);

        $this->getJson('/api/v1/me/assignments')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.title', 'Portal SPA')
            ->assertJsonPath('data.items.0.course_name', 'Vue')
            ->assertJsonPath('data.items.0.submission.kind', 'url')
            ->assertJsonPath('data.items.0.submission.days_late', 3)
            ->assertJsonPath('data.items.0.submission.score', null);
    }

    public function test_me_assignments_excludes_other_cohorts(): void
    {
        $otherCohort = $this->makeCohort();
        $otherCourse = Course::create(['cohort_id' => $otherCohort->id, 'name' => 'Angular', 'total_points' => 100]);
        Assignment::factory()->create(['course_id' => $otherCourse->id]);

        Sanctum::actingAs($this->student);

        $this->getJson('/api/v1/me/assignments')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 0);
    }

    public function test_me_excuses_returns_only_own_with_mapped_fields(): void
    {
        $engagement = Engagement::factory()->create(['engagement_type' => 'lab']);
        $session = Session::factory()->create([
            'engagement_id' => $engagement->id,
            'session_date' => '2026-05-18',
        ]);
        $attendance = Attendance::create([
            'session_id' => $session->id,
            'student_id' => $this->student->id,
            'status' => 'absent',
        ]);
        ExcuseRequest::create([
            'attendance_id' => $attendance->id,
            'student_id' => $this->student->id,
            'reason' => 'Family emergency.',
            'status' => 'requested',
        ]);

        $other = User::factory()->create(['role' => 'student']);
        $otherAttendance = Attendance::create([
            'session_id' => $session->id,
            'student_id' => $other->id,
            'status' => 'absent',
        ]);
        ExcuseRequest::create([
            'attendance_id' => $otherAttendance->id,
            'student_id' => $other->id,
            'reason' => 'Not mine.',
            'status' => 'requested',
        ]);

        Sanctum::actingAs($this->student);

        $this->getJson('/api/v1/me/excuses')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.reason', 'Family emergency.')
            ->assertJsonPath('data.items.0.session_title', 'Lab session')
            ->assertJsonPath('data.items.0.session_date', '2026-05-18')
            ->assertJsonPath('data.items.0.status', 'requested');
    }

    public function test_me_endpoints_require_auth(): void
    {
        $this->getJson('/api/v1/me/assignments')->assertUnauthorized();
        $this->getJson('/api/v1/me/excuses')->assertUnauthorized();
    }
}
