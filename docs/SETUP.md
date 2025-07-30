# KishansKraft E-commerce Platform - Complete Developer Guide

## Table of Contents
- [Environment Setup](#environment-setup)
- [Project Structure](#project-structure)
- [API Documentation](#api-documentation)
- [Developer Console](#developer-console)
- [Troubleshooting](#troubleshooting)
- [Development Guidelines](#development-guidelines)

## Environment Setup

### System Requirements

**Server Requirements:**
- **Operating System**: Linux (Ubuntu 20.04+ recommended), Windows Server 2019+, or macOS 10.15+
- **Web Server**: Apache 2.4+ with mod_rewrite enabled OR Nginx 1.18+
- **PHP**: Version 8.0 or higher (8.1+ recommended)
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **SSL Certificate**: Required for production (Let's Encrypt recommended)

**Required PHP Extensions:**
```bash
# Core extensions (usually included)
php-cli php-fpm php-mysql php-json php-mbstring

# Additional required extensions
php-curl php-gd php-zip php-xml php-intl php-bcmath

# Optional but recommended
php-redis php-opcache php-imagick
```

### Step-by-Step LAMP Stack Setup

#### 1. Ubuntu/Debian Installation

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y
sudo systemctl start apache2
sudo systemctl enable apache2

# Install MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation

# Install PHP 8.1 and required extensions
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install php8.1 php8.1-fpm php8.1-mysql php8.1-cli php8.1-curl \
    php8.1-gd php8.1-mbstring php8.1-xml php8.1-zip php8.1-intl \
    php8.1-bcmath php8.1-json php8.1-opcache -y

# Enable Apache modules
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo systemctl restart apache2
```

#### 2. CentOS/RHEL Installation

```bash
# Install EPEL repository
sudo dnf install epel-release -y

# Install Apache
sudo dnf install httpd -y
sudo systemctl start httpd
sudo systemctl enable httpd

# Install MySQL
sudo dnf install mysql-server -y
sudo systemctl start mysqld
sudo systemctl enable mysqld
sudo mysql_secure_installation

# Install PHP 8.1
sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm -y
sudo dnf module enable php:remi-8.1 -y
sudo dnf install php php-mysqlnd php-curl php-gd php-mbstring \
    php-xml php-zip php-intl php-bcmath php-json php-opcache -y

# Configure firewall
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

#### 3. Windows Server Setup

```powershell
# Download and install XAMPP or WampServer
# Alternative: Use IIS with PHP Manager

# For IIS + PHP setup:
# 1. Enable IIS role with CGI support
# 2. Download PHP 8.1 NTS version
# 3. Install PHP Manager for IIS
# 4. Install MySQL Server
```

### Database Setup

#### 1. Create Database and User

```sql
-- Connect to MySQL as root
mysql -u root -p

-- Create database
CREATE DATABASE kishanskraft_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated user
CREATE USER 'kishanskraft_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON kishanskraft_db.* TO 'kishanskraft_user'@'localhost';
FLUSH PRIVILEGES;

-- Exit MySQL
EXIT;
```

#### 2. Import Database Schema

```bash
# Navigate to project directory
cd /var/www/html/kishanskraft

# Import schema and sample data
mysql -u kishanskraft_user -p kishanskraft_db < database/schema.sql

# Verify import
mysql -u kishanskraft_user -p kishanskraft_db -e "SHOW TABLES;"
```

### Application Installation

#### 1. Download and Extract Files

```bash
# Option A: Git clone (if using version control)
cd /var/www/html
sudo git clone <repository-url> kishanskraft

# Option B: Extract ZIP file
sudo unzip kishanskraft.zip -d /var/www/html/
sudo mv /var/www/html/kishanskraft-main /var/www/html/kishanskraft
```

#### 2. Set File Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/html/kishanskraft

# Set directory permissions
sudo find /var/www/html/kishanskraft -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/html/kishanskraft -type f -exec chmod 644 {} \;

# Set executable permissions for PHP files
sudo chmod +x /var/www/html/kishanskraft/router.php

# Set write permissions for logs and uploads
sudo chmod -R 777 /var/www/html/kishanskraft/logs
sudo mkdir -p /var/www/html/kishanskraft/uploads
sudo chmod -R 777 /var/www/html/kishanskraft/uploads
```

#### 3. Configure Application

```bash
# Copy example configuration
cd /var/www/html/kishanskraft
sudo cp backend/core/config.example.php backend/core/config.php

# Edit configuration
sudo nano backend/core/config.php
```

**Sample Configuration Values:**

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'kishanskraft_db');
define('DB_USER', 'kishanskraft_user');
define('DB_PASS', 'your_secure_password_here');

// Security Keys (generate unique values)
define('JWT_SECRET', 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6');
define('CSRF_SECRET', 'z9y8x7w6v5u4t3s2r1q0p9o8n7m6l5k4j3i2h1g0f9e8d7c6b5a4');
define('ENCRYPTION_KEY', 'abcd1234efgh5678ijkl9012mnop3456');

// Business Settings
define('COMPANY_EMAIL', 'info@yourdomain.com');
define('COMPANY_PHONE', '+91 9876543210');

// SMS Configuration (TextLocal)
define('TEXTLOCAL_API_KEY', 'your_textlocal_api_key_here');
define('TEXTLOCAL_SENDER', 'YOURID');

// Email Configuration
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'noreply@yourdomain.com');

// Environment
define('APP_ENV', 'production');
define('APP_DEBUG', false);
```

### Web Server Configuration

#### Apache Virtual Host Configuration

```apache
# Create virtual host file
sudo nano /etc/apache2/sites-available/kishanskraft.conf

<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html/kishanskraft
    
    <Directory /var/www/html/kishanskraft>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Frame-Options DENY
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/kishanskraft_error.log
    CustomLog ${APACHE_LOG_DIR}/kishanskraft_access.log combined
</VirtualHost>

# Enable site
sudo a2ensite kishanskraft.conf
sudo systemctl restart apache2
```

#### Nginx Configuration

```nginx
# Create Nginx configuration
sudo nano /etc/nginx/sites-available/kishanskraft

server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/html/kishanskraft;
    index router.php index.php;
    
    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Frame-Options DENY;
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    
    # Main location block
    location / {
        try_files $uri $uri/ /router.php?$query_string;
    }
    
    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index router.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }
    
    # Static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Security - deny access to sensitive files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ \.(log|sql|conf)$ {
        deny all;
        access_log off;
        log_not_found off;
    }
}

# Enable site
sudo ln -s /etc/nginx/sites-available/kishanskraft /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### SSL Certificate Setup

#### Using Let's Encrypt (Recommended)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# For Apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# For Nginx
sudo apt install python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal setup
sudo crontab -e
# Add this line:
0 2 * * * /usr/bin/certbot renew --quiet
```

#### Manual SSL Certificate

```apache
# Apache SSL Virtual Host
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/kishanskraft
    
    SSLEngine on
    SSLCertificateFile /path/to/your_domain.crt
    SSLCertificateKeyFile /path/to/your_domain.key
    SSLCertificateChainFile /path/to/chain.crt
    
    # Modern SSL configuration
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384
    SSLHonorCipherOrder off
    SSLSessionTickets off
    
    # HSTS
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
</VirtualHost>
```

### PHP Configuration Optimization

```ini
# Edit PHP configuration
sudo nano /etc/php/8.1/fpm/php.ini

# Recommended settings
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
max_input_time = 300
date.timezone = Asia/Kolkata

# Security settings
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

# Session security
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_only_cookies = 1
session.cookie_samesite = "Strict"

# OPcache configuration
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
opcache.fast_shutdown = 1

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
```

### Service Integration Setup

#### SMS Service (TextLocal) Configuration

1. **Sign up for TextLocal account:**
   - Visit https://www.textlocal.in/
   - Create account and verify phone number
   - Purchase SMS credits

2. **Get API credentials:**
   - Login to TextLocal dashboard
   - Go to Settings > API Keys
   - Generate new API key
   - Note your Sender ID

3. **Configure in application:**
   ```php
   define('TEXTLOCAL_API_KEY', 'your_actual_api_key');
   define('TEXTLOCAL_SENDER', 'KSKRFT'); // Your approved sender ID
   ```

#### Email Service (Gmail) Configuration

1. **Enable 2-Factor Authentication:**
   - Go to Google Account settings
   - Enable 2-Factor Authentication

2. **Generate App Password:**
   - Go to Google Account > Security
   - Select "App passwords"
   - Generate password for "Mail"

3. **Configure in application:**
   ```php
   define('SMTP_USERNAME', 'your-email@gmail.com');
   define('SMTP_PASSWORD', 'generated-app-password');
   ```

### Cron Jobs Setup

```bash
# Edit crontab
sudo crontab -e

# Add these cron jobs:

# Log rotation (daily at 2 AM)
0 2 * * * /usr/bin/find /var/www/html/kishanskraft/logs -name "*.log" -size +10M -exec /bin/mv {} {}.$(date +\%Y\%m\%d) \;

# Cleanup old logs (weekly)
0 3 * * 0 /usr/bin/find /var/www/html/kishanskraft/logs -name "*.log.*" -mtime +30 -delete

# Database backup (daily at 1 AM)
0 1 * * * /usr/bin/mysqldump -u kishanskraft_user -p'your_password' kishanskraft_db > /var/backups/kishanskraft_$(date +\%Y\%m\%d).sql

# Cleanup expired OTPs (every 5 minutes)
*/5 * * * * /usr/bin/php /var/www/html/kishanskraft/backend/cron/cleanup_otp.php

# Send pending email notifications (every minute)
* * * * * /usr/bin/php /var/www/html/kishanskraft/backend/cron/send_emails.php
```

### Testing Installation

#### 1. Basic Functionality Test

```bash
# Test web server response
curl -I http://yourdomain.com

# Test database connection
php -r "
try {
    \$pdo = new PDO('mysql:host=localhost;dbname=kishanskraft_db', 'kishanskraft_user', 'your_password');
    echo 'Database connection: SUCCESS\n';
} catch (Exception \$e) {
    echo 'Database connection: FAILED - ' . \$e->getMessage() . '\n';
}
"

# Test PHP extensions
php -m | grep -E '(curl|mbstring|mysql|json|xml)'

# Test file permissions
ls -la /var/www/html/kishanskraft/logs/
```

#### 2. API Endpoint Testing

```bash
# Test products API
curl -X GET "http://yourdomain.com/backend/api/products.php?action=list"

# Test auth API
curl -X POST "http://yourdomain.com/backend/api/auth.php" \
  -H "Content-Type: application/json" \
  -d '{"action":"send_otp","mobile":"9876543210"}'
```

### Performance Optimization

#### 1. Enable PHP OPcache

```bash
# Check if OPcache is enabled
php -i | grep opcache.enable

# If not enabled, add to php.ini:
echo "opcache.enable=1" | sudo tee -a /etc/php/8.1/fpm/php.ini
sudo systemctl restart php8.1-fpm
```

#### 2. Enable Gzip Compression

```apache
# Add to .htaccess or Apache configuration
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/json
</IfModule>
```

#### 3. Database Optimization

```sql
-- Add indexes for better performance
USE kishanskraft_db;

-- User table indexes
CREATE INDEX idx_users_mobile ON users(mobile);
CREATE INDEX idx_users_email ON users(email);

-- Product table indexes
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_status ON products(status);

-- Order table indexes
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_date ON orders(created_at);

-- Cart table indexes
CREATE INDEX idx_cart_user ON cart_items(user_id);
CREATE INDEX idx_cart_product ON cart_items(product_id);
```

### Backup Procedures

#### 1. Database Backup

```bash
#!/bin/bash
# Create backup script: /usr/local/bin/backup_kishanskraft.sh

BACKUP_DIR="/var/backups/kishanskraft"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="kishanskraft_db"
DB_USER="kishanskraft_user"
DB_PASS="your_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/db_backup_$DATE.sql

# Remove backups older than 30 days
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +30 -delete

echo "Database backup completed: db_backup_$DATE.sql.gz"
```

#### 2. Application Files Backup

```bash
#!/bin/bash
# Application backup script

APP_DIR="/var/www/html/kishanskraft"
BACKUP_DIR="/var/backups/kishanskraft"
DATE=$(date +%Y%m%d_%H%M%S)

# Create tar archive excluding logs and temporary files
tar -czf $BACKUP_DIR/app_backup_$DATE.tar.gz \
    --exclude='logs/*' \
    --exclude='uploads/temp/*' \
    -C /var/www/html kishanskraft

echo "Application backup completed: app_backup_$DATE.tar.gz"
```

### Log Rotation Setup

```bash
# Create logrotate configuration
sudo nano /etc/logrotate.d/kishanskraft

/var/www/html/kishanskraft/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    copytruncate
    postrotate
        # Restart PHP-FPM to reopen log files
        systemctl reload php8.1-fpm
    endscript
}
```

This completes the comprehensive environment setup documentation. The setup process covers all major server configurations, security considerations, and operational procedures needed for a production deployment of the KishansKraft platform.
