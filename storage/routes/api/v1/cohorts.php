<?php

use App\Http\Controllers\Api\V1\CohortController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\LabGroupController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // only the BM opens cohorts and runs their lifecycle
    Route::post('/cohorts', [CohortController::class, 'store'])->middleware('role:branch_manager');
    Route::patch('/cohorts/{cohort}', [CohortController::class, 'update'])->middleware('role:branch_manager');
    Route::delete('/cohorts/{cohort}', [CohortController::class, 'destroy'])->middleware('role:branch_manager');

    // everything else is the BM and track admins
    Route::middleware('role:branch_manager,track_admin')->group(function () {
        Route::get('/cohorts', [CohortController::class, 'index']);
        Route::get('/cohorts/{cohort}', [CohortController::class, 'show']);
        Route::get('/cohorts/{cohort}/students', [CohortController::class, 'students']);
        Route::post('/cohorts/{cohort}/students', [CohortController::class, 'enroll']);
        Route::delete('/cohorts/{cohort}/students/{student}', [CohortController::class, 'unenroll']);

        Route::get('/cohorts/{cohort}/courses', [CourseController::class, 'index']);
        Route::post('/cohorts/{cohort}/courses', [CourseController::class, 'store']);
        Route::get('/courses/{course}', [CourseController::class, 'show']);
        Route::patch('/courses/{course}', [CourseController::class, 'update']);
        Route::delete('/courses/{course}', [CourseController::class, 'destroy']);

        Route::get('/cohorts/{cohort}/lab-groups', [LabGroupController::class, 'index']);
        Route::post('/cohorts/{cohort}/lab-groups', [LabGroupController::class, 'store']);
        Route::get('/lab-groups/{labGroup}', [LabGroupController::class, 'show']);
        Route::patch('/lab-groups/{labGroup}', [LabGroupController::class, 'update']);
        Route::delete('/lab-groups/{labGroup}', [LabGroupController::class, 'destroy']);
    });

    // an instructor sees the groups they teach (with rosters); a student sees their own
    Route::get('/my/lab-groups', [LabGroupController::class, 'mine'])->middleware('role:instructor');
    // a single group the instructor teaches, with roster + cohort courses/components (grading board)
    Route::get('/my/lab-groups/{labGroup}', [LabGroupController::class, 'mineShow'])->middleware('role:instructor');
    Route::get('/my/lab-group', [LabGroupController::class, 'myGroup'])->middleware('role:student');
});
