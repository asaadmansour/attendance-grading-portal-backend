<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLedger;
use App\Services\GradingService;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function attendance(Request $request)
    {
        $studentId = $request->user()->id;

        return $this->ok([
            'ledger_balance' => $this->ledgerBalance($studentId),
            'records' => Attendance::where('student_id', $studentId)
                ->latest('id')
                ->get(['session_id', 'status', 'arrived_at', 'left_at']),
        ]);
    }

    public function grades(Request $request, GradingService $grading)
    {
        return $this->ok($grading->grandTotalFor($request->user()));
    }

    public function progress(Request $request, GradingService $grading)
    {
        $studentId = $request->user()->id;

        $counts = Attendance::where('student_id', $studentId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $grades = $grading->grandTotalFor($request->user());

        return $this->ok([
            'attendance' => [
                'present' => (int) ($counts['present'] ?? 0),
                'absent' => (int) ($counts['absent'] ?? 0),
                'excused' => (int) ($counts['excused'] ?? 0),
                'ledger_balance' => $this->ledgerBalance($studentId),
            ],
            'grades' => [
                'courses' => $grades['courses'],
                'grand_total' => $grades['grand_total'],
            ],
        ]);
    }

    private function ledgerBalance(int $studentId): int
    {
        return (int) (AttendanceLedger::where('student_id', $studentId)->value('balance') ?? 250);
    }
}
