# Production Deployment: Laverie Nails

This runbook describes the sealed Laravel 12 production stack implemented by `Dockerfile`, `docker-compose.yml`, and `deploy.sh`.

If Docker cannot start containers because the VPS is an unprivileged LXC container (`runc ... net.ipv4.ip_unprivileged_port_start ... permission denied`), use the host-native path in [`docs/HOST_DEPLOYMENT.md`](docs/HOST_DEPLOYMENT.md) instead. Do not run both runtimes against the same SQLite database.

## Architecture

- **Nginx 1.27 Alpine** serves static/Vite assets and proxies `index.php` to PHP-FPM.
- **PHP 8.4 FPM Alpine** runs Laravel with OPcache and the required SQLite, Intl, Mbstring, ZIP, cURL, BCMath, and PCNTL extensions.
- **Queue worker** delivers queued transactional email and other jobs.
- **Scheduler** runs `schedule:work`, including the Midtrans pending-payment reconciliation.
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
4. Inbound traffic routed through Cloudflare Tunnel to the loopback-bound `${APP_PORT:-8080}`. The port is not published on a public interface.

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
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/data/database.sqlite
DB_BUSY_TIMEOUT=5000
DB_JOURNAL_MODE=WAL
LOG_CHANNEL=stderr
LOG_LEVEL=warning

# Cloudflare Tunnel terminates HTTPS before the origin.
TRUSTED_PROXIES=*
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Transactional email (Brevo SMTP relay). Keep MAIL_MAILER=log only for local work.
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=<SMTP login shown on Brevo's SMTP settings page>
MAIL_PASSWORD=<Brevo SMTP key starting with xsmtpsib->
MAIL_FROM_ADDRESS=admin@laverie.web.id
MAIL_FROM_NAME="Laverie Nails"
QUEUE_CONNECTION=database
```

`TRUSTED_PROXIES=*` is only safe because the compose port is bound to `127.0.0.1`. If the origin is ever exposed on a public interface, replace `*` with the proxy IPs/CIDRs.

`MAIL_USERNAME` is the **Login** value from Brevo's SMTP settings, which for newer accounts is an `@smtp-brevo.com` address rather than the account email. Using the account email returns `535 Authentication failed`. If the key has an IP allow-list, sending from an unlisted IP returns `525 Unauthorized IP address`.

`MAIL_SCHEME` must be `null` (port 587, STARTTLS auto-negotiated) or `smtps` (port 465). Laravel passes this value straight to Symfony as the DSN scheme, so `tls` raises `UnsupportedSchemeException`.

Generate `APP_KEY` on a trusted machine or with a one-off PHP command. Never reuse a development key and never commit `.env`.

## Deploy

See [`docs/OPERATIONS.md`](docs/OPERATIONS.md) for the operational runbook, backup/restore drill, monitoring, scheduler, and incident checklist.

```bash
chmod +x deploy.sh
./deploy.sh
```

The deployment script performs these steps in order:

1. Fast-forwards the checked-out server branch from its configured Git upstream. Set `DEPLOY_PULL_LATEST=false` only when source synchronization is handled externally.
2. Validates Docker, Compose, `.env`, `APP_KEY`, and required static assets.
3. Builds current PHP-FPM and Nginx images, then force-recreates PHP-FPM, Nginx, the queue worker, and the scheduler. Set `DEPLOY_NO_CACHE=true` for a one-off completely uncached build.
4. Creates the persistent SQLite file and Laravel runtime directories.
5. Applies `www-data` ownership and group-writable directory/file permissions.
6. Clears stale file-based bootstrap caches without querying a fresh database.
7. Backs up the SQLite database with `VACUUM INTO` before any schema change.
8. Runs `php artisan migrate --force`.
9. Synchronizes the canonical XS/S/M/L records using the idempotent `SizeStandardSeeder`.
10. Clears remaining caches, then runs `php artisan optimize` and `php artisan view:cache`.
11. Restarts the queue worker and checks `/healthz`, `/readyz` (database readiness), and `/up` on the local origin.
12. Verifies that the hero asset SHA-256 checksum is identical in the server checkout, PHP-FPM image, and Nginx image.
13. Downloads the cache-busted hero URL directly from the local Nginx origin and verifies its checksum.
14. Prints final container status.

Production does not bind-mount the repository. Editing or uploading a file directly into the host checkout does not alter a running container until the images are rebuilt and containers are recreated. Public image URLs include a content-derived query version so a changed image bypasses existing browser or CDN entries. Dynamic HTML responses are marked `no-store`; if Cloudflare has an explicit Cache Everything rule that ignores origin headers, purge that rule/cache once and exclude application HTML from edge caching.

To diagnose a deployed hero image, compare these values on the server:

```bash
sha256sum public/images/hero-banner.png
docker compose exec -T php-fpm sha256sum /var/www/html/public/images/hero-banner.png
docker compose exec -T nginx sha256sum /var/www/html/public/images/hero-banner.png
curl -fsS http://127.0.0.1:${APP_PORT:-8080}/ | grep -o 'images/hero-banner.png?v=[a-f0-9]*'
```

All three checksums must match. The final command must show a `?v=` value. If the local origin is correct but the public domain is stale, the remaining cache is outside Docker (usually a Cloudflare Cache Everything rule); purge it once and preserve the HTML bypass rule.

## Transactional email

Order emails (received, paid, shipped) are queued into the `jobs` table and delivered by the `worker` service. Without that worker running, nothing is sent, even with valid SMTP credentials.

The sender domain must stay authenticated in Brevo: the Brevo code TXT record, the `brevo1._domainkey` and `brevo2._domainkey` CNAMEs, and a DMARC record. Switching `MAIL_FROM_ADDRESS` to an unauthenticated domain or a free mailbox (Gmail, Yahoo) sends mail to spam or gets it rejected.

Inspect the worker and the queue:

```bash
docker compose ps worker
docker compose logs --tail=50 worker
docker compose exec -T php-fpm php artisan queue:failed
```

Retry failed jobs after fixing credentials:

```bash
docker compose exec -T php-fpm php artisan queue:retry all
```

### Email account ownership

The Brevo account is the store's sending identity, not a developer asset. The free plan allows a single user, so access cannot be shared.

- Send from `admin@laverie.web.id`; never use a personal mailbox as the From address.
- At handover, change the login email under **Settings → Profile**, then rotate the SMTP key and update `MAIL_PASSWORD` in the server `.env`.
- The DNS records (Brevo code TXT, `brevo1._domainkey`, `brevo2._domainkey`, DMARC) stay unchanged when only the login email changes.
- If the store opens its own Brevo account instead, the domain must be re-authenticated: the Brevo code and both DKIM CNAME targets are account-specific, so they must be replaced, and `MAIL_USERNAME`/`MAIL_PASSWORD` change with them.

## Health and operations

Check container health:

```bash
docker compose ps
curl --fail http://127.0.0.1:${APP_PORT:-8080}/healthz
curl --fail http://127.0.0.1:${APP_PORT:-8080}/readyz
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

