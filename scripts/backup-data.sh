#!/usr/bin/env bash

set -Eeuo pipefail

# Back up the SQLite database and catalog uploads without stopping the app.
# Run this on the VPS from the deployment checkout. The SQLite snapshot is
# created with VACUUM INTO and integrity-checked by docker/backup-sqlite.sh.
#
# Environment:
#   BACKUP_DIR                 Host directory for local snapshots (default: ./backups)
#   BACKUP_KEEP_DAYS           Local retention in days (default: 14)
#   BACKUP_RCLONE_REMOTE       Optional rclone destination, e.g. b2:laverie-backups
#   BACKUP_RCLONE_KEEP_DAYS    Off-site retention in days (default: 30)
#
# Example:
#   BACKUP_RCLONE_REMOTE=b2:laverie-backups ./scripts/backup-data.sh

readonly ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
readonly BACKUP_DIR="${BACKUP_DIR:-${ROOT_DIR}/backups}"
readonly KEEP_DAYS="${BACKUP_KEEP_DAYS:-14}"
readonly RCLONE_REMOTE="${BACKUP_RCLONE_REMOTE:-}"
readonly RCLONE_KEEP_DAYS="${BACKUP_RCLONE_KEEP_DAYS:-30}"
readonly STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
readonly TMP_DIR="$(mktemp -d "${TMPDIR:-/tmp}/laverie-backup.XXXXXX")"

cleanup() {
    rm -rf -- "${TMP_DIR}"
}
trap cleanup EXIT

on_error() {
    echo "Backup failed on line $1." >&2
}
trap 'on_error "${LINENO}"' ERR

cd "${ROOT_DIR}"

if ! command -v docker >/dev/null 2>&1; then
    echo "Docker is required but was not found." >&2
    exit 1
fi

if ! docker info >/dev/null 2>&1; then
    echo "The Docker daemon is not reachable." >&2
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

mkdir -p "${BACKUP_DIR}"

echo "Creating an integrity-checked SQLite snapshot inside the application container..."
"${COMPOSE[@]}" exec -T --user www-data php-fpm sh /var/www/html/docker/backup-sqlite.sh

database_path="$("${COMPOSE[@]}" exec -T --user www-data php-fpm sh -lc \
    'ls -1t /var/www/data/backups/database-*.sqlite | head -n 1' | tr -d '\r')"

if [[ -z "${database_path}" ]]; then
    echo "Could not find the SQLite backup produced by backup-sqlite.sh." >&2
    exit 1
fi

echo "Copying database snapshot to the host..."
"${COMPOSE[@]}" cp "php-fpm:${database_path}" "${TMP_DIR}/database.sqlite"

echo "Archiving catalog uploads..."
"${COMPOSE[@]}" exec -T --user www-data php-fpm \
    sh -lc 'tar -C /var/www/html/storage/app/public -czf - .' > "${TMP_DIR}/public-uploads.tar.gz"

if ! tar -tzf "${TMP_DIR}/public-uploads.tar.gz" >/dev/null 2>&1; then
    echo "The catalog uploads archive is invalid." >&2
    exit 1
fi

revision="$(git rev-parse --short=12 HEAD 2>/dev/null || printf 'unversioned')"
mkdir -p "${BACKUP_DIR}/${STAMP}"

mv "${TMP_DIR}/database.sqlite" "${BACKUP_DIR}/${STAMP}/database.sqlite"
mv "${TMP_DIR}/public-uploads.tar.gz" "${BACKUP_DIR}/${STAMP}/public-uploads.tar.gz"

{
    echo "created_at=${STAMP}"
    echo "git_revision=${revision}"
    echo "database_source=${database_path}"
    echo "database_sha256=$(sha256sum "${BACKUP_DIR}/${STAMP}/database.sqlite" | awk '{print $1}')"
    echo "uploads_sha256=$(sha256sum "${BACKUP_DIR}/${STAMP}/public-uploads.tar.gz" | awk '{print $1}')"
} > "${BACKUP_DIR}/${STAMP}/manifest.txt"

echo "Backup written to ${BACKUP_DIR}/${STAMP}"

if [[ -n "${RCLONE_REMOTE}" ]]; then
    if ! command -v rclone >/dev/null 2>&1; then
        echo "BACKUP_RCLONE_REMOTE is set but rclone is not installed." >&2
        exit 1
    fi

    echo "Uploading the backup to ${RCLONE_REMOTE}/${STAMP}..."
    rclone copy "${BACKUP_DIR}/${STAMP}" "${RCLONE_REMOTE}/${STAMP}" --immutable

    echo "Pruning off-site backups older than ${RCLONE_KEEP_DAYS} days..."
    rclone delete "${RCLONE_REMOTE}" --min-age "${RCLONE_KEEP_DAYS}d" --rmdirs
else
    echo "WARNING: BACKUP_RCLONE_REMOTE is empty; this backup is only on this host." >&2
fi

find "${BACKUP_DIR}" -mindepth 1 -maxdepth 1 -type d -mtime "+${KEEP_DAYS}" -exec rm -rf -- {} +

echo "Backup complete. Local retention: ${KEEP_DAYS} days."
