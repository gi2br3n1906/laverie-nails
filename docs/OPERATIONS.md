# Operations Runbook: Laverie Nails

This runbook covers the production stack behind Cloudflare Tunnel: Docker Compose,
SQLite, the queue worker, the scheduler, backups, monitoring, and incident
response. Deployment details live in [`DEPLOYMENT.md`](../DEPLOYMENT.md).

## Runtime topology

| Service | Role |
| --- | --- |
| `php-fpm` | Laravel application, migrations, Artisan commands |
| `nginx` | Public origin, bound to `127.0.0.1:${APP_PORT}` only |
| `worker` | Queues transactional email and other jobs |
| `scheduler` | Runs `schedule:work` for payment reconciliation |
| `cloudflared` | Runs outside this Compose stack; the only public entry point |
| SQLite | Single-writer database in the `sqlite_data` volume |
| `public_uploads` | Product images in the `public_uploads` volume |

The stack is intentionally single-node. Do not run multiple PHP-FPM/worker
replicas against one SQLite database.

## First-time setup after checkout

```bash
cp .env.production.example .env
# Fill every blank value, then build the image and generate the application key:
docker compose build php-fpm
docker compose run --rm --no-deps php-fpm php artisan key:generate --show
# Put the printed base64:... value in APP_KEY.
```

Create the first admin account after the stack is up:

```bash
docker compose exec php-fpm php artisan laverie:create-admin owner@example.com
```

The command asks for a password interactively. Do not pass a password through
shell history on a shared server. Then sign in at `/login` and open `/admin`.

Run the standard deploy:

```bash
./deploy.sh
```

`deploy.sh` validates the production environment, creates a pre-migration SQLite
backup, migrates, rebuilds caches, restarts the queue worker, and checks
`/healthz`, `/readyz`, and `/up` on the local origin.

### Required Cloudflare settings

