<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Cohort;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request, Cohort $cohort)
    {
        abort_unless($cohort->isManagedBy($request->user()), 403, 'Forbidden');

        return $this->ok($cohort->courses()->with('components')->get());
    }

    public function show(Request $request, Course $course)
    {
        abort_unless($course->cohort->isManagedBy($request->user()), 403, 'Forbidden');

        return $this->ok($course->load('components'));
    }

    public function store(StoreCourseRequest $request, Cohort $cohort)
    {
        abort_unless($cohort->isManagedBy($request->user()), 403, 'Forbidden');

        // courses are always out of 100
        $course = $cohort->courses()->create([
            'name' => $request->name,
            'total_points' => 100,
        ]);

        $course->components()->createMany($request->input('components'));

        return $this->ok($course->load('components'), 'Course created', 201);
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        abort_unless($course->cohort->isManagedBy($request->user()), 403, 'Forbidden');

        $course->update($request->only('name'));

        // swap the whole component set if a new one was sent
        if ($request->has('components')) {
            $course->components()->delete();
            $course->components()->createMany($request->input('components'));
        }

        return $this->ok($course->load('components'), 'Course updated');
    }

    public function destroy(Request $request, Course $course)
    {
        abort_unless($course->cohort->isManagedBy($request->user()), 403, 'Forbidden');

        $course->delete();

        return $this->ok(null, 'Course deleted');
    }
}
