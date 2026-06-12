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

        return response()->json([
            'data' => $analytics->atRiskStudents($cohort->id),
        ], 200);
    }

    public function gradeDistribution(string $cohort, AnalyticsService $analytics)
    {
        $cohort = Cohort::findOrFail($cohort);

        return response()->json([
            'data' => $analytics->gradeDistribution($cohort->id),
        ], 200);
    }

    public function submissionStatus(string $cohort, AnalyticsService $analytics)
    {
        $cohort = Cohort::findOrFail($cohort);

        return response()->json([
            'data' => $analytics->submissionStatus($cohort->id),
        ], 200);
    }
}
