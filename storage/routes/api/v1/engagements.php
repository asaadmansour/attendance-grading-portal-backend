<?php

use App\Http\Controllers\Api\V1\EngagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // the BM / track admins manage engagements; a person can hold several
    Route::middleware('role:branch_manager,track_admin')->group(function () {
        Route::get('/engagements', [EngagementController::class, 'index']);
        Route::post('/engagements', [EngagementController::class, 'store']);
        Route::get('/engagements/{engagement}', [EngagementController::class, 'show']);
        Route::patch('/engagements/{engagement}', [EngagementController::class, 'update']);
        Route::delete('/engagements/{engagement}', [EngagementController::class, 'destroy']);
    });

    // anyone signed in can read a person's access window
    Route::get('/users/{user}/access-window', [EngagementController::class, 'accessWindow']);
});