These are dashboard changes, not repository changes. Follow the detailed list in
[`DEPLOYMENT.md`](../DEPLOYMENT.md#required-cloudflare-settings):

1. SSL/TLS → **Always Use HTTPS**.
2. WAF/Bot Fight exception for `POST /api/payments/midtrans/notification`.
3. Cloudflare Access in front of `/admin*` (email OTP or service token).
4. Bypass cache for `/checkout*`, `/admin*`, `/payments*`, `/lacak-pesanan*`,
   `/keranjang*`, and HTML; cache only `/build/*`, `/storage/*`, and `/images/*`.

Do not put Cloudflare Access in front of the Midtrans webhook.

## Go-live minimum

Do not announce the store until every item is checked:

- [ ] `laverie.web.id` resolves and the Cloudflare Tunnel routes to `127.0.0.1:${APP_PORT}`.
- [ ] Production `.env` is filled, `APP_KEY` is set, and `./deploy.sh` completes without errors.
- [ ] `php artisan laverie:create-admin` has created the owner account; the password is in a password manager.
- [ ] The daily backup cron is installed with an rclone destination, and one restore drill has succeeded.
- [ ] An external uptime monitor alerts on `/healthz` and `/readyz` to a channel someone actually reads.
- [ ] Cloudflare Access protects `/admin*`; the Midtrans webhook path bypasses Access and cache.
- [ ] A sandbox checkout reaches `paid` through both the webhook and the reconciliation command.
- [ ] `/privacy` is live; business identity and Terms of Service are ready before requesting Midtrans production.
- [ ] `LAVERIE_WHATSAPP_NUMBER` contains the real number, and demo products/reviews are reviewed for the presentation.

## Routine schedule

| When | Task |
| --- | --- |
| Every 15 minutes | Payment reconciliation (scheduler) and `scripts/ops-check.sh` |
| Daily | Off-site backup of SQLite and catalog uploads |
| Weekly | Review failed jobs, disk usage, error logs, and Cloudflare security events |
| Monthly | Restore drill on a non-production copy; rotate credentials that are due |
| After every deploy | Check the deploy output, `/readyz`, queue worker, and one sandbox order |

### Backups

`docker/backup-sqlite.sh` creates an integrity-checked SQLite snapshot inside the
`sqlite_data` volume. The host script adds catalog uploads and an optional
off-site copy:

```bash
# Local backup only (useful for a presentation environment):
./scripts/backup-data.sh

# Production: upload to an rclone remote as well.
BACKUP_RCLONE_REMOTE=b2:laverie-backups ./scripts/backup-data.sh
```

The script writes `backups/<UTC timestamp>/database.sqlite`,
`public-uploads.tar.gz`, and `manifest.txt` containing SHA-256 checksums and the
source Git revision. It keeps 14 days locally by default and prunes the rclone
destination after 30 days by default.

Install `rclone` on the VPS and configure the remote once:

```bash
rclone config
```

Use a remote with object versioning or lifecycle protection where available. A
copy on the same VPS is not a disaster-recovery backup.

Example crontab for a deployment at `/opt/laverie`:

```cron
MAILTO=laverienails@gmail.com
15 2 * * * cd /opt/laverie && BACKUP_RCLONE_REMOTE=b2:laverie-backups ./scripts/backup-data.sh >> storage/logs/backup.log 2>&1
*/15 * * * * cd /opt/laverie && ./scripts/ops-check.sh >> storage/logs/ops-check.log 2>&1
```

`storage/logs` is inside a container volume; use a host path such as
`/var/log/laverie` for cron output if you prefer logs on the host. Ensure the
cron user can run Docker and can write the log directory.

### Restore drill

Restoring SQLite must be done while the application is stopped. Never restore a
live database under a running worker.

```bash
# 1. Pick the backup directory and stop application writers.
docker compose stop php-fpm nginx worker scheduler

# 2. Restore the database into the named volume.
docker compose run --rm --no-deps \
  -v "$PWD/backups/20261030T020000Z/database.sqlite:/restore/database.sqlite:ro" \
  php-fpm sh -lc 'cp /restore/database.sqlite /var/www/data/database.sqlite && chown www-data:www-data /var/www/data/database.sqlite'

# 3. Restore catalog uploads only if they were lost.
docker compose run --rm --no-deps \
  -v "$PWD/backups/20261030T020000Z/public-uploads.tar.gz:/restore/uploads.tar.gz:ro" \
  php-fpm sh -lc 'rm -rf /var/www/html/storage/app/public/* && tar -xzf /restore/uploads.tar.gz -C /var/www/html/storage/app/public && chown -R www-data:www-data /var/www/html/storage/app/public'

# 4. Start and verify.
docker compose up -d
docker compose exec php-fpm php artisan migrate:status
curl --fail http://127.0.0.1:${APP_PORT:-8080}/readyz
```

Run this drill on a staging copy before relying on it during an incident. Record
the date, backup used, and result in the handover log.

### Monitoring and alerts

`scripts/ops-check.sh` checks:

- public `/`, `/healthz`, and `/readyz`;
- `php-fpm`, `nginx`, `worker`, and `scheduler` containers;
- the `failed_jobs` table;
- disk usage (fails above 85% by default);
- the age of the newest local backup (fails after 48 hours by default).

Use an external uptime service in addition to this script. A cron job can only
report failures if the VPS itself is still running. Point the monitor at:

```text
https://laverie.web.id/
https://laverie.web.id/healthz
https://laverie.web.id/readyz
```

Send alerts to `laverienails@gmail.com` and, if available, a second channel
(Slack/WhatsApp). A monitor that only checks the homepage will not catch a dead
database, queue, or worker.

### Queue and email

```bash
docker compose logs --tail=100 worker
docker compose exec php-fpm php artisan queue:failed
docker compose exec php-fpm php artisan queue:retry all
```

The worker runs with `--tries=3` and restarts hourly. A failed job stays in
`failed_jobs` until retried or deleted. If order email stops, check the worker
first, then Brevo SMTP credentials, then `storage/logs/laravel.log`.

The admin new-order recipient is `LAVERIE_ADMIN_EMAIL`. Keep it set in the VPS
`.env`; it is not the same thing as the SMTP login.

### Payments

The scheduler runs:

```bash
docker compose exec php-fpm php artisan orders:reconcile-payments --minutes=30 --limit=100
```

This queries Midtrans for pending orders older than 30 minutes and updates
`payment_status` only after validating the order ID and gross amount. It exists
because webhooks can be delayed or blocked. It is a fallback, not a replacement
for the webhook.

Check the scheduler is actually running:

```bash
docker compose ps scheduler
docker compose logs --tail=50 scheduler
docker compose exec php-fpm php artisan schedule:list
```

If the scheduler container is down, orders can remain pending even though
Midtrans shows settlement. Start it with `docker compose up -d scheduler`.

### Logs and disk

```bash
docker compose ps
docker compose logs --tail=200 php-fpm
docker compose logs --tail=200 nginx
docker compose logs --tail=200 worker
docker compose logs --tail=200 scheduler
```

Compose now limits each container to five 10 MB JSON log files. Application logs
go to stderr and are visible through Docker. If log volume grows unexpectedly,
check for repeated Midtrans status failures, mail errors, or bot traffic.

```bash
df -h
docker system df
```

Do not run `docker system prune --volumes` on the production host; it can delete
the SQLite database volume.

## Security and access

- Keep `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, and the origin bound to
  `127.0.0.1`; these are enforced by the deployment script.
- Protect `/admin*` with Cloudflare Access. Application login remains the second
  layer; Cloudflare Access is not a replacement for a strong admin password.
- Admin accounts do not currently support in-app 2FA. Use Cloudflare Access email
  OTP or a service token, and keep admin passwords in a password manager.
- Rotate the Brevo SMTP key and the Midtrans server key after any suspected leak.
  Update `.env`, then redeploy or restart the affected containers.
- Never commit `.env`, database files, backups, or logs.
- The Midtrans notification endpoint must remain publicly reachable without
  Cloudflare Access; it still verifies the Midtrans signature.

## Deploy and rollback

```bash
# Normal deploy from the server checkout:
./deploy.sh

# Skip Git synchronization only when deploying a deliberately checked-out revision:
DEPLOY_PULL_LATEST=false ./deploy.sh
```

Before deploying, make sure the local commit is pushed to the branch the server
tracks. The deploy script refuses a dirty checkout or a branch without an
upstream.

Rollback is code plus forward migrations, not a database time-travel button:

1. Stop traffic or put the site in maintenance mode if the release is broken.
2. Check out the last known-good commit (or set the image tag if you use one).
3. Restore the pre-deploy SQLite backup if a migration changed data
   incompatibly.
4. Run `DEPLOY_PULL_LATEST=false ./deploy.sh`.
5. Verify `/readyz` and one checkout.

## Incident quick reference

| Symptom | First checks |
| --- | --- |
| Site 502/530 | `docker compose ps`; cloudflared process; tunnel ingress; `docker compose logs nginx` |
| Site 500 | `docker compose logs php-fpm`; `/readyz`; recent deploy; storage permissions |
| Orders stay pending | Midtrans notification history; `orders:reconcile-payments`; scheduler logs |
| Email not arriving | worker logs; `queue:failed`; Brevo SMTP; `LAVERIE_ADMIN_EMAIL` |
| Disk full | `df -h`; Docker logs; backups; never prune volumes |
| Database locked | one worker/scheduler only; `DB_BUSY_TIMEOUT`; check long-running queries |
| Suspected admin takeover | disable Cloudflare Access session, rotate admin password, inspect access logs |

For a full outage, preserve the current SQLite file and logs before changing
containers. Do not run `docker compose down -v`.
