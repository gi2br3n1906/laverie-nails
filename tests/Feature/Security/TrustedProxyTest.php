<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    use RefreshDatabase;

    public function test_forwarded_https_is_trusted_when_the_tunnel_proxy_is_configured(): void
    {
        config(['trustedproxy.proxies' => '*']);

        $parts = parse_url((string) config('app.url'));
        $authority = $parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders([
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-For' => '203.0.113.10',
            ])
            ->get('/lacak-pesanan')
            ->assertOk()
            ->assertSee('action="https://'.$authority.'/lacak-pesanan"', false);
    }

    public function test_forwarded_https_is_ignored_without_trusted_proxies(): void
    {
        config(['trustedproxy.proxies' => null]);

        $parts = parse_url((string) config('app.url'));
        $authority = $parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders([
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-For' => '203.0.113.10',
            ])
            ->get('/lacak-pesanan')
            ->assertOk()
            ->assertSee('action="http://'.$authority.'/lacak-pesanan"', false);
    }
}
