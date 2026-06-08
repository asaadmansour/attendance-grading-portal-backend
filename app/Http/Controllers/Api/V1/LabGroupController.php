<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLabGroupRequest;
use App\Http\Requests\UpdateLabGroupRequest;
use App\Models\Cohort;
use App\Models\LabGroup;
use Illuminate\Http\Request;

class LabGroupController extends Controller
{
    public function index(Request $request, Cohort $cohort)
    {
        abort_unless($cohort->isManagedBy($request->user()), 403, 'Forbidden');

        return $this->ok($cohort->labGroups()->with('instructor')->get());
    }

    public function show(Request $request, LabGroup $labGroup)
    {
        abort_unless($labGroup->cohort->isManagedBy($request->user()), 403, 'Forbidden');

        return $this->ok($labGroup->load('instructor'));
    }

    public function store(StoreLabGroupRequest $request, Cohort $cohort)
    {
        abort_unless($cohort->isManagedBy($request->user()), 403, 'Forbidden');

        $labGroup = $cohort->labGroups()->create([
            'name' => $request->name,
            'instructor_id' => $request->instructor_id,
            'capacity' => $request->input('capacity', 15),
        ]);

        return $this->ok($labGroup, 'Lab group created', 201);
    }

    public function update(UpdateLabGroupRequest $request, LabGroup $labGroup)
    {
        abort_unless($labGroup->cohort->isManagedBy($request->user()), 403, 'Forbidden');

        $labGroup->update($request->validated());

        return $this->ok($labGroup, 'Lab group updated');
    }

    public function destroy(Request $request, LabGroup $labGroup)
    {
        abort_unless($labGroup->cohort->isManagedBy($request->user()), 403, 'Forbidden');

        $labGroup->delete();

        return $this->ok(null, 'Lab group deleted');
    }
}
