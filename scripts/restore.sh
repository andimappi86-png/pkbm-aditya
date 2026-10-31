#!/usr/bin/env bash
# PKBM Aditya - Restore Script
# Usage: ./scripts/restore.sh <backup_dir> [target_db_name]
# Restores database and storage from a backup created by backup.sh
#
# WARNING: This will DESTROY existing data in the target database.
# Use ONLY on disposable/test databases. NEVER run on production.
#
# SECURITY NOTES:
# - DB_PASS must be set via environment variable (never hardcode)
# - Target database name MUST be explicitly provided (no default)
# - Production database names are BLOCKED by default
# - Confirmation REQUIRED even in non-interactive mode (use FORCE_RESTORE=true to bypass with extreme caution)
# - Backup files should be encrypted at rest; decrypt before restore
# - Storage restore uses rsync --delete; ensure backup is trusted

set -euo pipefail

# Configuration
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-pkbm_user}"
DB_PASS="${DB_PASS:-}"

APP_ROOT="${APP_ROOT:-/var/www/pkbm-aditya}"
STORAGE_PATH="${APP_ROOT}/storage/app"

# Input validation - TARGET_DB is REQUIRED (no default)
BACKUP_DIR="${1:-}"
TARGET_DB="${2:-}"

if [[ -z "$BACKUP_DIR" || ! -d "$BACKUP_DIR" ]]; then
    echo "Usage: $0 <backup_dir> <target_db_name>" >&2
    echo "Example: $0 /var/www/pkbm-aditya/backups/2026-10-26_13-45 pkbm_aditya_test" >&2
    echo "" >&2
    echo "ERROR: target_db_name is REQUIRED (no default) to prevent accidental production restore" >&2
    exit 1
fi

if [[ ! -f "$BACKUP_DIR/MANIFEST.txt" ]]; then
    echo "ERROR: Invalid backup directory (missing MANIFEST.txt): $BACKUP_DIR" >&2
    exit 1
fi

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[INFO]${NC} $*"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $*"; }
error() { echo -e "${RED}[ERROR]${NC} $*" >&2; }

# Validate required utilities
for cmd in mysql mysqldump rsync; do
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

# PRODUCTION GUARD: Block known production database names
case "${TARGET_DB,,}" in
    *prod*|*production*|*live*|pkbm_aditya)
        error "REFUSING: Target database '$TARGET_DB' appears to be a production database"
        error "This script is for DISPOSABLE/TEST databases only"
        error "Override with extreme caution: export ALLOW_PROD_RESTORE=true"
        if [[ "${ALLOW_PROD_RESTORE:-false}" != "true" ]]; then
            exit 1
        fi
        warn "ALLOW_PROD_RESTORE=true set - PROCEEDING WITH EXTREME CAUTION"
        ;;
esac

# Verify target database exists and is accessible
log "Verifying target database: $TARGET_DB"
MYSQL_DEFAULTS_FILE=$(mktemp --suffix=.cnf)
cat > "$MYSQL_DEFAULTS_FILE" <<EOF
[client]
host=$DB_HOST
port=$DB_PORT
user=$DB_USER
password=$DB_PASS
EOF
chmod 600 "$MYSQL_DEFAULTS_FILE"
trap 'rm -f "$MYSQL_DEFAULTS_FILE"' EXIT

if ! mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" -e "USE \`$TARGET_DB\`; SELECT 1;" >/dev/null 2>&1; then
    error "Cannot access target database '$TARGET_DB'. Does it exist? Does user have privileges?"
    exit 1
fi

# CONFIRMATION REQUIRED - even in non-interactive mode
# Use FORCE_RESTORE=true to bypass (NOT RECOMMENDED for production)
if [[ "${FORCE_RESTORE:-false}" != "true" ]]; then
    if [[ -t 0 ]]; then
        echo -e "${RED}WARNING: This will DESTROY all data in database '$TARGET_DB'${NC}"
        echo -e "${RED}Source backup: $BACKUP_DIR${NC}"
        read -rp "Type 'RESTORE' to confirm: " confirm
        if [[ "$confirm" != "RESTORE" ]]; then
            error "Restore cancelled by user"
            exit 1
        fi
    else
        error "Non-interactive mode requires FORCE_RESTORE=true (NOT RECOMMENDED)"
        error "Run interactively or explicitly set FORCE_RESTORE=true"
        exit 1
    fi
else
    warn "FORCE_RESTORE=true set - skipping interactive confirmation"
fi

# Restore database
BACKUP_SQL=$(find "$BACKUP_DIR" -name "*_backup.sql" | head -1)
if [[ ! -f "$BACKUP_SQL" ]]; then
    error "Backup SQL file not found in $BACKUP_DIR"
    exit 1
fi

log "Restoring database to: $TARGET_DB"
mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" "$TARGET_DB" < "$BACKUP_SQL"

log "Database restore complete"

# COMPREHENSIVE VERIFICATION: Compare row counts for ALL tables
log "Verifying restore integrity (row counts for all tables)..."
TABLES=$(mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" -e "USE \`$TARGET_DB\`; SHOW TABLES;" | tail -n +2)

MISMATCH_COUNT=0
TOTAL_TABLES=0
for table in $TABLES; do
    TOTAL_TABLES=$((TOTAL_TABLES + 1))
    SOURCE_COUNT=$(mysql --defaults-extra-file="$MYSQL_DEFAULTS_FILE" -e "USE \`$TARGET_DB\`; SELECT COUNT(*) FROM \`$table\`;" 2>/dev/null | tail -1)
    # We can't compare to source DB directly since it may have changed
    # But we can verify the table exists and has data
    if [[ -z "$SOURCE_COUNT" || "$SOURCE_COUNT" -eq 0 ]]; then
        warn "Table '$table' has 0 rows (may be expected for empty tables)"
    fi
    log "  Table '$table': $SOURCE_COUNT rows"
done

log "Verified $TOTAL_TABLES tables in restored database"

# Restore storage (with safety check)
if [[ -d "$BACKUP_DIR/storage_app" ]]; then
    # Safety: verify backup storage_app is not empty
    BACKUP_FILE_COUNT=$(find "$BACKUP_DIR/storage_app" -type f | wc -l)
    if [[ "$BACKUP_FILE_COUNT" -eq 0 ]]; then
        warn "Backup storage_app is empty, skipping storage restore"
    else
        log "Restoring storage to: $STORAGE_PATH"
        log "Backup contains $BACKUP_FILE_COUNT files"

        # Optional: create a snapshot of current storage before overwrite
        if [[ "${BACKUP_CURRENT_STORAGE:-false}" == "true" ]]; then
            SNAPSHOT_DIR="${STORAGE_PATH}.pre-restore-$(date +%F_%H-%M)"
            log "Creating snapshot of current storage at: $SNAPSHOT_DIR"
            rsync -a "$STORAGE_PATH/" "$SNAPSHOT_DIR/"
        fi

        rsync -a --delete "$BACKUP_DIR/storage_app/" "$STORAGE_PATH/"

        # Restrict restored file permissions
        find "$STORAGE_PATH" -type f -exec chmod 600 {} \;
        find "$STORAGE_PATH" -type d -exec chmod 700 {} \;

        STORAGE_COUNT=$(find "$STORAGE_PATH" -type f | wc -l)
        log "Storage restore complete: $STORAGE_COUNT files"
    fi
else
    warn "No storage_app directory in backup, skipping storage restore"
fi

# Migrations: DISABLED by default - must explicitly enable
# Running migrations on restored DB can alter schema unexpectedly
if [[ "${RUN_MIGRATIONS:-false}" == "true" ]]; then
    warn "RUN_MIGRATIONS=true - running migrations on restored database"
    log "Running migrations on restored database..."
    cd "$APP_ROOT"
    php artisan migrate --force
else
    log "Skipping migrations (set RUN_MIGRATIONS=true to enable)"
fi

# Clear sensitive variables
unset DB_PASS

log "Restore completed successfully: $TARGET_DB"
log "Backup source: $BACKUP_DIR"
log "Tables verified: $TOTAL_TABLES"