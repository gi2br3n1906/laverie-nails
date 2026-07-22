#!/usr/bin/env bash

set -Eeuo pipefail

readonly APP_SERVICE="php-fpm"
readonly WEB_SERVICE="nginx"
readonly HERO_ASSET="public/images/hero-banner.png"

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

if [[ "${DEPLOY_PULL_LATEST:-true}" == "true" ]]; then
    if ! command -v git >/dev/null 2>&1 || ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
        echo "DEPLOY_PULL_LATEST=true requires this deployment directory to be a Git checkout." >&2
        exit 1
    fi

    if [[ -n "$(git status --porcelain --untracked-files=no)" ]]; then
        echo "Tracked server files contain local changes. Commit or restore them before deploying." >&2
        exit 1
    fi

    if ! git rev-parse --abbrev-ref --symbolic-full-name '@{upstream}' >/dev/null 2>&1; then
        echo "The current deployment branch has no upstream. Configure it or run with DEPLOY_PULL_LATEST=false." >&2
        exit 1
    fi

    deployment_script_checksum_before_pull="$(sha256sum "$0" | awk '{print $1}')"
    echo "Fast-forwarding the server checkout to its configured upstream..."
    git pull --ff-only

    deployment_script_checksum_after_pull="$(sha256sum "$0" | awk '{print $1}')"
    if [[ "${deployment_script_checksum_before_pull}" != "${deployment_script_checksum_after_pull}" ]]; then
        echo "Deployment script changed during synchronization; restarting the updated script..."
        exec env DEPLOY_PULL_LATEST=false bash "$0" "$@"
    fi
else
    echo "Skipping Git synchronization because DEPLOY_PULL_LATEST=${DEPLOY_PULL_LATEST}."
fi

if [[ ! -f "${HERO_ASSET}" ]]; then
    echo "Required static asset ${HERO_ASSET} is missing from the deployment checkout." >&2
    exit 1
fi

deployment_revision="$(git rev-parse --short=12 HEAD 2>/dev/null || printf 'unversioned')"
echo "Deploying source revision ${deployment_revision}."

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
app_port="$(env_value APP_PORT)"
app_port="${app_port:-8080}"

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

build_args=(build --pull)
if [[ "${DEPLOY_NO_CACHE:-false}" == "true" ]]; then
    build_args+=(--no-cache)
fi

"${COMPOSE[@]}" "${build_args[@]}"
"${COMPOSE[@]}" up -d --force-recreate --remove-orphans

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

echo "Verifying immutable source and static assets inside both application images..."
host_hero_checksum="$(sha256sum "${HERO_ASSET}" | awk '{print $1}')"
php_hero_checksum="$("${COMPOSE[@]}" exec -T "${APP_SERVICE}" sha256sum "/var/www/html/${HERO_ASSET}" | awk '{print $1}')"
nginx_hero_checksum="$("${COMPOSE[@]}" exec -T "${WEB_SERVICE}" sha256sum "/var/www/html/${HERO_ASSET}" | awk '{print $1}')"

if [[ "${host_hero_checksum}" != "${php_hero_checksum}" || "${host_hero_checksum}" != "${nginx_hero_checksum}" ]]; then
    echo "Static asset checksum mismatch after deployment." >&2
    echo "Host:      ${host_hero_checksum}" >&2
    echo "PHP-FPM:   ${php_hero_checksum}" >&2
    echo "Nginx:     ${nginx_hero_checksum}" >&2
    exit 1
fi

echo "Hero asset checksum verified: ${host_hero_checksum}"

echo "Verifying the origin serves the cache-busted hero URL..."
hero_version="${host_hero_checksum:0:12}"
served_hero_checksum="$(curl --fail --silent --show-error "http://127.0.0.1:${app_port}/images/hero-banner.png?v=${hero_version}" | sha256sum | awk '{print $1}')"

if [[ "${host_hero_checksum}" != "${served_hero_checksum}" ]]; then
    echo "The Nginx origin returned an unexpected hero asset checksum." >&2
    echo "Expected: ${host_hero_checksum}" >&2
    echo "Served:   ${served_hero_checksum}" >&2
    exit 1
fi

echo "Origin hero response verified at /images/hero-banner.png?v=${hero_version}"

"${COMPOSE[@]}" ps

echo "Deployment complete. Laverie Nails is available on port ${app_port}."