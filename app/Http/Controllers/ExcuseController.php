<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewExcuseRequest;
use App\Http\Requests\StoreExcuseRequest;
use App\Models\Attendance;
use App\Models\AttendanceLedger;
use App\Models\ExcuseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExcuseController extends Controller
{
    public function store(StoreExcuseRequest $request): JsonResponse
    {
        $this->authorize('create', ExcuseRequest::class);

        $validated   = $request->validated();
        $attendanceId = $validated['attendance_id'];

        $attendance = Attendance::findOrFail($attendanceId);
        abort_if(
            $attendance->student_id !== auth()->id(),
            403,
            'You may only excuse your own absences.'
        );

        abort_if(
            $attendance->status === 'present',
            422,
            'You cannot excuse a session you attended.'
        );

        abort_if(
            ExcuseRequest::where('attendance_id', $attendanceId)
                ->whereIn('status', ['requested', 'approved'])
                ->exists(),
            422,
            'An excuse request already exists for this attendance record.'
        );

        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = app(\App\Services\FileStorage::class)
                ->store($request->file('attachment'), 'excuses');
        }

        $excuse = ExcuseRequest::create([
            'attendance_id'   => $attendanceId,
            'student_id'      => auth()->id(),
            'reason'          => $validated['reason'],
            'attachment_path' => $attachmentPath,
            'status'          => ExcuseRequest::STATUS_REQUESTED,
        ]);

        return response()->json(['data' => $excuse], 201);
    }


    public function review(ReviewExcuseRequest $request, ExcuseRequest $excuse): JsonResponse
    {
        $this->authorize('review', $excuse);

        abort_if(
            ! $excuse->isPending(),
            422,
            'Only pending excuse requests can be reviewed.'
        );

        $action = $request->validated('action');

        DB::transaction(function () use ($excuse, $action) {
            $excuse->update([
                'status'      => $action,
                'reviewed_by' => auth()->id(),
            ]);

            if ($action === ExcuseRequest::STATUS_APPROVED) {
                $excuse->attendance->update(['status' => 'excused']);

                $ledger = AttendanceLedger::where('student_id', $excuse->student_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $ledger->adjustDeduction(
                    ExcuseRequest::DEDUCTION_UNEXCUSED,
                    ExcuseRequest::DEDUCTION_EXCUSED
                );
            }
        });

        return response()->json(['data' => $excuse->fresh()->load('reviewer')]);
    }

    public function index(): JsonResponse
    {
        $user  = auth()->user();
        $query = ExcuseRequest::with(['student', 'attendance.session', 'reviewer']);

        if ($user->role === 'student') {
            $query->where('student_id', $user->id);
        }
        // TA / BM scoping can be added here when Sameh's cohort relationships land.

        $excuses = $query->latest()->paginate(request('per_page', 20));

        return response()->json([
            'data' => $excuses->items(),
            'meta' => [
                'current_page' => $excuses->currentPage(),
                'last_page'    => $excuses->lastPage(),
                'per_page'     => $excuses->perPage(),
                'total'        => $excuses->total(),
            ],
        ]);
    }

    public function approve(ExcuseRequest $excuse): JsonResponse
    {
        $this->authorize('review', $excuse);

        abort_if(
            ! $excuse->isPending(),
            422,
            'Only pending excuse requests can be reviewed.'
        );

        DB::transaction(function () use ($excuse) {
            $excuse->update([
                'status'      => ExcuseRequest::STATUS_APPROVED,
                'reviewed_by' => auth()->id(),
            ]);

            $excuse->attendance->update(['status' => 'excused']);

            $ledger = AttendanceLedger::where('student_id', $excuse->student_id)
                ->lockForUpdate()
                ->firstOrFail();

            $ledger->adjustDeduction(
                ExcuseRequest::DEDUCTION_UNEXCUSED,
                ExcuseRequest::DEDUCTION_EXCUSED
            );
        });

        return response()->json(['data' => $excuse->fresh()->load('reviewer')]);
    }

    public function reject(ExcuseRequest $excuse): JsonResponse
    {
        $this->authorize('review', $excuse);

        abort_if(
            ! $excuse->isPending(),
            422,
            'Only pending excuse requests can be reviewed.'
        );

        $excuse->update([
            'status'      => ExcuseRequest::STATUS_REJECTED,
            'reviewed_by' => auth()->id(),
        ]);

        return response()->json(['data' => $excuse->fresh()->load('reviewer')]);
    }
}