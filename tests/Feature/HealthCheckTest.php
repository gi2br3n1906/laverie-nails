<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_check_reports_database_connectivity(): void
    {
        $this->get('/healthz')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }
}
