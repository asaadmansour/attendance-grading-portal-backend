<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/sessions/{session}/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/sessions/{session}/check-out', [AttendanceController::class, 'checkOut']);
    Route::post('/sessions/{session}/reconcile', [AttendanceController::class, 'reconcile']);
});