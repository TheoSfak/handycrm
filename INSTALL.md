# HandyCRM v1.3.5 - Installation Guide

## 📋 Prerequisites

Before installing HandyCRM, ensure your system meets these requirements:

- **PHP**: 7.4 or higher (8.0+ recommended)
- **MySQL**: 5.7+ or MariaDB 10.2+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP Extensions**:
  - `mysqli` or `pdo_mysql`
  - `mbstring`
  - `json`
  - `session`
  - `gd` (for image uploads)
- **Disk Space**: 100MB minimum (500MB recommended for uploads)

## 🚀 Fresh Installation (New Users)

### Step 1: Download HandyCRM

**Option A: Download ZIP from GitHub**
```bash
# Download from: https://github.com/TheoSfak/handycrm/releases/download/v1.3.5/handycrm-v1.3.5.zip
# Extract to your web server directory
unzip handycrm-v1.3.5.zip -d /var/www/html/handycrm
cd /var/www/html/handycrm
```

**Option B: Clone from Git**
```bash
git clone https://github.com/TheoSfak/handycrm.git
cd handycrm
git checkout v1.3.5
```

### Step 2: Create Database

```sql
-- Connect to MySQL as root or admin user
mysql -u root -p

-- Create database with proper charset
CREATE DATABASE handycrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated user (recommended for security)
CREATE USER 'handycrm_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON handycrm.* TO 'handycrm_user'@'localhost';
FLUSH PRIVILEGES;
```

### Step 3: Import Database Schema

```bash
# Import the main schema
mysql -u handycrm_user -p handycrm < database/schema.sql

# Import sample data (optional - includes demo customers/projects)
mysql -u handycrm_user -p handycrm < database/sample_data.sql
```

### Step 4: Configure Application

```bash
# Copy example config
cp config/config.php.example config/config.php

# Edit configuration
nano config/config.php
```

Update these values in `config/config.php`:

```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'handycrm');
define('DB_USER', 'handycrm_user');
define('DB_PASS', 'your_secure_password');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('BASE_URL', 'http://localhost/handycrm'); // Change to your domain
define('SITE_NAME', 'HandyCRM');
define('TIMEZONE', 'Europe/Athens');

// Security Settings
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('ENABLE_CSRF', true);

// File Upload Settings
define('UPLOAD_MAX_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
```

### Step 5: Set Permissions

```bash
# Create uploads directory
mkdir -p uploads/projects uploads/tasks

# Set proper permissions
chmod 755 uploads
chmod 755 uploads/projects
chmod 755 uploads/tasks

# If using Apache, ensure proper ownership
chown -R www-data:www-data uploads
```

### Step 6: Configure Web Server

**Apache (.htaccess already included)**
```apache
# Enable mod_rewrite
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

**Nginx (create server block)**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/handycrm;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Step 7: First Login

1. Navigate to `http://localhost/handycrm` (or your configured URL)
2. **Default Admin Credentials**:
   - Username: `admin`
   - Password: `admin123`
3. **⚠️ IMPORTANT**: Change the admin password immediately!
   - Go to: Settings → Users → Edit Admin User

### Step 8: Initial Setup

1. **Update Company Information**:
   - Go to: Settings → Company Settings
   - Add your company name, logo, contact details

2. **Create Users**:
   - Go to: Settings → Users → Add New User
   - Available roles:
     - **Admin**: Full system access
     - **Supervisor**: Projects, materials, own profile
     - **Technician**: Own profile only
     - **Assistant**: Own profile only

3. **Import Materials Catalog** (optional):
   - Go to: Materials → Import
   - Or manually add your commonly used materials

4. **Configure Email** (optional):
   - Edit `config/email.php`
   - Set up SMTP for appointment reminders

## 🔄 Upgrading from Previous Version

### For Users Already Running HandyCRM

If you're upgrading from v1.3.0 or earlier:

### Step 1: Backup Everything

```bash
# Backup database
mysqldump -u handycrm_user -p handycrm > backup_before_1.3.5.sql

# Backup files
tar -czf handycrm_backup_$(date +%Y%m%d).tar.gz /var/www/html/handycrm
```

### Step 2: Update Files

**Option A: Git Pull**
```bash
cd /var/www/html/handycrm
git fetch origin
git checkout v1.3.5
```

**Option B: Manual Update**
```bash
# Download new files
# Extract and copy over existing installation
# Keep your config.php and uploads/ directory
```

### Step 3: Run Migrations (AUTOMATIC)

🎉 **Good News**: Migrations run automatically on next login!

The system will:
- Detect new migrations
- Execute them in the background
- Log any errors to `logs/migration_errors.log`

**Manual Migration (if needed)**:
```bash
mysql -u handycrm_user -p handycrm < migrations/migrate_to_1.3.5.sql
```

### Step 4: Clear Cache

```bash
# If using PHP OPcache
sudo systemctl restart php8.0-fpm

# If using Apache
sudo systemctl restart apache2

# Browser cache
# Press Ctrl+F5 to hard refresh
```

### Step 5: Verify Upgrade

1. Login to the system
2. Check: Settings → About → Version should show **1.3.5**
3. Check: Settings → Users → User roles should include "Supervisor"
4. Check: Payments page should show new features (statistics, bulk actions)

## 🆕 What's New in v1.3.5

### Payment Management
- ✅ Summary statistics card with grand totals
- ✅ Quick date preset buttons
- ✅ CSV export functionality
- ✅ Bulk payment marking
- ✅ Visual progress bars and color coding
- ✅ Role badges next to names

### Role-Based Access Control
- ✅ 4-tier role system (Admin, Supervisor, Technician, Assistant)
- ✅ Dynamic menu based on user role
- ✅ Permission guards in controllers
- ✅ Supervisor role for team leaders

### Bug Fixes
- ✅ Fixed duplicate technician cards
- ✅ Supervisors now included in payment lists
- ✅ Correct amount display in bulk payment modal
- ✅ Improved UI color contrast

## 🐛 Troubleshooting

### Database Connection Error
```
Error: Could not connect to database
```
**Solution**:
- Check `config/config.php` credentials
- Verify MySQL service is running: `sudo systemctl status mysql`
- Check firewall rules

### Permission Denied on Uploads
```
Warning: move_uploaded_file(): failed to open stream
```
**Solution**:
```bash
sudo chown -R www-data:www-data uploads
sudo chmod -R 755 uploads
```

### Migrations Not Running
**Solution**:
```bash
# Check migrations table exists
mysql -u handycrm_user -p handycrm -e "SHOW TABLES LIKE 'migrations';"

# Manually run migration
mysql -u handycrm_user -p handycrm < migrations/migrate_to_1.3.5.sql
```

### Page Not Found (404)
**Solution**:
- Check `.htaccess` file exists
- Enable mod_rewrite: `sudo a2enmod rewrite`
- Check Apache virtual host has `AllowOverride All`

## 📞 Support

- **Documentation**: [README.md](README.md)
- **Changelog**: [CHANGELOG.md](CHANGELOG.md)
- **Issues**: https://github.com/TheoSfak/handycrm/issues
- **Email**: theodore.sfakianakis@gmail.com

## 📄 License

© 2025 Theodore Sfakianakis. All rights reserved.

## 🙏 Acknowledgments

Thank you for choosing HandyCRM! If you find it useful, please:
- ⭐ Star the repository on GitHub
- 🐛 Report bugs or suggest features
- 📢 Share with other technicians/contractors
