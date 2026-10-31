#!/usr/bin/env bash
# PKBM Aditya - Backup Script
# Usage: ./scripts/backup.sh [backup_dir]
# Creates a timestamped backup of database and storage/app
#
# SECURITY NOTES:
# - NEVER run against production database directly; use read replica or snapshot
# - DB_PASS must be set via environment variable (never hardcode)
# - Backup directory defaults to APP_ROOT/backups (must be outside web root)
# - Backup files created with restrictive permissions (600)
# - Consider encrypting backups before storage/transfer (see docs)

set -euo pipefail

# Configuration
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_NAME:-pkbm_aditya}"
DB_USER="${DB_USER:-pkbm_user}"
DB_PASS="${DB_PASS:-}"

APP_ROOT="${APP_ROOT:-/var/www/pkbm-aditya}"
STORAGE_PATH="${APP_ROOT}/storage/app"

# Output directory - MUST be outside web root
BACKUP_BASE="${1:-${APP_ROOT}/backups}"
TIMESTAMP=$(date +%F_%H-%M)
BACKUP_DIR="${BACKUP_BASE}/${TIMESTAMP}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[INFO]${NC} $*"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $*"; }
error() { echo -e "${RED}[ERROR]${NC} $*" >&2; }

# Validate required utilities
for cmd in mysqldump rsync; do
    if ! command -v "$cmd" >/dev/null 2>&1; then
        error "Required command '$cmd' not found in PATH"
        exit 1
    fi
done

# Validate database credentials
if [[ -z "$DB_PASS" ]]; then
    error "DB_PASS environment variable must be set"
    exit 1
fi

# PRODUCTION GUARD: Refuse to back up known production database names
case "${DB_NAME,,}" in
    *prod*|*production*|*live*)
        error "REFUSING: DB_NAME '$DB_NAME' appears to be a production database"
        error "Use a read replica or snapshot for production backups"
        exit 1
        ;;
esac

# Validate backup base directory is not inside web root
WEB_ROOT_CANDIDATES=("${APP_ROOT}/public" "${APP_ROOT}/public_html" "${APP_ROOT}/www" "${APP_ROOT}/htdocs")
for web_root in "${WEB_ROOT_CANDIDATES[@]}"; do
    if [[ -d "$web_root" ]]; then
        # Check if BACKUP_BASE is under web_root
        real_backup_base=$(realpath "$BACKUP_BASE" 2>/dev/null || echo "$BACKUP_BASE")
        real_web_root=$(realpath "$web_root" 2>/dev/null || echo "$web_root")
        if [[ "$real_backup_base" == "$real_web_root"* ]]; then
            error "REFUSING: Backup directory '$BACKUP_BASE' is inside web root '$web_root'"
            error "Backups must be stored outside publicly accessible directories"
            exit 1
        fi
    fi
done

# Create backup directory (fail if exists to prevent accidental overwrite)
if [[ -e "$BACKUP_DIR" ]]; then
    error "Backup directory already exists: $BACKUP_DIR"
    error "Refusing to overwrite. Remove manually or use different base path."
    exit 1
fi

# Create with restrictive permissions (owner read/write/execute only)
mkdir -p -m 700 "$BACKUP_DIR"
log "Created backup directory: $BACKUP_DIR"

# Backup database using --defaults-extra-file to avoid password in process list
MYSQL_DEFAULTS_FILE=$(mktemp --suffix=.cnf)
cat > "$MYSQL_DEFAULTS_FILE" <<EOF
[client]
host=$DB_HOST
port=$DB_PORT
user=$DB_USER
password=$DB_PASS
EOF
chmod 600 "$MYSQL_DEFAULTS_FILE"

# Trap to clean up temp file on exit
trap 'rm -f "$MYSQL_DEFAULTS_FILE"' EXIT

log "Backing up database: $DB_NAME"
mysqldump --defaults-extra-file="$MYSQL_DEFAULTS_FILE" \
    --single-transaction --routines --triggers "$DB_NAME" \
    > "$BACKUP_DIR/${DB_NAME}_backup.sql"

if [[ ! -s "$BACKUP_DIR/${DB_NAME}_backup.sql" ]]; then
    error "Database backup file is empty or missing"
    exit 1
fi

# Restrict backup file permissions
chmod 600 "$BACKUP_DIR/${DB_NAME}_backup.sql"

DB_SIZE=$(du -h "$BACKUP_DIR/${DB_NAME}_backup.sql" | cut -f1)
log "Database backup complete: $DB_SIZE"

# Backup storage
log "Backing up storage: $STORAGE_PATH"
rsync -a "$STORAGE_PATH/" "$BACKUP_DIR/storage_app/"

# Restrict storage backup permissions
find "$BACKUP_DIR/storage_app" -type f -exec chmod 600 {} \;
find "$BACKUP_DIR/storage_app" -type d -exec chmod 700 {} \;

STORAGE_COUNT=$(find "$BACKUP_DIR/storage_app" -type f | wc -l)
log "Storage backup complete: $STORAGE_COUNT files"

# Create manifest
cat > "$BACKUP_DIR/MANIFEST.txt" <<MANIFEST
PKBM Aditya Backup Manifest
Generated: $(date -Iseconds)
Hostname: $(hostname)
Database: $DB_NAME
Database Size: $DB_SIZE
Storage Files: $STORAGE_COUNT
Git Commit: $(cd "$APP_ROOT" && git rev-parse HEAD 2>/dev/null || echo "unknown")
Git Branch: $(cd "$APP_ROOT" && git branch --show-current 2>/dev/null || echo "unknown")
MANIFEST

# Restrict manifest permissions
chmod 600 "$BACKUP_DIR/MANIFEST.txt"

log "Backup completed successfully: $BACKUP_DIR"
log "Manifest written to $BACKUP_DIR/MANIFEST.txt"

# Clear sensitive variables
unset DB_PASS
MYSQL_DEFAULTS_FILE=""

echo "$BACKUP_DIR"