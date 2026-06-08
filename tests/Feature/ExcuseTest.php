<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceLedger;
use App\Models\ExcuseRequest;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExcuseTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private User $ta;
    private Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->student = User::factory()->create(['role' => 'student']);
        $this->ta      = User::factory()->create(['role' => 'track_admin']);

        $session = Session::factory()->create(['is_delivered' => true]);

        $this->attendance = Attendance::create([
            'session_id' => $session->id,
            'student_id' => $this->student->id,
            'status' => 'absent',
        ]);

        AttendanceLedger::create([
            'student_id' => $this->student->id,
            'balance' => 225,
        ]);
    }

    /** @test */
    public function student_can_submit_an_excuse_without_attachment(): void
    {
        $this->actingAs($this->student)
            ->postJson('/api/excuses', [
                'attendance_id' => $this->attendance->id,
                'reason'        => 'I was sick.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'requested');
    }

    /** @test */
    public function student_can_submit_an_excuse_with_a_valid_pdf_attachment(): void
    {
        $file = UploadedFile::fake()->create('medical.pdf', 500, 'application/pdf'); // 500 KB

        $this->actingAs($this->student)
            ->postJson('/api/excuses', [
                'attendance_id' => $this->attendance->id,
                'reason'        => 'Doctor visit.',
                'attachment'    => $file,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'requested');
    }

    /** @test */
    public function attachment_larger_than_1mb_is_rejected(): void
    {
        $bigFile = UploadedFile::fake()->create('big.pdf', 2048, 'application/pdf'); // 2 MB

        $this->actingAs($this->student)
            ->postJson('/api/excuses', [
                'attendance_id' => $this->attendance->id,
                'reason'        => 'Sick.',
                'attachment'    => $bigFile,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('attachment');
    }

    /** @test */
    public function attachment_with_wrong_mime_type_is_rejected(): void
    {
        $exeFile = UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream');

        $this->actingAs($this->student)
            ->postJson('/api/excuses', [
                'attendance_id' => $this->attendance->id,
                'reason'        => 'Sick.',
                'attachment'    => $exeFile,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('attachment');
    }

    /** @test */
    public function student_cannot_excuse_another_students_absence(): void
    {
        $other = User::factory()->create(['role' => 'student']);
        $otherAttendance = Attendance::create([
            'session_id' => $this->attendance->session_id,
            'student_id' => $other->id,
            'status'     => 'absent',
        ]);

        $this->actingAs($this->student)
            ->postJson('/api/excuses', [
                'attendance_id' => $otherAttendance->id,
                'reason'        => 'Not mine.',
            ])
            ->assertForbidden();
    }

    /** @test */
    public function instructor_cannot_submit_excuses(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);

        $this->actingAs($instructor)
            ->postJson('/api/excuses', [
                'attendance_id' => $this->attendance->id,
                'reason'        => 'Should not work.',
            ])
            ->assertForbidden();
    }

    /** @test */
    public function approving_an_excuse_credits_20_points_to_ledger(): void
    {
        $excuse = ExcuseRequest::create([
            'attendance_id' => $this->attendance->id,
            'student_id'    => $this->student->id,
            'reason'        => 'Medical.',
            'status'        => 'requested',
        ]);

        $this->actingAs($this->ta)
            ->patchJson("/api/excuses/{$excuse->id}", ['action' => 'approved'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('attendance_ledgers', [
            'student_id' => $this->student->id,
            'balance'    => 245,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id'     => $this->attendance->id,
            'status' => 'excused',
        ]);
    }

    /** @test */
    public function rejecting_an_excuse_leaves_ledger_unchanged(): void
    {
        $excuse = ExcuseRequest::create([
            'attendance_id' => $this->attendance->id,
            'student_id'    => $this->student->id,
            'reason'        => 'Medical.',
            'status'        => 'requested',
        ]);

        $this->actingAs($this->ta)
            ->patchJson("/api/excuses/{$excuse->id}", ['action' => 'rejected'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('attendance_ledgers', [
            'student_id' => $this->student->id,
            'balance'    => 225,
        ]);
    }

    /** @test */
    public function student_cannot_review_an_excuse(): void
    {
        $excuse = ExcuseRequest::create([
            'attendance_id' => $this->attendance->id,
            'student_id'    => $this->student->id,
            'reason'        => 'Medical.',
            'status'        => 'requested',
        ]);

        $this->actingAs($this->student)
            ->patchJson("/api/excuses/{$excuse->id}", ['action' => 'approved'])
            ->assertForbidden();
    }

    /** @test */
    public function already_reviewed_excuse_cannot_be_reviewed_again(): void
    {
        $excuse = ExcuseRequest::create([
            'attendance_id' => $this->attendance->id,
            'student_id'    => $this->student->id,
            'reason'        => 'Medical.',
            'status'        => 'approved',
            'reviewed_by'   => $this->ta->id,
        ]);

        $this->actingAs($this->ta)
            ->patchJson("/api/excuses/{$excuse->id}", ['action' => 'rejected'])
            ->assertStatus(422);
    }
}