<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Simple health check test for the API.
     */
    public function test_ping_route_returns_ok(): void
    {
        $response = $this->getJson('/api/v1/ping');

        $response->assertOk();
    }
}