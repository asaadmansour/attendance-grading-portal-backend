<?php

use Illuminate\Support\Facades\Route;

foreach (glob(base_path('routes/api/v1/*.php')) as $routeFile) {
    Route::prefix('v1')->group($routeFile);
}