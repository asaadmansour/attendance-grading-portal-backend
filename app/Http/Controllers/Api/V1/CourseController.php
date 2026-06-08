<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Cohort;
use App\Models\Course;

class CourseController extends Controller
{
    public function index(Cohort $cohort)
    {
        return $this->ok($cohort->courses()->with('components')->get());
    }

    public function show(Course $course)
    {
        return $this->ok($course->load('components'));
    }

    public function store(StoreCourseRequest $request, Cohort $cohort)
    {
        $course = $cohort->courses()->create([
            'name' => $request->name,
            'total_points' => $request->total_points,
        ]);

        $course->components()->createMany($request->input('components'));

        return $this->ok($course->load('components'), 'Course created', 201);
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        $course->update($request->only('name', 'total_points'));

        // swap the whole component set if a new one was sent
        if ($request->has('components')) {
            $course->components()->delete();
            $course->components()->createMany($request->input('components'));
        }

        return $this->ok($course->load('components'), 'Course updated');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return $this->ok(null, 'Course deleted');
    }
}
