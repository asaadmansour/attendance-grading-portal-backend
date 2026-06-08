<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceLedger;
use App\Models\Enrollment;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $instructor;
    private User $student;
    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instructor = User::factory()->create(['role' => 'instructor']);
        $this->student    = User::factory()->create(['role' => 'student']);

        $this->session = Session::factory()->create([
            'is_delivered' => false,
        ]);

        AttendanceLedger::create([
            'student_id' => $this->student->id,
            'balance'    => 250,
        ]);

        Enrollment::factory()->create([
            'student_id' => $this->student->id,
            'cohort_id'  => $this->session->engagement->cohort_id,
        ]);
    }

    
    public function test_instructor_can_check_in_a_student(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson("/api/sessions/{$this->session->id}/check-in", [
                'student_id' => $this->student->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.student_id', $this->student->id)
            ->assertJsonPath('data.session_id', $this->session->id);

        $this->assertDatabaseHas('attendances', [
            'session_id' => $this->session->id,
            'student_id' => $this->student->id,
        ]);

        $this->assertNotNull(
            Attendance::where('session_id', $this->session->id)
                ->where('student_id', $this->student->id)
                ->first()
                ->arrived_at
        );
    }

    public function test_check_in_is_idempotent_second_scan_does_not_overwrite_arrived_at(): void
    {
        $this->actingAs($this->instructor)
            ->postJson("/api/sessions/{$this->session->id}/check-in", [
                'student_id' => $this->student->id,
            ]);

        $first = Attendance::where('session_id', $this->session->id)
            ->where('student_id', $this->student->id)
            ->first()
            ->arrived_at;

        $this->actingAs($this->instructor)
            ->postJson("/api/sessions/{$this->session->id}/check-in", [
                'student_id' => $this->student->id,
            ]);

        $second = Attendance::where('session_id', $this->session->id)
            ->where('student_id', $this->student->id)
            ->first()
            ->arrived_at;

        $this->assertEquals($first, $second);
    }

    public function test_student_cannot_check_in_others(): void
    {
        $other = User::factory()->create(['role' => 'student']);

        $this->actingAs($this->student)
            ->postJson("/api/sessions/{$this->session->id}/check-in", [
                'student_id' => $other->id,
            ])
            ->assertForbidden();
    }

    public function test_check_out_marks_student_present_and_does_not_deduct_ledger(): void
    {
        $this->actingAs($this->instructor)
            ->postJson("/api/sessions/{$this->session->id}/check-in", [
                'student_id' => $this->student->id,
            ]);

        $this->actingAs($this->instructor)
            ->postJson("/api/sessions/{$this->session->id}/check-out", [
                'student_id' => $this->student->id,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'present');

        $this->assertDatabaseHas('attendance_ledgers', [
            'student_id' => $this->student->id,
            'balance' => 250,
        ]);
    }

    public function test_reconcile_deducts_25_for_unexcused_absent_student(): void
    {
        $ta = User::factory()->create(['role' => 'track_admin']);

        $this->actingAs($ta)
            ->postJson("/api/sessions/{$this->session->id}/reconcile")
            ->assertStatus(200);

        $this->assertDatabaseHas('attendance_ledgers', [
            'student_id' => $this->student->id,
            'balance' => 225,
        ]);
    }

    public function test_reconcile_deducts_only_5_when_excuse_is_already_approved(): void
    {
        $ta = User::factory()->create(['role' => 'track_admin']);

        $attendance = Attendance::create([
            'session_id' => $this->session->id,
            'student_id' => $this->student->id,
            'status' => 'absent',
        ]);

        \App\Models\ExcuseRequest::create([
            'attendance_id' => $attendance->id,
            'student_id' => $this->student->id,
            'reason' => 'Test excuse',
            'status' => 'approved',
            'reviewed_by' => $ta->id,
        ]);

        $this->actingAs($ta)
            ->postJson("/api/sessions/{$this->session->id}/reconcile")
            ->assertStatus(200);

        $this->assertDatabaseHas('attendance_ledgers', [
            'student_id' => $this->student->id,
            'balance' => 245,
        ]);
    }
}