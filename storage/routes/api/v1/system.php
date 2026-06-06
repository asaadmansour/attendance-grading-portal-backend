<?php

use App\Http\Controllers\Api\V1\SystemController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', [SystemController::class, 'ping']);