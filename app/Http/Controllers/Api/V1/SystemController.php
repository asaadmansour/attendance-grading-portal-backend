<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

class SystemController extends Controller
{
    public function ping()
    {
        return $this->ok(['pong' => true], 'API is alive');
    }
}