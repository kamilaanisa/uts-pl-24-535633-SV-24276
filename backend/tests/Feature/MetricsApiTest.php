<?php

namespace Tests\Feature;

use Tests\TestCase;

class MetricsApiTest extends TestCase
{
    public function test_metrics_mencatat_request_dalam_format_prometheus(): void
    {
        $this->getJson('/api/books')->assertStatus(401);

        $this->get('/metrics')
            ->assertOk()
            ->assertSee('# TYPE http_requests_total counter', false)
            ->assertSee('http_requests_total{method="GET",route="api/books",status="401"} 1', false);
    }
}