# HandyCRM - Installation Guide

## System Requirements
- PHP 8.1 or higher
- MySQL 5.7 or higher / MariaDB 10.3+
- Apache with mod_rewrite enabled
- SSL Certificate (recommended for production)

## Installation Steps

### 1. Upload Files
Upload all files to your web server's document root or subdirectory.

### 2. Database Setup
1. Create a new MySQL database
2. Import the database schema using `database/handycrm.sql`
3. Create a database user with full privileges on the database

### 3. Configuration
1. Copy `config/config.example.php` to `config/config.php`
2. Edit `config/config.php` with your database credentials:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');

// Application URL (update for production)
define('APP_URL', 'https://yourdomain.com');
define('BASE_PATH', '/'); // or '/subdirectory/' if in subfolder
```

### 4. File Permissions
Set proper permissions:
```bash
chmod 755 uploads/
chmod 644 config/config.php
chmod 644 .htaccess
```

### 5. Apache Configuration
Ensure mod_rewrite is enabled and .htaccess files are allowed:

```apache
<Directory "/path/to/handycrm">
    AllowOverride All
    Require all granted
</Directory>
```

### 6. SSL Configuration (Production)
Update config.php for HTTPS:
```php
define('FORCE_HTTPS', true);
```

### 7. Initial Admin User
Default admin credentials (change immediately):
- Email: admin@handycrm.com
- Password: admin123

## Security Checklist
- [ ] Change default admin password
- [ ] Update database credentials
- [ ] Enable HTTPS/SSL
- [ ] Set proper file permissions
- [ ] Configure backup strategy
- [ ] Update APP_URL in config

## Features Included
✅ Customer Management with SEO-friendly URLs
✅ Project Management with slugs
✅ Invoice Management (INV-YYYY-NNNN format)
✅ Quote Management (QUO-YYYY-NNNN format)
✅ Appointment Scheduling
✅ Material Inventory
✅ Reports & Analytics
✅ User Management
✅ Mobile Responsive Design
✅ Clean URL Structure (SEO-ready)

## URL Structure
- Customers: `/customers/customer-name`
- Projects: `/projects/project-slug`
- Invoices: `/invoices/inv-2025-0001`
- Quotes: `/quotes/quo-2025-0001`

## Support
For technical support, contact: support@handycrm.com