The compose stack publishes Nginx on `127.0.0.1:${APP_PORT}` only. Cloudflare Tunnel is the only public entry point; do not open port 8080 in the VPS firewall.

This server is an LXC container on Proxmox. Docker bridge DNS/routing and UDP/QUIC are unreliable there, so the tunnel **must** use host networking and HTTP/2:

- `network_mode: host` for cloudflared (or `--net=host` with `docker run`); never attach it to the `laverie` bridge.
- `--protocol http2`; do not let cloudflared fall back to QUIC/UDP.
- Point the tunnel ingress at `http://127.0.0.1:${APP_PORT}` (default `http://127.0.0.1:8080`). Do not use `http://nginx:80`; that requires the bridge network and will fail or route unpredictably in this LXC.

### Tunnel is managed outside this stack

This repository does not create, authenticate, or run the tunnel. The tunnel token belongs to your Cloudflare/cloudflared setup, not to the application `.env` or this compose file. Register the tunnel and public hostname in the Cloudflare dashboard, and use `http://127.0.0.1:${APP_PORT}` (default `http://127.0.0.1:8080`) as the service URL.

If cloudflared runs as a container on this LXC host, keep it in the host network namespace:

```bash
docker run -d --name cloudflared-tunnel --restart unless-stopped --net=host \
  cloudflare/cloudflared:latest tunnel --no-autoupdate --protocol http2 run --token <TOKEN_FROM_CLOUDFLARE>
```

As a systemd service on the host, the command must still force HTTP/2:

