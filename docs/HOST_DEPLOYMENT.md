# Host-native deployment (VPS/LXC without usable Docker)

This VPS is an LXC container where Docker cannot start containers:

```text
runc create failed: unable to start container process: ...
open sysctl net.ipv4.ip_unprivileged_port_start file: reopen fd 3: permission denied
```

That is a runc/AppArmor restriction in a nested LXC container, not a Laverie
configuration error. The working pattern used by `web-kalimati` is a host-native
Laravel deployment: Nginx + PHP-FPM + systemd queue worker + cron scheduler. Use
this document when Docker is not an option.

Do not run `docker compose` and the host-native services at the same time. Pick
one runtime and keep it as the owner of the SQLite database and queue.

## 1. Install the runtime

On Ubuntu/Debian, install Nginx, PHP 8.2-FPM, the Laravel extensions, Composer,
Git, and Node:

```bash
sudo apt update
sudo apt install -y \
  nginx git unzip curl \
  php8.3-fpm php8.3-cli php8.3-sqlite3 php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-zip php8.3-intl php8.3-bcmath php8.3-gd php8.3-opcache

# Ubuntu's default nodejs package is 18.x. Vite 7 needs Node 20.19+ or 22.12+.
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs

php -v
php -m | grep -E 'pdo_sqlite|sqlite3|mbstring|intl|zip|bcmath|opcache'
composer --version
node --version
npm --version
```

Do not use Ubuntu's `nodejs` 18 package for the build; it produces the
`@tailwindcss/oxide` native-binding error before Vite can start.

If the server uses another PHP version, replace `php8.3` everywhere and use the
matching `/run/php/phpX.Y-fpm.sock` path.

Install Composer if it is missing:

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## 2. Clone and configure

```bash
sudo install -d -o "$USER" -g "$USER" /var/www/laverie
git clone https://github.com/gi2br3n1906/laverie-nails.git /var/www/laverie
cd /var/www/laverie

git log -1 --oneline
cp .env.production.example .env
chmod 600 .env
```

Edit `.env`:

```bash
nano .env
```

Minimum values:

```dotenv
APP_KEY=base64:...
APP_ENV=production
APP_DEBUG=false
APP_URL=https://laverie.web.id
APP_PORT=8080
APP_TIMEZONE=Asia/Jakarta
SESSION_SECURE_COOKIE=true
TRUSTED_PROXIES=*
CLOUDFLARE_ACCESS_TEAM_DOMAIN=https://your-team.cloudflareaccess.com
CLOUDFLARE_ACCESS_AUDIENCE=<application-audience-tag>

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/laverie/database/database.sqlite
DB_BUSY_TIMEOUT=5000
DB_JOURNAL_MODE=WAL

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=***
MAIL_FROM_ADDRESS=admin@laverie.web.id
MAIL_FROM_NAME="Laverie Nails"

LAVERIE_ADMIN_EMAIL=laverienails@gmail.com
LAVERIE_WHATSAPP_NUMBER=62...

MIDTRANS_SERVER_KEY=...          # Sandbox
MIDTRANS_CLIENT_KEY=...          # Sandbox
MIDTRANS_IS_PRODUCTION=false
```

Important:

- `DB_DATABASE` must be an absolute path. The example path assumes the clone is
  at `/var/www/laverie`.
- The template has Sandbox Midtrans URLs. Keep them until Midtrans approves
  production.
- Do not copy the local development `.env` or local SQLite file to the server.
- `MAIL_PASSWORD` is the Brevo SMTP key, not the Brevo account password.
- `CLOUDFLARE_ACCESS_TEAM_DOMAIN` is the Zero Trust team domain, and
  `CLOUDFLARE_ACCESS_AUDIENCE` is the Access application's AUD tag. Copy both
  from Cloudflare Access; the deploy script requires them for admin SSO.

Install dependencies and generate the application key:

```bash
sudo env COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
sudo php artisan key:generate
sudo mkdir -p database
sudo touch database/database.sqlite
sudo chown www-data:www-data .env database/database.sqlite
```

## 3. Install Nginx

```bash
sudo cp deploy/host/nginx/laverie.conf.example /etc/nginx/sites-available/laverie
sudo ln -sfn /etc/nginx/sites-available/laverie /etc/nginx/sites-enabled/laverie
sudo nginx -t
sudo systemctl reload nginx
```

The config listens on `127.0.0.1:8080`, not public port 80/443. Cloudflare Tunnel
connects to that loopback address. The config already targets PHP-FPM 8.3:
`fastcgi_pass unix:/run/php/php8.3-fpm.sock;`.

If Nginx is already serving another site on port 80, leave it alone; Laverie uses
its own loopback port.

