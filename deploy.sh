#!/usr/bin/env bash

set -Eeuo pipefail

readonly APP_SERVICE="php-fpm"

on_error() {
    echo "Deployment failed on line $1." >&2
}

trap 'on_error "$LINENO"' ERR

if ! command -v docker >/dev/null 2>&1; then
    echo "Docker is required but was not found." >&2
    exit 1
fi

if ! docker info >/dev/null 2>&1; then
    echo "The Docker daemon is not reachable. Start Docker Engine before deploying." >&2
    exit 1
fi

if command -v docker-compose >/dev/null 2>&1; then
    COMPOSE=(docker-compose)
elif docker compose version >/dev/null 2>&1; then
    COMPOSE=(docker compose)
else
    echo "Docker Compose is required but was not found." >&2
    exit 1
fi

if [[ ! -f .env ]]; then
    echo "Missing .env. Copy .env.example, set production values, and generate APP_KEY before deploying." >&2
    exit 1
fi

env_value() {
    sed -n "s/^$1=//p" .env | tail -n 1 | tr -d '\r' | sed -e 's/^"//' -e 's/"$//'
}

app_key="$(env_value APP_KEY)"
app_url="$(env_value APP_URL)"

if [[ ! "${app_key}" =~ ^base64:[A-Za-z0-9+/]{43}=$ ]]; then
    echo "APP_KEY is missing from .env. Run 'php artisan key:generate' securely before deploying." >&2
    exit 1
fi

if [[ "$(env_value APP_ENV)" != "production" ]]; then
    echo "APP_ENV must be set to production in .env." >&2
    exit 1
fi

if [[ "$(env_value APP_DEBUG)" != "false" ]]; then
    echo "APP_DEBUG must be set to false in .env." >&2
    exit 1
fi

if [[ -z "${app_url}" || "${app_url}" =~ ^https?://(localhost|127\.0\.0\.1)([:/]|$) ]]; then
    echo "APP_URL must be set to the public production URL in .env." >&2
    exit 1
fi

echo "Starting Laverie Nails production deployment..."

"${COMPOSE[@]}" up -d --build --remove-orphans

echo "Waiting for PHP-FPM to become available..."
for attempt in {1..30}; do
    if "${COMPOSE[@]}" exec -T "${APP_SERVICE}" php -v >/dev/null 2>&1; then
        break
    fi

    if [[ "${attempt}" -eq 30 ]]; then
        echo "PHP-FPM did not become available in time." >&2
        "${COMPOSE[@]}" ps
        exit 1
    fi

    sleep 2
done

echo "Preparing persistent SQLite and Laravel runtime directories..."
"${COMPOSE[@]}" exec -T --user root "${APP_SERVICE}" sh -lc '
    set -eu
    mkdir -p \
        /var/www/data \
        /var/www/html/storage/app/public \
        /var/www/html/storage/framework/cache/data \
        /var/www/html/storage/framework/sessions \
        /var/www/html/storage/framework/testing \
        /var/www/html/storage/framework/views \
        /var/www/html/storage/logs \
        /var/www/html/bootstrap/cache
    touch /var/www/data/database.sqlite
    chown -R www-data:www-data \
        /var/www/data \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache
    find /var/www/data /var/www/html/storage /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \;
    find /var/www/data /var/www/html/storage /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \;
'

artisan() {
    "${COMPOSE[@]}" exec -T --user www-data "${APP_SERVICE}" php artisan "$@"
}

echo "Clearing stale Laravel bootstrap caches..."
artisan config:clear
artisan route:clear
artisan view:clear
artisan event:clear

echo "Running database migrations..."
artisan migrate --force

echo "Synchronizing canonical nail size standards..."
artisan db:seed --class=SizeStandardSeeder --force

echo "Building Laravel production caches..."
artisan optimize:clear
artisan optimize
artisan view:cache

"${COMPOSE[@]}" ps

echo "Deployment complete. Laverie Nails is available on port ${APP_PORT:-8080}."