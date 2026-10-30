<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Comma-separated proxy IPs, or "*" when the origin is reachable only
    | through Cloudflare Tunnel / a load balancer. Laravel needs this to read
    | the visitor IP and HTTPS scheme from X-Forwarded-* headers, otherwise
    | rate limiting sees one IP and secure-cookie / signed-URL checks fail.
    |
    | Never use "*" when the container port is exposed directly to the internet.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
