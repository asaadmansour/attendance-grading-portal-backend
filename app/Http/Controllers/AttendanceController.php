<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInRequest;
use App\Http\Requests\CheckOutRequest;
use App\Models\Attendance;
use App\Models\AttendanceLedger;
use App\Models\ExcuseRequest;
use App\Models\Session;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function checkIn(CheckInRequest $request, Session $session): JsonResponse
    {
        $this->authorize('checkIn', $session);

        $student = User::findOrFail($request->validated('student_id'));

        $attendance = DB::transaction(function () use ($session, $student) {
            $attendance = Attendance::firstOrCreate(
                [
                    'session_id' => $session->id,
                    'student_id' => $student->id,
                ],
                [
                    'status'     => 'absent',
                    'arrived_at' => null,
                    'left_at'    => null,
                ]
            );

            if ($attendance->arrived_at === null) {
                $attendance->update(['arrived_at' => now()]);
            }

            return $attendance->fresh();
        });

        return response()->json([
            'data' => [
                'id'         => $attendance->id,
                'student_id' => $attendance->student_id,
                'session_id' => $attendance->session_id,
                'arrived_at' => $attendance->arrived_at,
                'status'     => $attendance->status,
            ],
        ]);
    }

    public function checkOut(CheckOutRequest $request, Session $session): JsonResponse
    {
        $this->authorize('checkOut', $session);

        $student = User::findOrFail($request->validated('student_id'));

        $attendance = DB::transaction(function () use ($session, $student) {
            $attendance = Attendance::firstOrCreate(
                [
                    'session_id' => $session->id,
                    'student_id' => $student->id,
                ],
                [
                    'status'     => 'absent',
                    'arrived_at' => null,
                    'left_at'    => null,
                ]
            );

            if ($attendance->left_at !== null) {
                return $attendance;
            }

            $attendance->update([
                'left_at' => now(),
                'status' => 'present',
            ]);

            $ledger = AttendanceLedger::firstOrCreate(
                ['student_id' => $student->id],
                ['balance' => 250]
            );

            return $attendance->fresh();
        });

        return response()->json([
            'data' => [
                'id' => $attendance->id,
                'student_id' => $attendance->student_id,
                'session_id' => $attendance->session_id,
                'arrived_at' => $attendance->arrived_at,
                'left_at' => $attendance->left_at,
                'status'  => $attendance->status,
            ],
        ]);
    }

    public function reconcile(Session $session): JsonResponse
    {
        $this->authorize('checkIn', $session);

        $cohortId = $session->engagement?->cohort_id;

        if (!$cohortId) {
            return response()->json([
                'message' => 'Session has no engagement.'
            ], 422);
        }

        $enrolledIds = \App\Models\Enrollment::where('cohort_id', $cohortId)
            ->pluck('student_id');

        DB::transaction(function () use ($session, $enrolledIds) {

            foreach ($enrolledIds as $studentId) {

                $attendance = Attendance::firstOrCreate(
                    [
                        'session_id' => $session->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'status' => 'absent',
                        'arrived_at' => null,
                        'left_at' => null,
                    ]
                );

                if ($attendance->status !== 'absent') {
                    continue;
                }

                $excuse = ExcuseRequest::where('attendance_id', $attendance->id)
                    ->where('status', 'approved')
                    ->first();

                $deduction = $excuse
                    ? ExcuseRequest::DEDUCTION_EXCUSED  //5
                    : ExcuseRequest::DEDUCTION_UNEXCUSED; //25

                $ledger = AttendanceLedger::firstOrCreate(
                    ['student_id' => $studentId],
                    ['balance' => 250]
                );

                $alreadyProcessed = \DB::table('attendance_reconciliations')
                    ->where('session_id', $session->id)
                    ->where('student_id', $studentId)
                    ->exists();

                if (!$alreadyProcessed) {
                    $ledger->deduct($deduction);

                    \DB::table('attendance_reconciliations')->insert([
                        'session_id' => $session->id,
                        'student_id' => $studentId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return response()->json([
            'data' => [
                'message' => 'Session reconciled successfully.'
            ]
        ]);
    }
}