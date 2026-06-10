<?php

use App\Http\Controllers\Api\V1\MeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me/attendance', [MeController::class, 'attendance']);
    Route::get('/me/grades', [MeController::class, 'grades']);
    Route::get('/me/progress', [MeController::class, 'progress']);
});
