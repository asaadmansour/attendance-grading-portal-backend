<?php

use App\Http\Controllers\Api\V1\StudentNoteController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware('role:instructor,track_admin')->group(function(){
        Route::get('/students/{student}/notes', [StudentNoteController::class, 'index']);
        Route::post('/students/{student}/notes', [StudentNoteController::class, 'store']);
        Route::delete('/notes/{note}', [StudentNoteController::class, 'destroy']);
    });
});