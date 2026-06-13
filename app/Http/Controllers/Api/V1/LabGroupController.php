<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLabGroupRequest;
use App\Http\Requests\UpdateLabGroupRequest;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\LabGroup;
use Illuminate\Http\Request;

class LabGroupController extends Controller
{
    public function index(Request $request, Cohort $cohort)
    {
        abort_unless($cohort->isManagedBy($request->user()), 403, 'Forbidden');

        return $this->ok($cohort->labGroups()->with('instructor', 'students')->get());
    }

    public function show(Request $request, LabGroup $labGroup)
    {
        abort_unless($labGroup->cohort->isManagedBy($request->user()), 403, 'Forbidden');

        return $this->ok($labGroup->load('instructor', 'students'));
    }

    public function store(StoreLabGroupRequest $request, Cohort $cohort)
    {
        abort_unless($cohort->isManagedBy($request->user()), 403, 'Forbidden');

        $labGroup = $cohort->labGroups()->create([
            'name' => $request->name,
            'instructor_id' => $request->instructor_id,
            'capacity' => $request->input('capacity', 15),
        ]);

        $this->syncStudents($labGroup, $request->input('student_ids', []));

        return $this->ok($labGroup->load('students'), 'Lab group created', 201);
    }

    public function update(UpdateLabGroupRequest $request, LabGroup $labGroup)
    {
        abort_unless($labGroup->cohort->isManagedBy($request->user()), 403, 'Forbidden');

        $labGroup->update($request->only('name', 'instructor_id', 'capacity'));

        if ($request->has('student_ids')) {
            $this->syncStudents($labGroup, $request->input('student_ids', []));
        }

        return $this->ok($labGroup->load('students'), 'Lab group updated');
    }

    public function destroy(Request $request, LabGroup $labGroup)
    {
        abort_unless($labGroup->cohort->isManagedBy($request->user()), 403, 'Forbidden');

        // let the students go first, so nobody's enrollment points at a dead group
        Enrollment::where('lab_group_id', $labGroup->id)->update(['lab_group_id' => null]);

        $labGroup->delete();

        return $this->ok(null, 'Lab group deleted');
    }

    // the groups an instructor teaches, each with who's in it
    public function mine(Request $request)
    {
        $groups = LabGroup::where('instructor_id', $request->user()->id)
            ->with('students:id,name,email', 'cohort:id,name')
            ->get();

        return $this->ok($groups);
    }

    // a single group the instructor teaches: roster + cohort courses/components (grading board)
    public function mineShow(Request $request, LabGroup $labGroup)
    {
        abort_unless((int) $labGroup->instructor_id === (int) $request->user()->id, 403, 'Forbidden');

        $labGroup->load([
            'students:id,name,email',
            'cohort:id,name',
            'cohort.courses:id,cohort_id,name,total_points',
            'cohort.courses.components:id,course_id,component_type,weight,raw_max',
        ]);

        return $this->ok($labGroup);
    }

    // the group this student was placed in
    public function myGroup(Request $request)
    {
        $groups = LabGroup::whereHas('students', fn ($q) => $q->whereKey($request->user()->id))
            ->with('instructor:id,name')
            ->get();

        return $this->ok($groups);
    }

    // reset the roster: clear out who's in the group, then slot in the new lot
    // (only students already enrolled in this cohort)
    private function syncStudents(LabGroup $labGroup, array $studentIds): void
    {
        Enrollment::where('lab_group_id', $labGroup->id)->update(['lab_group_id' => null]);

        if ($studentIds) {
            Enrollment::where('cohort_id', $labGroup->cohort_id)
                ->whereIn('student_id', $studentIds)
                ->update(['lab_group_id' => $labGroup->id]);
        }
    }
}
