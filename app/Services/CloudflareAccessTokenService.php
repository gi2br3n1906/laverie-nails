<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use JsonException;

class CloudflareAccessTokenService
{
    public function isConfigured(): bool
    {
        $teamDomain = rtrim((string) config('services.cloudflare_access.team_domain'), '/');
        $host = parse_url($teamDomain, PHP_URL_HOST);

        return parse_url($teamDomain, PHP_URL_SCHEME) === 'https'
            && is_string($host)
            && preg_match('/^[a-z0-9.-]+\.cloudflareaccess\.com$/i', $host) === 1
            && ! filled(parse_url($teamDomain, PHP_URL_USER))
            && ! filled(parse_url($teamDomain, PHP_URL_PORT))
            && ! filled(parse_url($teamDomain, PHP_URL_PATH))
            && ! filled(parse_url($teamDomain, PHP_URL_QUERY))
            && filled(config('services.cloudflare_access.audience'));
    }

    public function email(string $token): ?string
    {
        if (! $this->isConfigured() || strlen($token) > 16384) {
            return null;
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        try {
            $header = json_decode($this->decode($parts[0]), true, flags: JSON_THROW_ON_ERROR);
            $claims = json_decode($this->decode($parts[1]), true, flags: JSON_THROW_ON_ERROR);
            $signature = $this->decode($parts[2]);
        } catch (JsonException|\InvalidArgumentException) {
            return null;
        }

        if (! is_array($header) || ! is_array($claims)
            || ($header['alg'] ?? null) !== 'RS256'
            || ! is_string($header['kid'] ?? null)
            || ! $this->hasValidClaims($claims)) {
            return null;
        }

        $publicKey = $this->publicKey($header['kid']);

        if (! $publicKey) {
            return null;
        }

        try {
            $verified = openssl_verify($parts[0].'.'.$parts[1], $signature, $publicKey, OPENSSL_ALGO_SHA256);
        } catch (\Throwable) {
            return null;
        }

        if ($verified !== 1) {
            return null;
        }

        $email = strtolower(trim((string) ($claims['email'] ?? '')));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /** @param array<string, mixed> $claims */
    private function hasValidClaims(array $claims): bool
    {
        $teamDomain = rtrim((string) config('services.cloudflare_access.team_domain'), '/');
        $audience = (string) config('services.cloudflare_access.audience');
        $tokenAudiences = $claims['aud'] ?? null;
        $tokenAudiences = is_array($tokenAudiences) ? $tokenAudiences : [$tokenAudiences];
        $now = now()->timestamp;

        return ($claims['iss'] ?? null) === $teamDomain
            && in_array($audience, $tokenAudiences, true)
            && is_numeric($claims['exp'] ?? null)
            && (int) $claims['exp'] > $now
            && (! isset($claims['nbf']) || (is_numeric($claims['nbf']) && (int) $claims['nbf'] <= $now))
            && is_numeric($claims['iat'] ?? null)
            && (int) $claims['iat'] <= $now + 60;
    }

    private function publicKey(string $keyId): ?string
    {
        $cacheKey = 'cloudflare_access.public_certs.'.hash('sha256', (string) config('services.cloudflare_access.team_domain'));
        $certificates = Cache::remember($cacheKey, now()->addMinutes(10), fn (): array => $this->fetchCertificates());

        if (! isset($certificates[$keyId])) {
            Cache::forget($cacheKey);
            $certificates = Cache::remember($cacheKey, now()->addMinutes(10), fn (): array => $this->fetchCertificates());
        }

        $key = $certificates[$keyId] ?? null;

        return is_string($key)
            && (str_contains($key, 'BEGIN PUBLIC KEY') || str_contains($key, 'BEGIN CERTIFICATE'))
            ? $key
            : null;
    }

    /** @return array<string, string> */
    private function fetchCertificates(): array
    {
        $teamDomain = rtrim((string) config('services.cloudflare_access.team_domain'), '/');

        try {
            $response = Http::acceptJson()->timeout(5)->get($teamDomain.'/cdn-cgi/access/certs');
        } catch (\Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $rows = $response->json('public_certs');

        if (! is_array($rows)) {
            return [];
        }

        $certificates = [];

        foreach ($rows as $row) {
            if (is_array($row) && is_string($row['kid'] ?? null) && is_string($row['cert'] ?? null)) {
                $certificates[$row['kid']] = $row['cert'];
            }
        }

        return $certificates;
    }

    private function decode(string $value): string
    {
        if ($value === '' || preg_match('/^[A-Za-z0-9_-]+$/', $value) !== 1) {
            throw new \InvalidArgumentException('Invalid base64url value.');
        }

        $decoded = base64_decode(strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4), true);

        if ($decoded === false) {
            throw new \InvalidArgumentException('Invalid base64url value.');
        }

        return $decoded;
    }
}
