<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register'])
        ->middleware('role:branch_manager,track_admin');
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});