<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Track;

class TrackController extends Controller
{
    // tracks the BM picks from when opening a cohort
    public function index()
    {
        return $this->ok(Track::all(['id', 'name']));
    }
}
