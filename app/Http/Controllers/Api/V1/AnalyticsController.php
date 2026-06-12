<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Services\AnalyticsService;

class AnalyticsController extends Controller
{
    public function atRisk(string $cohort, AnalyticsService $analytics)
    {
        $cohort = Cohort::findOrFail($cohort);

        return $this->ok($analytics->atRiskStudents($cohort->id));
    }

    public function gradeDistribution(string $cohort, AnalyticsService $analytics)
    {
        $cohort = Cohort::findOrFail($cohort);

        return $this->ok($analytics->gradeDistribution($cohort->id));
    }

    public function submissionStatus(string $cohort, AnalyticsService $analytics)
    {
        $cohort = Cohort::findOrFail($cohort);

        return $this->ok($analytics->submissionStatus($cohort->id));
    }
}
