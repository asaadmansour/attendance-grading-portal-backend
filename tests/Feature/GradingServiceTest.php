<?php

namespace Tests\Feature;

use App\Services\GradingService;
use Carbon\Carbon;
use Tests\TestCase;

class GradingServiceTest extends TestCase
{
    public function test_late_penalty_matches_spec(): void
    {
        $service = new GradingService();
        $due = Carbon::parse('2026-01-01 12:00:00');

        // on time → full score
        $this->assertSame(10.0, $service->latePenalty(10, $due, $due->copy()));

        // 1 day late → 7.5 (25% per day)
        $this->assertSame(7.5, $service->latePenalty(10, $due, $due->copy()->addDay()));

        // 2 days late → 5.0 (spec example)
        $this->assertSame(5.0, $service->latePenalty(10, $due, $due->copy()->addDays(2)));

        // 4 days late → 0.0 (cap at 4 days = 100% penalty)
        $this->assertSame(0.0, $service->latePenalty(10, $due, $due->copy()->addDays(4)));

        // 5 days late → still 0.0 (capped, never negative)
        $this->assertSame(0.0, $service->latePenalty(10, $due, $due->copy()->addDays(5)));
    }
}
