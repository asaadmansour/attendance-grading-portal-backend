<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCohortRequest;
use App\Http\Requests\UpdateCohortRequest;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CohortController extends Controller
{
    public function index(Request $request)
    {
        $query = Cohort::with('track');

        // track admins only see the cohorts they run
        if ($request->user()->role === 'track_admin') {
            $query->whereHas('tas', fn ($q) => $q->whereKey($request->user()->id));
        }

        return $this->ok($query->get());
    }

    public function show(Request $request, Cohort $cohort)
    {
        abort_unless($cohort->isManagedBy($request->user()), 403, 'Forbidden');

        return $this->ok($cohort->load('track', 'tas', 'courses.components', 'labGroups.instructor'));
    }

    // the cohort's enrolled students, each with the lab group they belong to
    public function students(Request $request, Cohort $cohort)
    {
        abort_unless($cohort->isManagedBy($request->user()), 403, 'Forbidden');

        return $this->ok($cohort->students()->get(['users.id', 'users.name', 'users.email']));
    }

    // enroll one or more students into the cohort
    public function enroll(Request $request, Cohort $cohort)
    {
        abort_unless($cohort->isManagedBy($request->user()), 403, 'Forbidden');

        $data = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => Rule::exists('users', 'id')->where('role', 'student'),
        ]);

        foreach ($data['student_ids'] as $studentId) {
            Enrollment::firstOrCreate(['cohort_id' => $cohort->id, 'student_id' => $studentId]);
        }

        return $this->ok($cohort->students()->get(['users.id', 'users.name', 'users.email']));
    }

    // remove a student from the cohort
    public function unenroll(Request $request, Cohort $cohort, User $student)
    {
        abort_unless($cohort->isManagedBy($request->user()), 403, 'Forbidden');

        Enrollment::where('cohort_id', $cohort->id)->where('student_id', $student->id)->delete();

        return $this->ok(null, 'Student removed from cohort');
    }

    public function store(StoreCohortRequest $request)
    {
        $cohort = Cohort::create([
            'track_id' => $request->track_id,
            'name' => $request->name,
            'status' => $request->input('status', 'active'),
            'created_by' => $request->user()->id,
        ]);

        $cohort->tas()->sync($request->input('ta_ids', []));

        return $this->ok($cohort->load('tas'), 'Cohort created', 201);
    }

    public function update(UpdateCohortRequest $request, Cohort $cohort)
    {
        $cohort->update($request->validated());

        return $this->ok($cohort, 'Cohort updated');
    }

    public function destroy(Cohort $cohort)
    {
        $cohort->delete();

        return $this->ok(null, 'Cohort deleted');
    }
}
