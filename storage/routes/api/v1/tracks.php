<?php

use App\Http\Controllers\Api\V1\TrackController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:branch_manager,track_admin'])->group(function () {
    Route::get('/tracks', [TrackController::class, 'index']);
});
