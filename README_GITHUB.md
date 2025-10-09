# HandyCRM - Professional CRM System

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/yourusername/handycrm)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

## 📋 Overview

HandyCRM is a professional Customer Relationship Management system designed for small to medium businesses. Built with modern PHP and featuring SEO-friendly URLs, it helps manage customers, projects, invoices, and quotes efficiently.

## ✨ Features

- 🎯 **Customer Management** - Complete customer profiles with contact information
- 📊 **Project Tracking** - Monitor projects with status updates and notes
- 💰 **Invoice System** - Create and manage invoices with automatic calculations
- 📝 **Quote Generation** - Professional quotes with customizable templates
- 📈 **Dashboard Analytics** - Real-time revenue and project statistics
- 🔒 **Secure Authentication** - Password hashing and CSRF protection
- 🌐 **SEO-Friendly URLs** - Clean URLs like `/customers/john-doe`
- 🇬🇷 **Greek Language Support** - Full localization for Greek businesses
- 📱 **Responsive Design** - Mobile-friendly Bootstrap 5 interface
- ⚡ **One-Click Installation** - Automated setup wizard

## 🛠️ Installation

### Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- 50MB disk space

### Quick Install

1. **Upload files** to your web server
2. **Navigate** to `https://yourdomain.com/install.php`
3. **Enter** database credentials
4. **Click** install button
5. **Login** with default credentials:
   - Email: `admin@handycrm.com`
   - Password: `admin123`
6. **Delete** `install.php` file for security

### Manual Installation

See [INSTALLATION.md](INSTALLATION.md) for detailed instructions.

## 📦 What's Included

```
handycrm/
├── classes/           # Core classes (Database, Models, etc.)
├── config/            # Configuration files
├── controllers/       # MVC Controllers
├── database/          # SQL schema and migrations
├── helpers/           # Helper utilities
├── models/            # Data models
├── views/             # UI templates
├── uploads/           # User file uploads
├── .htaccess          # Apache rewrite rules
├── index.php          # Application entry point
└── install.php        # Installation wizard
```

## 🔧 Configuration

After installation, you can customize settings in `config/config.php`:

```php
// Application URL
define('APP_URL', 'https://yourdomain.com');

// Security
define('FORCE_HTTPS', true);

// Debug mode (disable in production)
define('DEBUG_MODE', false);
```

## 🚀 Usage

### Creating a Customer

1. Navigate to **Customers** → **Add New**
2. Fill in customer details
3. Save to generate SEO-friendly URL

### Managing Projects

1. Go to **Projects** → **New Project**
2. Select customer and set project details
3. Track progress with status updates

### Generating Invoices

1. Click **Invoices** → **Create Invoice**
2. Select customer and add line items
3. Invoice gets automatic number (INV-2025-0001)

## 🔄 Auto-Update System

HandyCRM includes an auto-update checker:

1. Go to **Settings** → **Check for Updates**
2. System compares with GitHub releases
3. Download and extract new version
4. Run database migrations if needed

## 📊 Database Schema

- **users** - System users and authentication
- **customers** - Customer information
- **projects** - Project tracking
- **invoices** - Invoice management
- **quotes** - Quote generation
- **settings** - System configuration

## 🔐 Security Features

- ✅ Password hashing with bcrypt
- ✅ CSRF token protection
- ✅ SQL injection prevention with PDO
- ✅ XSS protection with htmlspecialchars
- ✅ Session security with httponly cookies
- ✅ Input validation and sanitization

## 🌍 SEO-Friendly URLs

Clean, readable URLs powered by Apache mod_rewrite:

- `/customers/theodoros-sfakianakis`
- `/projects/air-conditioning-repair`
- `/invoices/inv-2025-0001`
- `/quotes/quo-2025-0001`

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 Changelog

### Version 1.0.0 (2025-01-09)
- Initial release
- Complete CRM functionality
- SEO-friendly URLs
- One-click installation
- Greek language support

## 📄 License

Copyright (c) 2025 Theodore Sfakianakis (theodore.sfakianakis@gmail.com)

**License Terms:**
- ✓ Free to use for personal and commercial purposes
- ✓ Can redistribute in original form with attribution
- ✗ Cannot sell or charge fees
- ✗ Cannot modify or edit the source code
- ✗ Cannot remove copyright notices

See the [LICENSE](LICENSE) file for complete terms.

## 👨‍💻 Author

**Theodore Sfakianakis**  
Email: theodore.sfakianakis@gmail.com

Developed with ❤️ for Greek businesses

## 🆘 Support

For issues and questions:
- GitHub Issues: [Create an issue](https://github.com/yourusername/handycrm/issues)
- Email: theodore.sfakianakis@gmail.com

**Copyright Notice:**  
© 2025 Theodore Sfakianakis. All rights reserved.  
This software cannot be modified or sold. Redistribution requires attribution.

## 🙏 Acknowledgments

- Bootstrap 5 for UI framework
- Chart.js for analytics
- Font Awesome for icons

---

**Current Version:** 1.0.0  
**Last Updated:** January 9, 2025