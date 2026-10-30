<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_is_rate_limited_per_client(): void
    {
        $payload = [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ];

        for ($attempt = 1; $attempt <= 6; $attempt++) {
            $this->post(route('register'), $payload)->assertSessionHasErrors();
        }

        $this->post(route('register'), $payload)->assertStatus(429);
    }
}
