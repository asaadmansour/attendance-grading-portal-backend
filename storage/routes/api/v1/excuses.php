<?php

use App\Http\Controllers\ExcuseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/excuses', [ExcuseController::class, 'store']);
    Route::patch('/excuses/{excuse}/approve', [ExcuseController::class, 'approve']);
    Route::patch('/excuses/{excuse}/reject', [ExcuseController::class, 'reject']);
    Route::patch('/excuses/{excuse}', [ExcuseController::class, 'review']);
    Route::get('/excuses', [ExcuseController::class, 'index']);
});
