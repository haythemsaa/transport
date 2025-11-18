#!/bin/bash

#######################################################
# Teleroute Marketplace - Server Setup Script
# Usage: sudo ./setup.sh
# This script sets up a fresh server for the application
#######################################################

set -e  # Exit on error

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Configuration
APP_NAME="teleroute-marketplace"
APP_DIR="/var/www/${APP_NAME}"
DB_NAME="teleroute_marketplace"
DB_USER="teleroute_user"
DB_PASS=$(openssl rand -base64 32)
DOMAIN="teleroute-marketplace.com"

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

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run this script as root (sudo ./setup.sh)"
    exit 1
fi

print_msg "Starting server setup for Teleroute Marketplace..."

# Step 1: Update system
print_msg "Updating system packages..."
apt-get update
apt-get upgrade -y
print_success "System updated"

# Step 2: Install Apache
print_msg "Installing Apache web server..."
apt-get install -y apache2
systemctl enable apache2
systemctl start apache2
print_success "Apache installed and started"

# Step 3: Install PHP and extensions
print_msg "Installing PHP 8.2 and extensions..."
apt-get install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get install -y \
    php8.2 \
    php8.2-cli \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-pdo \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-gd \
    php8.2-bcmath \
    php8.2-intl \
    libapache2-mod-php8.2

print_success "PHP 8.2 installed"

# Step 4: Install MySQL
print_msg "Installing MySQL server..."
apt-get install -y mysql-server
systemctl enable mysql
systemctl start mysql
print_success "MySQL installed and started"

# Step 5: Secure MySQL installation
print_msg "Configuring MySQL..."
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
print_success "MySQL database and user created"

# Step 6: Install Composer
print_msg "Installing Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
print_success "Composer installed"

# Step 7: Install Node.js and NPM
print_msg "Installing Node.js and NPM..."
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt-get install -y nodejs
print_success "Node.js $(node --version) and NPM $(npm --version) installed"

# Step 8: Install Git
print_msg "Installing Git..."
apt-get install -y git
print_success "Git installed"

# Step 9: Install additional tools
print_msg "Installing additional tools..."
apt-get install -y \
    curl \
    wget \
    unzip \
    vim \
    htop \
    certbot \
    python3-certbot-apache
print_success "Additional tools installed"

# Step 10: Configure Apache
print_msg "Configuring Apache..."

# Enable required modules
a2enmod rewrite
a2enmod headers
a2enmod expires
a2enmod deflate
a2enmod ssl

# Create virtual host
cat > "/etc/apache2/sites-available/${APP_NAME}.conf" << EOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    ServerAlias www.${DOMAIN}
    ServerAdmin admin@${DOMAIN}

    DocumentRoot ${APP_DIR}

    <Directory ${APP_DIR}>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Logs
    ErrorLog \${APACHE_LOG_DIR}/${APP_NAME}-error.log
    CustomLog \${APACHE_LOG_DIR}/${APP_NAME}-access.log combined

    # Security headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
EOF

# Enable site
a2dissite 000-default.conf
a2ensite ${APP_NAME}.conf

# Restart Apache
systemctl restart apache2
print_success "Apache configured"

# Step 11: Create application directory
print_msg "Creating application directory..."
mkdir -p "$APP_DIR"
mkdir -p "$APP_DIR/uploads"
mkdir -p "$APP_DIR/logs"
mkdir -p "$APP_DIR/cache"
chown -R www-data:www-data "$APP_DIR"
print_success "Application directory created"

# Step 12: Configure PHP
print_msg "Configuring PHP..."
PHP_INI="/etc/php/8.2/apache2/php.ini"

