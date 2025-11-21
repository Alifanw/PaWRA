# Deployment Checklist

## Pre-Deployment Configuration

### 1. Environment Configuration
```bash
# Copy and configure .env
cp .env.example .env

# Update these critical values:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
DB_PASSWORD=your_secure_password
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm ci

# Build frontend assets
npm run build
```

### 3. Database Setup
```bash
# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed initial data (roles, permissions, default users)
php artisan db:seed --force
```

### 4. Optimize for Production
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache
```

### 5. File Permissions
```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/airpanas

# Set directory permissions
sudo find /var/www/airpanas -type d -exec chmod 755 {} \;
sudo find /var/www/airpanas -type f -exec chmod 644 {} \;

# Storage and cache writable
sudo chmod -R 775 /var/www/airpanas/storage
sudo chmod -R 775 /var/www/airpanas/bootstrap/cache
```

## Apache Configuration

### Enable Required Modules
```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo systemctl restart apache2
```

### Virtual Host Configuration
Create `/etc/apache2/sites-available/airpanas.conf`:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    
    # Redirect all HTTP to HTTPS
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    ServerAdmin admin@yourdomain.com

    DocumentRoot /var/www/airpanas/public

    <Directory /var/www/airpanas/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    Header always set X-Frame-Options "DENY"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
    
    # Content Security Policy
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'none';"

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:!aNULL:!MD5:!3DES
    SSLHonorCipherOrder on

    # Logging
    ErrorLog ${APACHE_LOG_DIR}/airpanas-error.log
    CustomLog ${APACHE_LOG_DIR}/airpanas-access.log combined
</VirtualHost>
```

Enable site and restart:
```bash
sudo a2ensite airpanas
sudo systemctl restart apache2
```

## SSL/TLS with Let's Encrypt

```bash
# Install Certbot
sudo apt update
sudo apt install certbot python3-certbot-apache

# Obtain certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal (runs twice daily)
sudo systemctl status certbot.timer
```

## Database Security

### Create dedicated MySQL user:
```sql
CREATE DATABASE airpanas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'airpanas_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON airpanas_db.* TO 'airpanas_user'@'localhost';
FLUSH PRIVILEGES;
```

### Secure MySQL:
```bash
sudo mysql_secure_installation
```

## Redis Setup (for caching)

```bash
# Install Redis
sudo apt install redis-server

# Configure Redis
sudo nano /etc/redis/redis.conf
# Set: bind 127.0.0.1
# Set: requirepass your_redis_password

# Restart Redis
sudo systemctl restart redis-server
sudo systemctl enable redis-server

# Update .env
CACHE_STORE=redis
REDIS_PASSWORD=your_redis_password
```

## Monitoring & Logs

### Log Rotation
Create `/etc/logrotate.d/airpanas`:
```
/var/www/airpanas/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        php /var/www/airpanas/artisan cache:clear > /dev/null 2>&1
    endscript
}
```

### Setup Cron Jobs
```bash
sudo crontab -e -u www-data
```

Add:
```cron
# Laravel Scheduler
* * * * * cd /var/www/airpanas && php artisan schedule:run >> /dev/null 2>&1

# Database Backup (daily at 2 AM)
0 2 * * * /usr/local/bin/backup-airpanas-db.sh
```

### Database Backup Script
Create `/usr/local/bin/backup-airpanas-db.sh`:
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/airpanas"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="airpanas_db"
DB_USER="airpanas_user"
DB_PASS="your_secure_password"

mkdir -p $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -type f -mtime +30 -delete
```

Make executable:
```bash
sudo chmod +x /usr/local/bin/backup-airpanas-db.sh
```

## Health Checks

### Test Endpoints
```bash
# Health check
curl -I https://yourdomain.com

# API endpoint (should return 401 without token)
curl https://yourdomain.com/api/products

# HSTS header verification
curl -I https://yourdomain.com | grep -i strict-transport

# SSL/TLS verification
openssl s_client -connect yourdomain.com:443 -tls1_3
```

### Performance Optimization
```bash
# Enable OPcache (edit php.ini)
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

## Default Users (Change passwords immediately)

| Username | Password | Role |
|----------|----------|------|
| superadmin | Admin123! | Superadmin |
| admin | Admin123! | Admin |
| cashier | Cashier123! | Cashier |

**CRITICAL**: Change all default passwords after first login:
```bash
php artisan tinker
>>> $user = User::where('username', 'superadmin')->first();
>>> $user->password = Hash::make('your_new_secure_password');
>>> $user->save();
```

## Security Checklist

- [ ] APP_DEBUG=false in .env
- [ ] Strong APP_KEY generated
- [ ] Database credentials secured
- [ ] Redis password set
- [ ] SSL/TLS certificate installed
- [ ] HSTS enabled
- [ ] Security headers configured
- [ ] File permissions correct (755/644)
- [ ] storage/ and bootstrap/cache/ writable
- [ ] Default passwords changed
- [ ] Firewall configured (only 80, 443, 22)
- [ ] SSH key-based authentication
- [ ] Database backups automated
- [ ] Log rotation configured
- [ ] Error pages customized
- [ ] Rate limiting tested

## Post-Deployment Testing

```bash
# Test login rate limiting
for i in {1..6}; do curl -X POST https://yourdomain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"wrong","password":"wrong"}'; done

# Test permission middleware
curl https://yourdomain.com/api/products \
  -H "Authorization: Bearer invalid_token"

# Test audit logging
# Login and check audit_logs table for entry
```

## Rollback Plan

If deployment fails:
```bash
# Restore database backup
gunzip < /var/backups/airpanas/backup_YYYYMMDD_HHMMSS.sql.gz | mysql -u airpanas_user -p airpanas_db

# Revert code
git checkout previous_stable_tag

# Rebuild
composer install --no-dev
npm run build
php artisan config:cache
```

## Support Contacts

- **System Admin**: admin@yourdomain.com
- **Database**: DBA contact
- **Security Issues**: security@yourdomain.com
