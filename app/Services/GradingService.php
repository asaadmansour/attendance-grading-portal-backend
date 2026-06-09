<?php

namespace App\Services;

class GradingService
{
    public function normalize(float $rawScore, float $rawMax, float $weight): float
    {
        if($rawMax == 0)return 0;
        return ($rawScore / $rawMax)* $weight;
    }
}