# Update PHP settings
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 20M/' "$PHP_INI"
sed -i 's/post_max_size = .*/post_max_size = 20M/' "$PHP_INI"
sed -i 's/memory_limit = .*/memory_limit = 256M/' "$PHP_INI"
sed -i 's/max_execution_time = .*/max_execution_time = 300/' "$PHP_INI"
sed -i 's/;date.timezone.*/date.timezone = Europe\/Paris/' "$PHP_INI"
sed -i 's/display_errors = .*/display_errors = Off/' "$PHP_INI"
sed -i 's/log_errors = .*/log_errors = On/' "$PHP_INI"

systemctl restart apache2
print_success "PHP configured"

# Step 13: Setup firewall
print_msg "Configuring firewall..."
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 22/tcp
ufw --force enable
print_success "Firewall configured"

# Step 14: Setup cron for backups
print_msg "Setting up automated backups..."
CRON_FILE="/etc/cron.d/${APP_NAME}-backup"
cat > "$CRON_FILE" << EOF
# Teleroute Marketplace - Daily backup at 2:00 AM
0 2 * * * www-data ${APP_DIR}/scripts/backup.sh full >> ${APP_DIR}/logs/backup.log 2>&1
EOF
chmod 644 "$CRON_FILE"
print_success "Automated backups configured"

# Step 15: Create .env file template
print_msg "Creating environment configuration..."
cat > "${APP_DIR}/.env.example" << EOF
# Database Configuration
DB_HOST=localhost
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASS=${DB_PASS}

# Application Configuration
APP_ENV=production
APP_DEBUG=false
APP_URL=https://${DOMAIN}

# Session Configuration
SESSION_LIFETIME=7200
SESSION_SECURE=true

# Mail Configuration
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@${DOMAIN}
MAIL_FROM_NAME="Teleroute Marketplace"

# S3 Configuration (for file uploads)
S3_KEY=
S3_SECRET=
S3_REGION=
S3_BUCKET=

# Stripe Configuration (for payments)
STRIPE_PUBLIC_KEY=
STRIPE_SECRET_KEY=
STRIPE_WEBHOOK_SECRET=
EOF

# Copy to actual .env
cp "${APP_DIR}/.env.example" "${APP_DIR}/.env"
chown www-data:www-data "${APP_DIR}/.env"
chmod 600 "${APP_DIR}/.env"
print_success "Environment file created"

# Step 16: Save credentials
CREDS_FILE="/root/.${APP_NAME}-credentials"
cat > "$CREDS_FILE" << EOF
========================================
Teleroute Marketplace - Credentials
========================================
Generated: $(date)

Database:
  Host: localhost
  Name: ${DB_NAME}
  User: ${DB_USER}
  Pass: ${DB_PASS}

Application:
  Path: ${APP_DIR}
  Domain: ${DOMAIN}
  Environment File: ${APP_DIR}/.env

Commands:
  Deploy: ${APP_DIR}/scripts/deploy.sh
  Backup: ${APP_DIR}/scripts/backup.sh
  Logs: tail -f ${APP_DIR}/logs/app-$(date +%Y-%m-%d).log

Next Steps:
  1. Clone your repository to ${APP_DIR}
  2. Import database schema: mysql -u${DB_USER} -p${DB_PASS} ${DB_NAME} < ${APP_DIR}/database/schema.sql
  3. Configure SSL: certbot --apache -d ${DOMAIN} -d www.${DOMAIN}
  4. Update .env file with production values
  5. Set proper permissions: chown -R www-data:www-data ${APP_DIR}

========================================
EOF

chmod 600 "$CREDS_FILE"
print_success "Credentials saved to $CREDS_FILE"

# Completion
echo ""
print_success "========================================="
print_success "  Server setup completed!"
print_success "========================================="
echo ""
print_msg "Credentials file: $CREDS_FILE"
print_msg "Application path: $APP_DIR"
print_msg "Apache config: /etc/apache2/sites-available/${APP_NAME}.conf"
echo ""
print_warning "IMPORTANT: Save the database password from $CREDS_FILE"
print_warning "Next: Clone your repository and run the deployment script"
echo ""
cat "$CREDS_FILE"
