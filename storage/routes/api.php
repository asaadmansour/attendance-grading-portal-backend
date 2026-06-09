<?php

use Illuminate\Support\Facades\Route;

foreach (glob(base_path('storage/routes/api/v1/*.php')) as $routeFile) {
    Route::prefix('v1')->middleware('throttle:api')->group($routeFile);
}