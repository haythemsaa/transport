#!/bin/bash

#######################################################
# Database Initialization Script
# Initializes the database for Teleroute Marketplace
#######################################################

set -e  # Exit on error

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

print_msg() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

# Configuration
DB_NAME="${DB_NAME:-teleroute_marketplace}"
DB_USER="${DB_USER:-teleroute_user}"
DB_PASS="${DB_PASS:-}"
DB_HOST="${DB_HOST:-localhost}"
SCHEMA_FILE="$(dirname $0)/schema.sql"

print_msg "Starting database initialization..."

# Check if schema file exists
if [ ! -f "$SCHEMA_FILE" ]; then
    print_error "Schema file not found: $SCHEMA_FILE"
    exit 1
fi

# Prompt for database password if not set
if [ -z "$DB_PASS" ]; then
    echo -n "Enter database password for $DB_USER: "
    read -s DB_PASS
    echo
fi

# Check MySQL connection
print_msg "Testing MySQL connection..."
if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1" > /dev/null 2>&1; then
    print_success "MySQL connection successful"
else
    print_error "Failed to connect to MySQL"
    exit 1
fi

# Create database if not exists
print_msg "Creating database if not exists..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" << EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EOF
print_success "Database $DB_NAME created/verified"

# Import schema
print_msg "Importing database schema..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SCHEMA_FILE"
print_success "Schema imported successfully"

# Verify tables
print_msg "Verifying tables..."
TABLE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES" | wc -l)
EXPECTED_TABLES=12

if [ $TABLE_COUNT -ge $EXPECTED_TABLES ]; then
    print_success "All $TABLE_COUNT tables created successfully"
else
    print_error "Expected at least $EXPECTED_TABLES tables, found $TABLE_COUNT"
    exit 1
fi

# Create initial admin user (optional)
read -p "Create an admin user? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo -n "Admin email: "
    read ADMIN_EMAIL
    echo -n "Admin password: "
    read -s ADMIN_PASS
    echo

    # Hash password with PHP
    HASHED_PASS=$(php -r "echo password_hash('$ADMIN_PASS', PASSWORD_BCRYPT);")

    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << EOF
INSERT INTO users (user_type, company_name, email, password, created_at)
VALUES ('both', 'Administrator', '$ADMIN_EMAIL', '$HASHED_PASS', NOW())
ON DUPLICATE KEY UPDATE email=email;
EOF
    print_success "Admin user created: $ADMIN_EMAIL"
fi

# Display summary
echo ""
print_success "========================================="
print_success "  Database initialization complete!"
print_success "========================================="
echo ""
print_msg "Database: $DB_NAME"
print_msg "Tables created: $TABLE_COUNT"
print_msg "Host: $DB_HOST"
echo ""
print_msg "Next steps:"
echo "  1. Update your .env file with database credentials"
echo "  2. Run: php generate-test-data.php (optional)"
echo "  3. Start your web server"
echo ""
