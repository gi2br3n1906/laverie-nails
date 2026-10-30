<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_tracking_lookups_are_rate_limited_per_client(): void
    {
        $payload = [
            'order_id' => 'ORD-20260101-01JABCDEFGHJKMNPQRSTVWXYZ0',
            'email' => 'nobody@example.test',
        ];

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->post(route('orders.track.store'), $payload)->assertOk();
        }

        $this->post(route('orders.track.store'), $payload)->assertStatus(429);
    }
}