## 4. Install the queue worker and scheduler

```bash
sudo cp deploy/host/systemd/laverie-worker.service.example /etc/systemd/system/laverie-worker.service
sudo systemctl daemon-reload
sudo systemctl enable --now laverie-worker

sudo install -d -o www-data -g www-data /var/log/laverie
sudo cp deploy/host/cron/laverie-scheduler /etc/cron.d/laverie-scheduler
sudo chmod 644 /etc/cron.d/laverie-scheduler
```

Check both:

```bash
systemctl status laverie-worker --no-pager
sudo -u www-data php artisan schedule:list
```

The cron entry runs `schedule:run` every minute. It replaces the Docker
`scheduler` service and runs the payment reconciliation command.

## 5. Build and deploy

```bash
sudo PHP_BIN=php8.3 ./scripts/deploy-host.sh
```

The host deploy script:

1. pulls the current branch;
2. installs Composer dependencies and builds Vite assets;
3. creates the SQLite file and writable runtime directories;
4. puts the app into maintenance mode;
5. migrates the database and seeds size standards;
6. rebuilds Laravel caches;
7. restarts the queue worker;
8. checks `/healthz` and `/readyz` on `127.0.0.1:8080`.

If Nginx is not configured yet, the final health check will fail; configure
Nginx first, then rerun the script.

The first run does not create an admin or catalog data. Do that next.

## 6. Create the admin and demo catalog

```bash
cd /var/www/laverie

sudo -u www-data php artisan laverie:create-admin admin@laverie.web.id

# Only for a deliberate presentation/demo database. The production guard is
# bypassed for this one command; do not enable it permanently.
sudo -u www-data env LAVERIE_ALLOW_DEMO_SEED=true \
  php artisan db:seed --class=PresentationCatalogSeeder --force
```

The admin command asks for a password interactively. It must be at least 12
characters; if it is shorter, the command exits without creating the account.
Do not pass a password in a shared shell history. When Cloudflare Access is
configured with the team domain and app audience, the matching admin account is
logged in automatically after the Access JWT is verified at the origin.

## 7. Connect Cloudflare Tunnel

In Cloudflare Dashboard → Networks → Tunnels → your tunnel → Public Hostname:

- Domain: `laverie.web.id`
- Subdomain: blank / `@`
- Service: `http://127.0.0.1:8080`

The connector (`cloudflared`) must run on this same VPS. For LXC, run it with
HTTP/2 and host networking:

```bash
cloudflared tunnel run NAMA_TUNNEL --protocol http2
```

If you need `www.laverie.web.id`, add another public hostname pointing at the same
origin, then create a Redirect Rule from `www` to the apex.

Do not create an A/AAAA record pointing at the container. With a tunnel, DNS is a
Cloudflare-managed CNAME and the origin stays loopback-only.

Verify from outside:

```bash
curl -I https://laverie.web.id/
curl -fsS https://laverie.web.id/readyz
```

A 530 means the tunnel connector is offline or the public hostname is not
attached to the tunnel.

## 8. Cloudflare and Midtrans

Before sharing the URL:

- SSL/TLS → Always Use HTTPS.
- Cloudflare Access for `/admin*`.
- WAF skip for `POST /api/payments/midtrans/notification`.
- Bypass cache for `/checkout*`, `/admin*`, `/api/*`, and HTML.
- Midtrans notification URL:
  `https://laverie.web.id/api/payments/midtrans/notification`.
- Run one Sandbox order until the order is `paid`.

Keep `MIDTRANS_IS_PRODUCTION=false` until Midtrans approves production. When
approved, change the keys and all Midtrans URLs together; never mix Sandbox keys
with production URLs.

## 9. Verify before calling it done

```bash
systemctl status laverie-worker --no-pager
sudo -u www-data php artisan schedule:list
sudo -u www-data php artisan queue:failed
curl -fsS http://127.0.0.1:8080/healthz
curl -fsS http://127.0.0.1:8080/readyz
```

Check in the browser:

- [ ] `https://laverie.web.id` loads from the tunnel.
- [ ] Admin login works.
- [ ] Demo products/reviews are visible.
- [ ] Sandbox checkout reaches `paid`.
- [ ] Admin order email arrives.
- [ ] WhatsApp CTA uses the real number.
- [ ] Worker and scheduler are running.

## 10. Backups later

The host deployment does not use Docker volumes. After go-live, back up:

- `/var/www/laverie/database/database.sqlite`
- `/var/www/laverie/storage/app/public`

Use a SQLite-aware snapshot (`VACUUM INTO`), not a copy of a live WAL file. The
Docker backup script is not applicable here; add a host backup script or use the
existing `deploy-host.sh` migration-time backup pattern as a starting point.
