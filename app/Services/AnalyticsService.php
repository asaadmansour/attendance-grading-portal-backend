<?php

namespace App\Services;

use App\Models\User;
use App\Models\Enrollment;
use App\Models\AttendanceLedger;
use App\Models\ComponentGrade;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Submission;

class AnalyticsService
{
    public function atRiskStudents($cohortId): array
    {
        // 1. Students in this cohort 
        $studentIds = Enrollment::where('cohort_id', $cohortId)->pluck('student_id');

        $students = User::whereIn('id', $studentIds)
            ->where('role', 'student')
            ->get(['id', 'name']);

        
        $balances = AttendanceLedger::whereIn('student_id', $studentIds)
            ->pluck('balance', 'student_id');

        $gradesByStudent = ComponentGrade::with('courseComponent.course')
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy('student_id');

        $atRisk = [];

        // 2. Loop over students IN MEMORY 
        foreach ($students as $student) {

            // ledger balance 
            $ledger = (int) ($balances[$student->id] ?? 250);

            // per-course totals
            $courseTotals = ($gradesByStudent[$student->id] ?? collect())
                ->groupBy(fn (ComponentGrade $grade) => $grade->courseComponent->course_id)
                ->map(fn ($courseGrades) => round((float) $courseGrades->sum('normalized_score'), 2));

            // 3. apply the rule
            $lowAttendance = $ledger < 150;
            $lowCourses    = $courseTotals->filter(fn ($total) => $total < 60);
            $lowGrade      = $lowCourses->isNotEmpty();

            if ($lowAttendance || $lowGrade) {
                $reasons = [];
                if ($lowAttendance) {
                    $reasons[] = 'low_attendance';
                }
                if ($lowGrade) {
                    $reasons[] = 'low_grade';
                }

                $atRisk[] = [
                    'student_id'     => $student->id,
                    'name'           => $student->name,
                    'ledger_balance' => $ledger,
                    'reason'         => $reasons,
                    'low_courses'    => $lowCourses->all(), 
                ];
            }
        }

        // 4. return the flagged students.
        return $atRisk;
    }

    /**
     * Count of students per grade band, per course in the cohort.
     * Band = each student's course total (sum of normalized_score), 0–100.
     * Students with no grade in a course are reported as `ungraded`.
     */
    public function gradeDistribution($cohortId): array
    {
        $studentIds = Enrollment::where('cohort_id', $cohortId)->pluck('student_id');
        $courses    = Course::where('cohort_id', $cohortId)->get(['id', 'name']);

        // Bulk: every grade for these students, once. courseComponent gives course_id.
        $grades = ComponentGrade::with('courseComponent')
            ->whereIn('student_id', $studentIds)
            ->get();

        // [course_id][student_id] => running course total
        $totals = [];
        foreach ($grades as $grade) {
            $courseId  = $grade->courseComponent->course_id;
            $studentId = $grade->student_id;
            $totals[$courseId][$studentId] = ($totals[$courseId][$studentId] ?? 0) + (float) $grade->normalized_score;
        }

        $distribution = [];

        foreach ($courses as $course) {
            $bands    = ['90-100' => 0, '80-89' => 0, '70-79' => 0, '60-69' => 0, '<60' => 0];
            $ungraded = 0;

            foreach ($studentIds as $studentId) {
                if (! isset($totals[$course->id][$studentId])) {
                    $ungraded++;
                    continue;
                }

                $bands[$this->gradeBand(round($totals[$course->id][$studentId], 2))]++;
            }

            $distribution[] = [
                'course_id' => $course->id,
                'course'    => $course->name,
                'bands'     => $bands,
                'ungraded'  => $ungraded,
            ];
        }

        return $distribution;
    }

    /**
     * Cohort-wide deliverable counts: submitted / late / missing.
     *  - late    = a submission whose submitted_at is after the assignment due_at.
     *  - missing = an enrolled student with no submission for a PAST-DUE assignment.
     *  - null due_at: a submission counts as submitted (never late); a non-submission
     *    is never missing (no deadline to miss). Not-yet-due assignments are uncounted.
     */
    public function submissionStatus($cohortId): array
    {
        $studentIds = Enrollment::where('cohort_id', $cohortId)->pluck('student_id');
        $courseIds  = Course::where('cohort_id', $cohortId)->pluck('id');

        $assignments = Assignment::whereIn('course_id', $courseIds)->get(['id', 'due_at']);

        // [assignment_id][student_id] => submitted_at (Carbon)
        $submitted = [];
        foreach (Submission::whereIn('assignment_id', $assignments->pluck('id'))
            ->whereIn('student_id', $studentIds)
            ->get(['assignment_id', 'student_id', 'submitted_at']) as $submission) {
            $submitted[$submission->assignment_id][$submission->student_id] = $submission->submitted_at;
        }

        $now = now();
        $counts = ['submitted' => 0, 'late' => 0, 'missing' => 0];

        foreach ($assignments as $assignment) {
            foreach ($studentIds as $studentId) {
                $submittedAt = $submitted[$assignment->id][$studentId] ?? null;

                if ($submittedAt !== null) {
                    if ($assignment->due_at !== null && $submittedAt->greaterThan($assignment->due_at)) {
                        $counts['late']++;
                    } else {
                        $counts['submitted']++;
                    }
                    continue;
                }

                // No submission → missing only if the deadline has already passed.
                if ($assignment->due_at !== null && $assignment->due_at->lessThan($now)) {
                    $counts['missing']++;
                }
            }
        }

        return $counts;
    }

    /**
     * Map a 0–100 course total to its grade band.
     */
    private function gradeBand(float $total): string
    {
        return match (true) {
            $total >= 90 => '90-100',
            $total >= 80 => '80-89',
            $total >= 70 => '70-79',
            $total >= 60 => '60-69',
            default      => '<60',
        };
    }
}
