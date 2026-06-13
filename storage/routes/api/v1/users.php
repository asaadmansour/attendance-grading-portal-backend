<?php

use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [UserController::class, 'me']);

    // managers read and manage the accounts under them; the UserPolicy decides which
    // targets each caller may touch (BM → track admins, TA → instructors/students)
    Route::middleware('role:branch_manager,track_admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::patch('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });

    Route::post('/me/avatar', [UserController::class, 'updateAvatar']);
});