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

1. Validates Docker, Compose, `.env`, and `APP_KEY`.
2. Runs `docker-compose up -d --build --remove-orphans` (or the Compose plugin fallback).
3. Creates the persistent SQLite file and Laravel runtime directories.
4. Applies `www-data` ownership and group-writable directory/file permissions.
5. Clears stale file-based bootstrap caches without querying a fresh database.
6. Runs `php artisan migrate --force`.
7. Synchronizes the canonical XS/S/M/L records using the idempotent `SizeStandardSeeder`.
8. Clears remaining caches, then runs `php artisan optimize` and `php artisan view:cache`.
9. Prints final container status.

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