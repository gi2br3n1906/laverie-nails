#!/bin/sh

set -eu

backup_dir="/var/www/data/backups"
database="/var/www/data/database.sqlite"

mkdir -p "$backup_dir"

stamp="$(date -u +%Y%m%dT%H%M%SZ)"
backup_file="${backup_dir}/database-${stamp}.sqlite"

php -r '$pdo = new PDO("sqlite:".$argv[1]); $pdo->exec("VACUUM INTO \"".$argv[2]."\"");' "$database" "$backup_file"

php -r '$pdo = new PDO("sqlite:".$argv[1]); $result = $pdo->query("PRAGMA integrity_check")->fetchColumn(); if ($result !== "ok") { fwrite(STDERR, "Backup integrity check failed: ".$result."\n"); exit(1); }' "$backup_file"

# Keep two weeks of local backups; copy them off the server for disaster recovery.
find "$backup_dir" -type f -name 'database-*.sqlite' -mtime +14 -delete

echo "SQLite backup written and verified: ${backup_file}"
