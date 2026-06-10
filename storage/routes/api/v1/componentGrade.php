<?php

use App\Http\Controllers\Api\V1\ComponentGradeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    // Read a student's grades — instructors (own group) + TA
    Route::middleware('role:instructor,track_admin')->group(function () {
        Route::get('/students/{student}/grades', [ComponentGradeController::class, 'index']);
        Route::post('/component-grades', [ComponentGradeController::class, 'store']);
        Route::patch('/component-grades/{id}', [ComponentGradeController::class, 'update']);
    });

});