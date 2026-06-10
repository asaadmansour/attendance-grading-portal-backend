<?php

use App\Http\Controllers\Api\V1\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::post('/assignments/{assignment}/submissions', [SubmissionController::class, 'store']);
});
