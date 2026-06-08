<?php

use App\Http\Controllers\Api\V1\EngagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // the BM / track admins book engagements; a person can hold several
    Route::post('/engagements', [EngagementController::class, 'store'])
        ->middleware('role:branch_manager,track_admin');

    // anyone signed in can read a person's access window
    Route::get('/users/{user}/access-window', [EngagementController::class, 'accessWindow']);
});
