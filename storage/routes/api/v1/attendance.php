<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/sessions/{id}/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/sessions/{id}/check-out', [AttendanceController::class, 'checkOut']);
});