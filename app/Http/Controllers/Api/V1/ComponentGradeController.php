<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ComponentGrade;
use App\Models\CourseComponent;
use App\Models\Submission;
use App\Http\Requests\StoreComponentGradeRequest;
use App\Http\Requests\UpdateComponentGradeRequest;
use App\Services\GradingService;
class ComponentGradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $student)
    {
        $studentUser = User::findOrFail($student);
        $studentGrades = ComponentGrade::with(['student','courseComponent','enteredBy'])->where('student_id',$studentUser->id)->get();
        return response()->json([
            'data'=>$studentGrades
        ],200); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreComponentGradeRequest $request, GradingService $grading)
    {
        $data = $request->validated();

        $component = CourseComponent::findOrFail($data['course_component_id']);

        abort_if($data['raw_score'] > $component->raw_max, 422, 'raw_score exceeds the component maximum.');

        $effectiveScore = $this->applyLatePenalty($data['raw_score'], $data['submission_id'] ?? null, $grading);

        $normalized = $grading->normalize(
            $effectiveScore,
            $component->raw_max,
            $component->weight
        );

        $grade = new ComponentGrade([
            'course_component_id' => $data['course_component_id'],
            'student_id'=> $data['student_id'],
            'raw_score' => $data['raw_score'],
        ]);
        $grade->normalized_score = $normalized;
        $grade->entered_by= auth()->id();
        $grade->save();

        return response()->json(['data' => $grade], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Correct a grade you previously entered.
     *
     * Only the grader who entered the grade may use this endpoint. Track Admin
     * changes to another grader's score must go through the override flow
     * (GRD-6) so the original value and note are preserved.
     */
    public function update(UpdateComponentGradeRequest $request, GradingService $grading, string $id)
    {
        $grade = ComponentGrade::findOrFail($id);

        abort_unless(
            (int) $grade->entered_by === (int) auth()->id(),
            403,
            'You can only correct grades you entered. Use the override flow to change another grader\'s score.'
        );

        $component = CourseComponent::findOrFail($grade->course_component_id);
        $data      = $request->validated();
        $rawScore  = $data['raw_score'];

        abort_if($rawScore > $component->raw_max, 422, 'raw_score exceeds the component maximum.');

        $effectiveScore = $this->applyLatePenalty($rawScore, $data['submission_id'] ?? null, $grading);

        $grade->raw_score        = $rawScore;
        $grade->normalized_score = $grading->normalize($effectiveScore, $component->raw_max, $component->weight);
        $grade->save();

        return response()->json(['data' => $grade], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Resolve the score to normalize, applying a late penalty only when the
     * grade is tied to a submission that had a real deadline.
     *
     *  - No submission_id (exam/lecture, no deliverable) → raw score as-is.
     *  - Submission exists but its assignment has no due_at → raw score as-is.
     *  - Submission with a due_at → penalty applied via GradingService.
     *
     * Read-only on submissions/assignments.
     */
    private function applyLatePenalty(float $rawScore, ?int $submissionId, GradingService $grading): float
    {
        if ($submissionId === null) {
            return $rawScore;
        }

        $submission = Submission::with('assignment')->findOrFail($submissionId);
        $dueAt      = $submission->assignment?->due_at;

        if ($dueAt === null) {
            return $rawScore;
        }

        return $grading->latePenalty($rawScore, $dueAt, $submission->submitted_at);
    }
}
