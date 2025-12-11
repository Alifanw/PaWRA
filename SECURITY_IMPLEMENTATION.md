# Security Implementation Guide - AirPanas Admin System

## 🔒 Security Checklist Implementation Status

### ✅ HIGH PRIORITY (IMPLEMENTED)

#### 1. Password Security
- **Status**: ✅ IMPLEMENTED
- **Implementation**:
  - Using Laravel's `bcrypt` hashing (PASSWORD_BCRYPT)
  - Password minimum 8 characters required
  - Hash stored in database, never plain text
- **Upgrade Path**: Can migrate to `argon2id` in production
```php
// In User model
protected function casts(): array {
    return ['password' => 'hashed'];
}
```

#### 2. Authentication & Session Management
- **Status**: ✅ IMPLEMENTED
- **Implementation**: Laravel Sanctum token-based authentication
- **Features**:
  - Secure token generation
  - Token revocation on logout
  - httpOnly cookies for SPA
  - Session regeneration on login
- **Config**: `config/sanctum.php`
```php
'expiration' => 60, // Token expires in 60 minutes
```

#### 3. Rate Limiting & Brute Force Protection
- **Status**: ✅ IMPLEMENTED
- **Implementation**:
  - Login: 5 attempts per minute per IP
  - Per username: 3 attempts per 5 minutes
  - Account lockout: 15 minutes temporary lock
- **Middleware**: `RateLimitLogin`
- **Location**: `app/Http/Middleware/RateLimitLogin.php`

#### 4. Authorization (RBAC)
- **Status**: ✅ IMPLEMENTED
- **Implementation**:
  - Role-based permissions
  - Permission caching (5 minutes)
  - Middleware-based permission checks
- **Middleware**: `CheckPermission`
- **Roles**: superadmin, admin, cashier, frontdesk, auditor
- **Permissions**: users.manage, products.manage, bookings.create, etc.

#### 5. Input Validation & SQL Injection Protection
- **Status**: ✅ IMPLEMENTED
- **Implementation**:
  - Laravel Form Request validation
  - Eloquent ORM (prepared statements)
  - No raw SQL queries without parameter binding
- **Example**: `LoginRequest` validation
```php
'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_]+$/'],
'password' => ['required', 'string', 'min:8'],
```

#### 6. Audit Logging
- **Status**: ✅ IMPLEMENTED
- **Implementation**:
  - All authentication events logged
  - Unauthorized access attempts logged
  - User actions on critical resources
- **Table**: `audit_logs`
- **Fields**: user_id, action, resource, ip_addr, user_agent, before_json, after_json

---

### ⚠️ MEDIUM PRIORITY (TO CONFIGURE)

#### 7. CSRF Protection
- **Status**: ⚠️ NEEDS CONFIGURATION
- **Laravel Default**: CSRF enabled for web routes
- **For API (SPA)**:
  - Use Sanctum's stateful configuration
  - Configure `sanctum.stateful` domains
- **Action Required**:
```php
// config/sanctum.php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 
    'localhost,127.0.0.1,airpanas.local'
)),
```

#### 8. Secure Headers
- **Status**: ⚠️ NEEDS WEB SERVER CONFIG
- **Required Headers**:
  - `Strict-Transport-Security` (HSTS)
  - `X-Frame-Options: DENY`
  - `X-Content-Type-Options: nosniff`
  - `Referrer-Policy: no-referrer-when-downgrade`
  - `Content-Security-Policy`

**Apache Configuration** (Add to VirtualHost):
```apache
<VirtualHost *:443>
    ServerName airpanas.local
    
    # TLS/SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/airpanas.crt
    SSLCertificateKeyFile /etc/ssl/private/airpanas.key
    SSLProtocol -all +TLSv1.2 +TLSv1.3
    SSLCipherSuite HIGH:!aNULL:!MD5
    
    # Security Headers
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
    Header always set X-Frame-Options "DENY"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "no-referrer-when-downgrade"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:;"
    Header always set X-XSS-Protection "1; mode=block"
    
    # Disable Directory Listing
    Options -Indexes
</VirtualHost>
```

**Nginx Configuration**:
```nginx
server {
    listen 443 ssl http2;
    server_name airpanas.local;
    
    # SSL Configuration
    ssl_certificate /etc/ssl/certs/airpanas.crt;
    ssl_certificate_key /etc/ssl/private/airpanas.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers off;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256';
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';" always;
    
    # Disable server version disclosure
    server_tokens off;
}
```

