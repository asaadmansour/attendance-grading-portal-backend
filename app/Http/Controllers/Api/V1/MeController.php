<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\AttendanceLedger;
use App\Models\Enrollment;
use App\Models\ExcuseRequest;
use App\Models\Submission;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class MeController extends Controller
{
    public function attendance(Request $request)
    {
        $studentId = $request->user()->id;

        $rows = Attendance::with('session.engagement')
            ->where('student_id', $studentId)
            ->latest('id')
            ->get()
            ->map(function (Attendance $a) {
                $type = $a->session?->engagement?->engagement_type ?? 'session';

                return [
                    'id'           => $a->id,
                    'session_date' => $a->session?->session_date?->toDateString(),
                    'title'        => ucfirst($type).' session',
                    'type'         => $type,
                    'arrived_at'   => $a->arrived_at,
                    'left_at'      => $a->left_at,
                    'status'       => $a->status,
                ];
            });

        return $this->ok([
            'ledger_balance' => $this->ledgerBalance($studentId),
            'rows'           => $rows,
        ]);
    }

    public function grades(Request $request, GradingService $grading)
    {
        return $this->ok($grading->breakdownFor($request->user()));
    }

    public function progress(Request $request, GradingService $grading)
    {
        $courses = $grading->breakdownFor($request->user())['courses'];

        return $this->ok([
            'points' => collect($courses)->map(fn ($course) => [
                'label' => $course['name'],
                'total' => $course['earned'],
            ])->values(),
        ]);
    }

    public function assignments(Request $request)
    {
        $studentId = $request->user()->id;
        $cohortId = Enrollment::where('student_id', $studentId)->value('cohort_id');

        $perPage = min(max((int) $request->integer('per_page', 50), 1), 100);

        $assignments = Assignment::with('course:id,name,cohort_id')
            ->whereHas('course', fn ($q) => $q->where('cohort_id', $cohortId))
            ->orderBy('due_at')
            ->paginate($perPage);

        $submissions = Submission::where('student_id', $studentId)
            ->whereIn('assignment_id', collect($assignments->items())->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        return $this->ok([
            'items' => collect($assignments->items())->map(function (Assignment $a) use ($submissions) {
                $sub = $submissions->get($a->id);

                return [
                    'id'          => $a->id,
                    'course_name' => $a->course?->name,
                    'title'       => $a->title,
                    'due_at'      => $a->due_at,
                    'submission'  => $sub ? [
                        'id'           => $sub->id,
                        'kind'         => $sub->url ? 'url' : 'file',
                        'url'          => $sub->url,
                        'file_name'    => $sub->file_path ? basename($sub->file_path) : null,
                        'submitted_at' => $sub->submitted_at,
                        'days_late'    => $a->due_at && $sub->submitted_at->greaterThan($a->due_at)
                            ? (int) $a->due_at->diffInDays($sub->submitted_at)
                            : 0,
                        'score'        => null,
                    ] : null,
                ];
            }),
            'meta' => $this->meta($assignments),
        ]);
    }

    public function excuses(Request $request)
    {
        $studentId = $request->user()->id;
        $perPage = min(max((int) $request->integer('per_page', 50), 1), 100);

        $excuses = ExcuseRequest::with('attendance.session.engagement')
            ->where('student_id', $studentId)
            ->latest('id')
            ->paginate($perPage);

        return $this->ok([
            'items' => collect($excuses->items())->map(function (ExcuseRequest $e) {
                $type = $e->attendance?->session?->engagement?->engagement_type ?? 'session';

                return [
                    'id'            => $e->id,
                    'attendance_id' => $e->attendance_id,
                    'session_title' => ucfirst($type).' session',
                    'session_date'  => $e->attendance?->session?->session_date?->toDateString(),
                    'reason'        => $e->reason,
                    'status'        => $e->status,
                    'created_at'    => $e->created_at,
                ];
            }),
            'meta' => $this->meta($excuses),
        ]);
    }

    private function meta(LengthAwarePaginator $paginator): array
    {
        return [
            'page'      => $paginator->currentPage(),
            'per_page'  => $paginator->perPage(),
            'total'     => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    private function ledgerBalance(int $studentId): int
    {
        return (int) (AttendanceLedger::where('student_id', $studentId)->value('balance') ?? 250);
    }
}
