#!/bin/bash

#######################################################
# Teleroute Marketplace - Backup Script
# Usage: ./backup.sh [type]
# Types: full, database, files
#######################################################

set -e  # Exit on error

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

# Configuration
BACKUP_TYPE=${1:-full}
APP_DIR="/var/www/teleroute-marketplace"
BACKUP_DIR="/var/backups/teleroute"
DATE=$(date +%Y%m%d_%H%M%S)
KEEP_DAYS=30

# Database credentials (should be loaded from .env in production)
DB_HOST="localhost"
DB_NAME="teleroute_marketplace"
DB_USER="teleroute_user"
DB_PASS="your_secure_password"

# S3 Configuration (optional - for offsite backups)
S3_BUCKET=""
S3_ENABLED=false

# Functions
print_msg() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

# Create backup directory
mkdir -p "$BACKUP_DIR"/{database,files,full}

# Backup database
backup_database() {
    print_msg "Backing up database..."

    BACKUP_FILE="$BACKUP_DIR/database/db_${DATE}.sql"

    # Dump database
    mysqldump -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" \
        --single-transaction \
        --quick \
        --lock-tables=false \
        "$DB_NAME" > "$BACKUP_FILE"

    # Compress
    gzip "$BACKUP_FILE"
    BACKUP_FILE="${BACKUP_FILE}.gz"

    SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    print_success "Database backup created: $BACKUP_FILE ($SIZE)"

    echo "$BACKUP_FILE"
}

# Backup files
backup_files() {
    print_msg "Backing up files..."

    BACKUP_FILE="$BACKUP_DIR/files/files_${DATE}.tar.gz"

    # Exclude certain directories
    tar -czf "$BACKUP_FILE" \
        --exclude="$APP_DIR/cache/*" \
        --exclude="$APP_DIR/logs/*" \
        --exclude="$APP_DIR/.git" \
        --exclude="$APP_DIR/node_modules" \
        --exclude="$APP_DIR/vendor" \
        -C "$(dirname $APP_DIR)" \
        "$(basename $APP_DIR)"

    SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    print_success "Files backup created: $BACKUP_FILE ($SIZE)"

    echo "$BACKUP_FILE"
}

# Full backup
backup_full() {
    print_msg "Creating full backup..."

    DB_FILE=$(backup_database)
    FILES_FILE=$(backup_files)

    # Create combined archive
    FULL_BACKUP="$BACKUP_DIR/full/full_${DATE}.tar.gz"
    tar -czf "$FULL_BACKUP" \
        "$DB_FILE" \
        "$FILES_FILE"

    SIZE=$(du -h "$FULL_BACKUP" | cut -f1)
    print_success "Full backup created: $FULL_BACKUP ($SIZE)"

    echo "$FULL_BACKUP"
}

# Upload to S3 (if enabled)
upload_to_s3() {
    if [ "$S3_ENABLED" = true ] && [ -n "$S3_BUCKET" ]; then
        print_msg "Uploading to S3..."
        aws s3 cp "$1" "s3://${S3_BUCKET}/backups/$(basename $1)"
        print_success "Uploaded to S3: s3://${S3_BUCKET}/backups/$(basename $1)"
    fi
}

# Cleanup old backups
cleanup_old_backups() {
    print_msg "Cleaning up backups older than ${KEEP_DAYS} days..."

    # Database backups
    find "$BACKUP_DIR/database" -name "*.sql.gz" -type f -mtime +$KEEP_DAYS -delete
    # File backups
    find "$BACKUP_DIR/files" -name "*.tar.gz" -type f -mtime +$KEEP_DAYS -delete
    # Full backups
    find "$BACKUP_DIR/full" -name "*.tar.gz" -type f -mtime +$KEEP_DAYS -delete

    print_success "Old backups cleaned"
}

# Verify backup
verify_backup() {
    print_msg "Verifying backup integrity..."

    if [ -f "$1" ]; then
        if file "$1" | grep -q "gzip compressed"; then
            if gzip -t "$1" 2>/dev/null; then
                print_success "Backup file is valid: $1"
                return 0
            else
                print_error "Backup file is corrupted: $1"
                return 1
            fi
        elif file "$1" | grep -q "tar archive"; then
            if tar -tzf "$1" > /dev/null 2>&1; then
                print_success "Backup file is valid: $1"
                return 0
            else
                print_error "Backup file is corrupted: $1"
                return 1
            fi
        fi
    else
        print_error "Backup file not found: $1"
        return 1
    fi
}

# Create backup report
create_report() {
    REPORT_FILE="$BACKUP_DIR/backup_report_${DATE}.txt"

    cat > "$REPORT_FILE" << EOF
========================================
Teleroute Marketplace - Backup Report
========================================
Date: $(date '+%Y-%m-%d %H:%M:%S')
Type: ${BACKUP_TYPE}
Host: $(hostname)

Files:
$(ls -lh $1)

Disk Usage:
$(df -h "$BACKUP_DIR")

Database Size:
$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.TABLES WHERE table_schema = '$DB_NAME' GROUP BY table_schema;" 2>/dev/null || echo "N/A")

EOF

    print_success "Backup report created: $REPORT_FILE"
}

# Main execution
print_msg "Starting ${BACKUP_TYPE} backup..."

BACKUP_FILE=""

case $BACKUP_TYPE in
    database)
        BACKUP_FILE=$(backup_database)
        ;;
    files)
        BACKUP_FILE=$(backup_files)
        ;;
    full)
        BACKUP_FILE=$(backup_full)
        ;;
    *)
        print_error "Invalid backup type: $BACKUP_TYPE"
        echo "Usage: $0 [full|database|files]"
        exit 1
        ;;
esac

# Verify backup
verify_backup "$BACKUP_FILE"

# Upload to S3
upload_to_s3 "$BACKUP_FILE"

# Cleanup
cleanup_old_backups

# Create report
create_report "$BACKUP_FILE"

# Success
echo ""
print_success "========================================="
print_success "  Backup completed successfully!"
print_success "========================================="
echo ""
print_msg "Backup file: $BACKUP_FILE"
print_msg "Backup size: $(du -h $BACKUP_FILE | cut -f1)"
echo ""
