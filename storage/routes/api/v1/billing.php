<?php

use App\Http\Controllers\Api\V1\BillingController;
use Illuminate\Support\Facades\Route;

// billing is a branch-level finance operation, so the branch manager owns it
Route::middleware(['auth:sanctum', 'role:branch_manager'])->group(function () {
    Route::post('/billing/run', [BillingController::class, 'run']);
    Route::get('/billing/rollup', [BillingController::class, 'rollup']);
    Route::post('/billing/forward', [BillingController::class, 'forward']);
});
