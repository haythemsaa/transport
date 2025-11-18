#!/bin/bash

#######################################################
# Teleroute Marketplace - Deployment Script
# Usage: ./deploy.sh [environment]
# Environments: production, staging, development
#######################################################

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
ENVIRONMENT=${1:-production}
APP_DIR="/var/www/teleroute-marketplace"
BACKUP_DIR="/var/backups/teleroute"
GIT_REPO="https://github.com/your-repo/teleroute-marketplace.git"
GIT_BRANCH=${2:-main}

# Function to print colored messages
print_msg() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

# Check if running as correct user
if [ "$EUID" -eq 0 ]; then
    print_error "Please do not run this script as root"
    exit 1
fi

print_msg "Starting deployment to ${ENVIRONMENT}..."

# Step 1: Backup current installation
print_msg "Creating backup..."
if [ -d "$APP_DIR" ]; then
    BACKUP_FILE="${BACKUP_DIR}/backup_$(date +%Y%m%d_%H%M%S).tar.gz"
    mkdir -p "$BACKUP_DIR"
    tar -czf "$BACKUP_FILE" -C "$APP_DIR" .
    print_success "Backup created: $BACKUP_FILE"
else
    print_warning "No existing installation found, skipping backup"
fi

# Step 2: Enable maintenance mode
print_msg "Enabling maintenance mode..."
if [ -d "$APP_DIR" ]; then
    touch "$APP_DIR/maintenance.flag"
    echo "<?php header('HTTP/1.1 503 Service Temporarily Unavailable'); echo 'Site en maintenance, retour dans quelques minutes...'; exit; ?>" > "$APP_DIR/maintenance.php"
    print_success "Maintenance mode enabled"
fi

# Step 3: Pull latest code
print_msg "Pulling latest code from Git..."
if [ -d "$APP_DIR/.git" ]; then
    cd "$APP_DIR"
    git fetch origin
    git checkout "$GIT_BRANCH"
    git pull origin "$GIT_BRANCH"
    print_success "Code updated from branch: $GIT_BRANCH"
else
    print_msg "Cloning repository..."
    git clone -b "$GIT_BRANCH" "$GIT_REPO" "$APP_DIR"
    cd "$APP_DIR"
    print_success "Repository cloned"
fi

# Step 4: Install/Update dependencies
if [ -f "$APP_DIR/composer.json" ]; then
    print_msg "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
    print_success "Composer dependencies installed"
fi

if [ -f "$APP_DIR/package.json" ]; then
    print_msg "Installing NPM dependencies..."
    npm ci --production
    print_success "NPM dependencies installed"
fi

# Step 5: Run database migrations
print_msg "Running database migrations..."
if [ -f "$APP_DIR/database/migrations.php" ]; then
    php "$APP_DIR/database/migrations.php"
    print_success "Database migrations completed"
else
    print_warning "No migration script found"
fi

# Step 6: Clear and warm up caches
print_msg "Clearing caches..."
if [ -d "$APP_DIR/cache" ]; then
    rm -rf "$APP_DIR/cache/*"
    print_success "Cache cleared"
fi

# Step 7: Set correct permissions
print_msg "Setting permissions..."
chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR" -type d -exec chmod 755 {} \;
find "$APP_DIR" -type f -exec chmod 644 {} \;
chmod -R 775 "$APP_DIR/uploads"
chmod -R 775 "$APP_DIR/logs"
chmod -R 775 "$APP_DIR/cache" 2>/dev/null || true
print_success "Permissions set"

# Step 8: Restart services
print_msg "Restarting services..."
sudo systemctl reload apache2 || sudo systemctl reload nginx
sudo systemctl restart php7.4-fpm || sudo systemctl restart php8.0-fpm || sudo systemctl restart php8.2-fpm || true
print_success "Services restarted"

# Step 9: Disable maintenance mode
print_msg "Disabling maintenance mode..."
rm -f "$APP_DIR/maintenance.flag"
rm -f "$APP_DIR/maintenance.php"
print_success "Maintenance mode disabled"

# Step 10: Verify deployment
print_msg "Verifying deployment..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/)
if [ "$HTTP_CODE" -eq 200 ]; then
    print_success "Deployment successful! HTTP Status: $HTTP_CODE"
else
    print_error "Deployment may have issues. HTTP Status: $HTTP_CODE"
    print_warning "Rolling back..."

    # Rollback
    if [ -n "$BACKUP_FILE" ] && [ -f "$BACKUP_FILE" ]; then
        rm -rf "$APP_DIR"/*
        tar -xzf "$BACKUP_FILE" -C "$APP_DIR"
        print_success "Rolled back to previous version"
    fi
    exit 1
fi

# Step 11: Cleanup old backups (keep last 10)
print_msg "Cleaning up old backups..."
cd "$BACKUP_DIR"
ls -t backup_*.tar.gz | tail -n +11 | xargs -r rm
print_success "Old backups cleaned"

# Deployment complete
echo ""
print_success "========================================="
print_success "  Deployment to ${ENVIRONMENT} completed!"
print_success "========================================="
echo ""
print_msg "Application URL: http://localhost"
print_msg "Environment: ${ENVIRONMENT}"
print_msg "Branch: ${GIT_BRANCH}"
print_msg "Backup: ${BACKUP_FILE:-N/A}"
echo ""
