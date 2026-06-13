<?php

use App\Http\Controllers\Api\V1\TrackController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // BM and track admins read the track list (e.g. when opening a cohort)
    Route::get('/tracks', [TrackController::class, 'index'])->middleware('role:branch_manager,track_admin');

    // only the BM manages tracks and the admins that run them
    Route::middleware('role:branch_manager')->group(function () {
        Route::post('/tracks', [TrackController::class, 'store']);
        Route::patch('/tracks/{track}', [TrackController::class, 'update']);
        Route::delete('/tracks/{track}', [TrackController::class, 'destroy']);

        Route::post('/tracks/{track}/admins', [TrackController::class, 'attachAdmins']);
        Route::delete('/tracks/{track}/admins/{user}', [TrackController::class, 'detachAdmin']);
    });
});
