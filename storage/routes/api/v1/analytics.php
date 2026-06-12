<?php

use App\Http\Controllers\Api\V1\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    
    Route::middleware('role:instructor,track_admin')->group(function () {
        Route::get('/cohorts/{cohort}/at-risk', [AnalyticsController::class, 'atRisk']);
        Route::get('/cohorts/{cohort}/grade-distribution', [AnalyticsController::class, 'gradeDistribution']);
        Route::get('/cohorts/{cohort}/submission-status', [AnalyticsController::class, 'submissionStatus']);
    });
});
