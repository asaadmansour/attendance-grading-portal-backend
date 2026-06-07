<?php

use App\Http\Controllers\Api\V1\TagController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/tags' , [TagController::class, 'index']);
    Route::post('/tags' , [TagController::class, 'store']);
    Route::get('/tags/{id}' , [TagController::class, 'show']);
    Route::patch('/tags/{id}' , [TagController::class, 'update']);
    Route::delete('/tags/{id}' , [TagController::class, 'destroy']);
});