#### 9. HTTPS/TLS Enforcement
- **Status**: ⚠️ NEEDS CERTIFICATE
- **Action Required**:
  1. Obtain SSL/TLS certificate (Let's Encrypt recommended)
  2. Configure web server (see above)
  3. Force HTTPS redirect in Laravel:
```php
// app/Providers/AppServiceProvider.php
public function boot()
{
    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }
}
```

#### 10. Database Security
- **Status**: ⚠️ NEEDS DB USER CONFIG
- **Action Required**:
  1. Create dedicated DB user with limited privileges
  2. Grant only: SELECT, INSERT, UPDATE, DELETE
  3. NO DROP, CREATE, ALTER permissions for app user

```sql
-- Create dedicated user
CREATE USER 'airpanas_app'@'localhost' IDENTIFIED BY 'strong_password_here';

-- Grant minimal required permissions
GRANT SELECT, INSERT, UPDATE, DELETE 
ON airpanas.* 
TO 'airpanas_app'@'localhost';

FLUSH PRIVILEGES;

-- Update .env
DB_USERNAME=airpanas_app
DB_PASSWORD=strong_password_here
```

---

### 📊 LOW PRIORITY (OPERATIONAL)

#### 11. Dependency Scanning
- **Status**: ⚠️ MANUAL PROCESS
- **Tools to Use**:
  - `composer audit` (built-in)
  - Snyk.io
  - GitHub Dependabot
- **Regular Actions**:
```bash
# Check for vulnerable dependencies
composer audit

# Update dependencies
composer update
```

#### 12. Automated Backups
- **Status**: ⚠️ NEEDS CRON SETUP
- **Recommended Schedule**:
  - Full backup: Daily at 2 AM
  - Incremental: Every 6 hours
  - Retention: 30 days
- **Backup Script**:
```bash
#!/bin/bash
# /usr/local/bin/backup-airpanas.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/airpanas"
DB_NAME="airpanas"

# Database backup
mysqldump --single-transaction --routines --events \
  -u backup_user -p'backup_pass' \
  $DB_NAME | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Files backup
tar -czf "$BACKUP_DIR/files_$DATE.tar.gz" /var/www/airpanas/storage

# Delete backups older than 30 days
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

**Crontab**:
```cron
0 2 * * * /usr/local/bin/backup-airpanas.sh >> /var/log/airpanas-backup.log 2>&1
```

#### 13. Monitoring & Alerting
- **Status**: ⚠️ NOT CONFIGURED
- **Recommended Tools**:
  - Sentry (error tracking)
  - Prometheus + Grafana (metrics)
  - Laravel Telescope (development)
- **Quick Setup** (Sentry):
```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=your-sentry-dsn
```

---

## 🛡️ Additional Security Measures

### File Upload Security
If implementing file uploads:
```php
// Validation example
$request->validate([
    'file' => 'required|file|mimes:jpg,png,pdf|max:2048', // 2MB max
]);

// Store outside public directory
$path = $request->file('file')->store('uploads', 'private');

// Scan with ClamAV (if available)
```

### Environment Variables
- **CRITICAL**: Never commit `.env` to version control
- Use strong random values:
```bash
php artisan key:generate  # APP_KEY
php artisan config:cache
```

### Session Security
```php
// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'strict',
```

### CORS Configuration
```php
// config/cors.php
'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'https://airpanas.local')),
'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

---

## 🔍 Security Testing Checklist

### Manual Tests
- [ ] Login with wrong password (should fail after 5 attempts)
- [ ] Access protected endpoint without token (should return 401)
- [ ] Access restricted resource (should return 403)
- [ ] SQL injection attempts (should be sanitized)
- [ ] XSS attempts in forms (should be escaped)
- [ ] CSRF token validation
- [ ] Session fixation test
- [ ] Password reset flow security

### Automated Tests
```bash
# Run PHPUnit security tests
php artisan test --filter=SecurityTest

# Static analysis
./vendor/bin/phpstan analyse

# Code style & security
./vendor/bin/pint
```

### Penetration Testing
- **Tools**: OWASP ZAP, Burp Suite
- **Frequency**: Before production, then quarterly
- **Focus Areas**:
  - Authentication bypass
  - Authorization flaws
  - Injection vulnerabilities
  - Broken access control

---

## 📋 Pre-Production Security Checklist

- [ ] Change all default passwords
- [ ] Configure HTTPS/TLS with valid certificate
- [ ] Set secure headers in web server
- [ ] Create limited-privilege database user
- [ ] Enable audit logging
- [ ] Configure automated backups
- [ ] Set up error monitoring (Sentry)
- [ ] Review `.env` for production values
- [ ] Disable debug mode (`APP_DEBUG=false`)
- [ ] Configure CORS properly
- [ ] Run security audit (`composer audit`)
- [ ] Perform penetration test
- [ ] Document incident response plan

---

## 🚨 Incident Response

### In Case of Security Breach:
1. **Isolate**: Take affected system offline
2. **Assess**: Determine scope of breach
3. **Contain**: Revoke all active tokens, force password reset
4. **Remediate**: Fix vulnerability
5. **Document**: Log all actions in audit trail
6. **Notify**: Inform stakeholders as required
7. **Review**: Post-mortem analysis

### Emergency Contacts
- System Admin: [contact info]
- Database Admin: [contact info]
- Security Team: [contact info]

---

## 📚 References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [CWE Top 25](https://cwe.mitre.org/top25/)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
