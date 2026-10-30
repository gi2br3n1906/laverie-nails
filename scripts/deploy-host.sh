#!/usr/bin/env bash

set -Eeuo pipefail

# Host-native deployment for a VPS/LXC where Docker containers cannot start.
# Run as root from the application checkout:
#   sudo ./scripts/deploy-host.sh
#
# The script expects the app at /var/www/laverie by default. Override APP_ROOT.

readonly SCRIPT_ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
readonly APP_ROOT="${APP_ROOT:-${SCRIPT_ROOT}}"
readonly APP_USER="${APP_USER:-www-data}"
readonly PHP_BIN="${PHP_BIN:-php}"
readonly APP_PORT="${APP_PORT:-8080}"
readonly BACKUP_KEEP_DAYS="${BACKUP_KEEP_DAYS:-14}"

cd "${APP_ROOT}"

if [[ "${EUID}" -ne 0 ]]; then
    echo "Run this script as root: sudo ./scripts/deploy-host.sh" >&2
    exit 1
fi

if [[ ! -f .env ]]; then
    echo "Missing .env. Copy .env.production.example and fill production values first." >&2
    exit 1
fi

if ! git diff --quiet || ! git diff --cached --quiet; then
    echo "Deployment aborted: tracked local changes found in ${APP_ROOT}." >&2
    git status --short >&2
    exit 1
fi

for command in git composer npm "$PHP_BIN"; do
    if ! command -v "${command}" >/dev/null 2>&1; then
        echo "Required command not found: ${command}" >&2
        exit 1
    fi
done

app_key="$(sed -n 's/^APP_KEY=//p' .env | tail -n 1 | tr -d '\r' | sed -e 's/^"//' -e 's/"$//')"
if [[ ! "${app_key}" =~ ^base64:[A-Za-z0-9+/]{43}=$ ]]; then
    echo "APP_KEY is missing or malformed in .env." >&2
    echo "Generate it with: php artisan key:generate (or run the initial setup steps in docs/HOST_DEPLOYMENT.md)." >&2
    exit 1
fi

db_path="$(sed -n 's/^DB_DATABASE=//p' .env | tail -n 1 | tr -d '\r' | sed -e 's/^"//' -e 's/"$//')"
if [[ -z "${db_path}" || "${db_path}" != /* ]]; then
    echo "DB_DATABASE must be an absolute path for a host deployment." >&2
    exit 1
fi

if [[ "${DEPLOY_PULL_LATEST:-true}" == "true" ]]; then
    git pull --ff-only
fi

echo "Installing PHP dependencies..."
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "Building frontend assets..."
npm ci --no-audit --no-fund --progress=false
npm run build

if [[ ! -s public/build/manifest.json ]]; then
    echo "Vite manifest is missing after npm run build." >&2
    exit 1
fi

echo "Preparing writable runtime directories and database..."
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    "$(dirname "${db_path}")"
touch "${db_path}"
ln -sfn ../storage/app/public public/storage
chown -R "${APP_USER}:${APP_USER}" storage bootstrap/cache "$(dirname "${db_path}")"
chown "${APP_USER}:${APP_USER}" .env
chmod 640 .env
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;

if [[ -s "${db_path}" ]]; then
    echo "Creating a pre-migration SQLite snapshot..."
    mkdir -p "${APP_ROOT}/backups"
    backup_file="${APP_ROOT}/backups/database-$(date -u +%Y%m%dT%H%M%SZ).sqlite"
    "${PHP_BIN}" -r '$pdo = new PDO("sqlite:".$argv[1]); $pdo->exec("VACUUM INTO \"".$argv[2]."\"");' "${db_path}" "${backup_file}"
    "${PHP_BIN}" -r '$pdo = new PDO("sqlite:".$argv[1]); $result = $pdo->query("PRAGMA integrity_check")->fetchColumn(); if ($result !== "ok") { fwrite(STDERR, "Backup integrity check failed: ".$result."\n"); exit(1); }' "${backup_file}"
    find "${APP_ROOT}/backups" -type f -name 'database-*.sqlite' -mtime "+${BACKUP_KEEP_DAYS}" -delete
fi

run_artisan() {
    sudo -u "${APP_USER}" -- "${PHP_BIN}" artisan "$@"
}

echo "Putting the application into maintenance mode..."
run_artisan down || true

cleanup() {
    run_artisan up || true
}
trap cleanup EXIT

echo "Running migrations..."
run_artisan migrate --force

echo "Synchronizing canonical nail size standards..."
run_artisan db:seed --class=SizeStandardSeeder --force

echo "Building production caches..."
run_artisan optimize:clear
run_artisan optimize
run_artisan view:cache

echo "Restarting the queue worker..."
run_artisan queue:restart

echo "Verifying the local Nginx origin..."
curl --fail --silent --show-error "http://127.0.0.1:${APP_PORT}/healthz" >/dev/null
curl --fail --silent --show-error "http://127.0.0.1:${APP_PORT}/readyz" >/dev/null

run_artisan up || true
trap - EXIT

echo "Host deployment complete. Verify https://laverie.web.id/readyz through the tunnel."
