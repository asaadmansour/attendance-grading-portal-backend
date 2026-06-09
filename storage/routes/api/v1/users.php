<?php

use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [UserController::class, 'me']);
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('role:branch_manager,track_admin');
});