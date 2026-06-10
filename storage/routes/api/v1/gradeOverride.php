<?php

use App\Http\Controllers\Api\V1\GradeOverrideController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:track_admin'])->group(function () {
    Route::post('/component-grades/{componentGrade}/override',
        [GradeOverrideController::class, 'override']);

    Route::get('/component-grades/{componentGrade}/overrides',
        [GradeOverrideController::class, 'index']);
});