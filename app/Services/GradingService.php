<?php

namespace App\Services;

use App\Models\User;
use App\Models\AttendanceLedger;
use App\Models\ComponentGrade;
use Carbon\Carbon;

class GradingService
{
    public function normalize(float $rawScore, float $rawMax, float $weight): float
    {
        if($rawMax == 0)return 0;
        return ($rawScore / $rawMax)* $weight;
    }

    public function grandTotalFor(User $student): array
    {
        $ledgerBalance = (int) (AttendanceLedger::where('student_id', $student->id)->value('balance') ?? 250);

        $grades = ComponentGrade::with('courseComponent.course')->where('student_id', $student->id)->get();

        $courses = $grades
            ->groupBy(fn (ComponentGrade $grade) => $grade->courseComponent->course_id)
            ->map(function ($courseGrades) {
                $course = $courseGrades->first()->courseComponent->course;

                return [
                    'course_id' => $course->id,
                    'course'    => $course->name,
                    'total'     => round((float) $courseGrades->sum('normalized_score'), 2),
                ];
            })
            ->values();

        $coursesTotal = round((float) $courses->sum('total'), 2);

        return [
            'ledger_balance' => $ledgerBalance,
            'courses'        => $courses,
            'grand_total'    => round($ledgerBalance + $coursesTotal, 2),
        ];
    }

    public function latePenalty(float $score, Carbon $dueDate, Carbon $submittedAt): float
    {
        if ($submittedAt->lessThanOrEqualTo($dueDate)) {
            return $score;              
        }
        $daysLate = $dueDate->diffInDays($submittedAt);
        $daysLate = min($daysLate, 4); 
        $penalty = $score * $daysLate * 0.25;
        return $score - $penalty;
    }
}