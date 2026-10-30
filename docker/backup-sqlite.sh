#!/bin/sh

set -eu

backup_dir="/var/www/data/backups"
database="/var/www/data/database.sqlite"

mkdir -p "$backup_dir"

stamp="$(date -u +%Y%m%dT%H%M%SZ)"

php -r '$pdo = new PDO("sqlite:/var/www/data/database.sqlite"); $pdo->exec("VACUUM INTO \"/var/www/data/backups/database-'"$stamp"'.sqlite\"");'

# Keep two weeks of local backups; copy them off the server for disaster recovery.
find "$backup_dir" -type f -name 'database-*.sqlite' -mtime +14 -delete

echo "SQLite backup written to ${backup_dir}/database-${stamp}.sqlite"
