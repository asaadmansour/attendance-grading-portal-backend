<?php

use App\Http\Controllers\Api\V1\StudentTagController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware('role:instructor,track_admin')->group(function(){
        Route::get('/students/{student}/tags', [StudentTagController::class, 'index']);
        Route::post('/students/{student}/tags', [StudentTagController::class, 'store']);
        Route::delete('/student-tags/{id}', [StudentTagController::class, 'destroy']);
    });
});