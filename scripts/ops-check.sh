#!/usr/bin/env bash

set -Eeuo pipefail

# Lightweight production checks for cron or a monitoring wrapper.
# Exit code 0 = healthy, 1 = one or more checks failed.
#
# Environment:
#   OPS_URL                    Public URL to probe (default: APP_URL from .env)
#   OPS_DISK_PERCENT           Fail above this disk usage percentage (default: 85)
#   OPS_BACKUP_MAX_AGE_HOURS   Fail if the newest local backup is older (default: 48)

readonly ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
readonly DISK_LIMIT="${OPS_DISK_PERCENT:-85}"
readonly BACKUP_MAX_AGE_HOURS="${OPS_BACKUP_MAX_AGE_HOURS:-48}"

cd "${ROOT_DIR}"

env_value() {
    sed -n "s/^$1=//p" .env 2>/dev/null | tail -n 1 | tr -d '\r' | sed -e 's/^"//' -e 's/"$//'
}

url="${OPS_URL:-$(env_value APP_URL)}"
url="${url%/}"
failures=0

pass() { printf 'OK   %s\n' "$1"; }
fail() {
    printf 'FAIL %s\n' "$1" >&2
    failures=$((failures + 1))
}

if [[ -z "${url}" ]]; then
    fail "APP_URL/OPS_URL is empty"
else
    for path in / /healthz /readyz; do
        if curl --fail --silent --show-error --max-time 10 --output /dev/null "${url}${path}"; then
            pass "HTTP ${url}${path}"
        else
            fail "HTTP ${url}${path}"
        fi
    done
fi

if command -v docker >/dev/null 2>&1; then
    if command -v docker-compose >/dev/null 2>&1; then
        COMPOSE=(docker-compose)
    elif docker compose version >/dev/null 2>&1; then
        COMPOSE=(docker compose)
    else
        COMPOSE=()
    fi

    if [[ ${#COMPOSE[@]} -gt 0 ]]; then
        running="$("${COMPOSE[@]}" ps --services --filter status=running 2>/dev/null || true)"
        for service in php-fpm nginx worker scheduler; do
            if grep -qx "${service}" <<<"${running}"; then
                pass "container ${service} running"
            else
                fail "container ${service} not running"
            fi
        done

        failed_jobs="$("${COMPOSE[@]}" exec -T --user www-data php-fpm \
            php artisan tinker --execute='echo \Illuminate\Support\Facades\DB::table("failed_jobs")->count();' \
            2>/dev/null | tail -n 1 | tr -dc '0-9' || true)"
        failed_jobs="${failed_jobs:-0}"

        if [[ "${failed_jobs}" -gt 0 ]]; then
            fail "${failed_jobs} failed queue job(s)"
        else
            pass "queue has no failed jobs"
        fi
    else
        fail "Docker Compose not found; container and queue checks skipped"
    fi
else
    fail "Docker not found; container and queue checks skipped"
fi

usage="$(df -P "${ROOT_DIR}" | awk 'NR==2 {gsub("%", "", $5); print $5}')"
if [[ -n "${usage}" && "${usage}" -gt "${DISK_LIMIT}" ]]; then
    fail "disk usage ${usage}% is above ${DISK_LIMIT}%"
else
    pass "disk usage ${usage:-unknown}%"
fi

latest_backup="$(find "${ROOT_DIR}/backups" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %p\n' 2>/dev/null | sort -nr | head -n 1 | cut -d' ' -f2-)"
if [[ -z "${latest_backup}" ]]; then
    fail "no local backup found"
else
    age_hours=$(( ( $(date +%s) - $(stat -c '%Y' "${latest_backup}") ) / 3600 ))
    if (( age_hours > BACKUP_MAX_AGE_HOURS )); then
        fail "newest backup is ${age_hours}h old (limit ${BACKUP_MAX_AGE_HOURS}h)"
    else
        pass "newest backup is ${age_hours}h old"
    fi
fi

if (( failures > 0 )); then
    echo "${failures} operational check(s) failed." >&2
    exit 1
fi

echo "All operational checks passed."