```bash
cloudflared tunnel --no-autoupdate --protocol http2 run --token "$TUNNEL_TOKEN"
```

Do not add cloudflared to the `laverie` bridge network, and do not point its ingress at `http://nginx:80`; that route depends on Docker bridge DNS and is unreliable in this LXC. The application only needs to publish Nginx on `127.0.0.1:${APP_PORT}`.

### Required Cloudflare settings

1. **SSL/TLS → Always Use HTTPS** on. TLS terminates at Cloudflare; the origin speaks HTTP inside the tunnel.
2. **WAF / Bot Fight Mode exception for the payment webhook.** Add a rule that skips WAF, Bot Fight, and managed challenges for `POST` on `/api/payments/midtrans/notification`. Otherwise Midtrans notifications can be challenged and orders will stay `pending`. The endpoint still verifies the Midtrans signature. Set the notification URL in Midtrans to `https://<your-domain>/api/payments/midtrans/notification`; the route is registered under `/api`.
3. **Cloudflare Access for `/admin*`.** Tunnel alone does not authenticate anyone. Protect the admin area with an Access policy (email OTP or service token). Do not put Access in front of the Midtrans webhook.
4. **Cache rules.** Bypass cache for `/checkout*`, `/admin*`, `/payments*`, `/lacak-pesanan*`, `/keranjang*`, and all HTML. Cache only `/build/*`, `/storage/*`, and `/images/*`.
5. **Rate limiting (optional).** Add edge rules for `/login`, `/register`, and `/lacak-pesanan` if the store is targeted; Laravel also throttles these, using the real visitor IP after `TRUSTED_PROXIES` is set.

### Verify the origin is private

```bash
ss -ltnp | grep 8080          # must show 127.0.0.1:8080, not 0.0.0.0:8080
docker compose ps              # php-fpm, nginx, worker, scheduler
curl -I http://127.0.0.1:8080/healthz
curl -I http://127.0.0.1:8080/readyz
curl -I https://your-domain.example/
```

If `ss` shows `0.0.0.0:8080`, fix `docker-compose.yml` before exposing the tunnel. cloudflared is intentionally not part of `docker compose ps`; run and verify it separately as described above.

### Backups off the server

`deploy.sh` writes a pre-migration SQLite snapshot into the `sqlite_data` volume and verifies it with `PRAGMA integrity_check`. For daily off-site backups (database plus catalog uploads, checksums, and optional rclone upload), use the host script documented in [`docs/OPERATIONS.md`](docs/OPERATIONS.md#backups):

```bash
BACKUP_RCLONE_REMOTE=b2:laverie-backups ./scripts/backup-data.sh
```

Uploaded catalog images live in the `public_uploads` volume and are included in that archive.

### Single-node constraint

SQLite with WAL is fine for one store and one worker, but it is a single-writer database. Keep exactly one PHP-FPM/Nginx deployment and one worker; do not scale replicas horizontally. If traffic or admin concurrency grows, migrate to PostgreSQL/MySQL before adding replicas.

## Checkout safety model

- Payment amounts are recalculated server-side from locked product rows; browser-supplied prices are ignored. Checkout charges the product price plus shipping based on the destination province: Rp15.000 for Java and Rp20.000 outside Java by default. Admins can change both rates under Admin → Ongkir; each order snapshots the rate and region used at checkout. The admin records the courier and tracking number manually after dispatch.
- Province, regency, district, and village are selected from a server-side proxy of `wilayah.id` (cached for 30 days); the street-level detail stays in one free-text field. The selected region is saved to the customer account for repeat purchases.
- The region proxy accepts only well-formed hierarchical codes; shipping always uses the validated `province_code`, never a client-supplied amount.
- Coupons are revalidated at checkout, including expiry and minimum spend.
- Stock is checked under a transaction and decremented with a conditional `stock >= quantity` update, so SQLite cannot oversell under concurrent checkouts.
- A per-user cache lock serialises duplicate checkout submissions; `POST /checkout` is also throttled.
- Midtrans notifications require a valid SHA-512 signature; stale `pending` notifications cannot regress a paid order.
- Paid notifications arriving after stock was released are logged at `critical` and left unpaid for manual refund/reconciliation. Refunds and chargebacks are not automated.
- Duplicate paid notifications do not resend the paid email.
- The payment page is visible only to the order owner or through a signed email link, and guest tracking requires order ID plus matching email.