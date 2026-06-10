<?php

use App\Http\Controllers\Api\V1\StudentGradesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:instructor,track_admin,student'])->group(function () {
    Route::get('/students/{student}/grand-total',
        [StudentGradesController::class, 'grandTotal']);
});