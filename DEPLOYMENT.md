# Production Deployment: Laverie Nails

This runbook describes the sealed Laravel 12 production stack implemented by `Dockerfile`, `docker-compose.yml`, and `deploy.sh`.

## Architecture

- **Nginx 1.27 Alpine** serves static/Vite assets and proxies `index.php` to PHP-FPM.
- **PHP 8.4 FPM Alpine** runs Laravel with OPcache and the required SQLite, Intl, Mbstring, ZIP, cURL, BCMath, and PCNTL extensions.
- Application source and dependencies are built into immutable images. Production does not bind-mount the repository.
- Nginx and PHP-FPM run with read-only root filesystems. Only explicit named volumes and temporary filesystems are writable.

### Persistent volumes

| Volume | Container path | Purpose |
| --- | --- | --- |
| `sqlite_data` | `/var/www/data` | Persistent `database.sqlite` |
| `public_uploads` | `/var/www/html/storage/app/public` | Persistent product images |
| `runtime_storage` | `/var/www/html/storage` | Logs, sessions, cache, and generated views |
| `bootstrap_cache` | `/var/www/html/bootstrap/cache` | Laravel bootstrap caches |

Never run `docker compose down -v` in production unless permanent deletion of the SQLite database and uploaded catalog media is intended.

## Server prerequisites

1. Linux server with Docker Engine and Docker Compose.
2. The repository checked out on the server.
3. A production `.env` file that is not committed to Git.
4. Inbound traffic routed to `${APP_PORT:-8080}` directly or through the approved Cloudflare Tunnel.

Verify Docker is running:

```bash
docker version
docker compose version
```

## Production environment

Create the environment file:

```bash
cp .env.example .env
```

Set at minimum:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_KEY=base64:GENERATE_A_SECURE_KEY
APP_PORT=8080

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/data/database.sqlite
LOG_CHANNEL=stderr
LOG_LEVEL=warning
```

Generate `APP_KEY` on a trusted machine or with a one-off PHP command. Never reuse a development key and never commit `.env`.

## Deploy

```bash
chmod +x deploy.sh
./deploy.sh
```

The deployment script performs these steps in order:

1. Fast-forwards the checked-out server branch from its configured Git upstream. Set `DEPLOY_PULL_LATEST=false` only when source synchronization is handled externally.
2. Validates Docker, Compose, `.env`, `APP_KEY`, and required static assets.
3. Builds current PHP-FPM and Nginx images, then force-recreates both containers. Set `DEPLOY_NO_CACHE=true` for a one-off completely uncached build.
4. Creates the persistent SQLite file and Laravel runtime directories.
5. Applies `www-data` ownership and group-writable directory/file permissions.
6. Clears stale file-based bootstrap caches without querying a fresh database.
7. Runs `php artisan migrate --force`.
8. Synchronizes the canonical XS/S/M/L records using the idempotent `SizeStandardSeeder`.
9. Clears remaining caches, then runs `php artisan optimize` and `php artisan view:cache`.
10. Verifies that the hero asset SHA-256 checksum is identical in the server checkout, PHP-FPM image, and Nginx image.
11. Downloads the cache-busted hero URL directly from the local Nginx origin and verifies its checksum.
12. Prints final container status.

Production does not bind-mount the repository. Editing or uploading a file directly into the host checkout does not alter a running container until the images are rebuilt and containers are recreated. Public image URLs include a content-derived query version so a changed image bypasses existing browser or CDN entries. Dynamic HTML responses are marked `no-store`; if Cloudflare has an explicit Cache Everything rule that ignores origin headers, purge that rule/cache once and exclude application HTML from edge caching.

To diagnose a deployed hero image, compare these values on the server:

```bash
sha256sum public/images/hero-banner.png
docker compose exec -T php-fpm sha256sum /var/www/html/public/images/hero-banner.png
docker compose exec -T nginx sha256sum /var/www/html/public/images/hero-banner.png
curl -fsS http://127.0.0.1:${APP_PORT:-8080}/ | grep -o 'images/hero-banner.png?v=[a-f0-9]*'
```

All three checksums must match. The final command must show a `?v=` value. If the local origin is correct but the public domain is stale, the remaining cache is outside Docker (usually a Cloudflare Cache Everything rule); purge it once and preserve the HTML bypass rule.

## Health and operations

Check container health:

```bash
docker compose ps
curl --fail http://127.0.0.1:${APP_PORT:-8080}/healthz
```

Inspect logs:

```bash
docker compose logs --tail=100 php-fpm nginx
```

Create an SQLite backup without stopping the application:

```bash
docker compose exec -T --user www-data php-fpm \
  php -r '$db=new SQLite3("/var/www/data/database.sqlite"); $db->exec("VACUUM INTO \"/var/www/data/database-backup.sqlite\"");'
```

Copy the backup from the named volume using an approved server backup process. Uploaded catalog media in `public_uploads` must be backed up separately.

## Cloudflare Tunnel

When Cloudflare Tunnel is used, map the public hostname to:

```text
http://127.0.0.1:8080
```

TLS terminates at Cloudflare. Do not expose the Docker port publicly if the server firewall and tunnel architecture are intended to keep origin traffic private.