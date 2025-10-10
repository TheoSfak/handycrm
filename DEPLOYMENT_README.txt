================================================================================
  HandyCRM v1.0.3 - Deployment Instructions
================================================================================

Thank you for using HandyCRM!

This package contains everything you need to deploy HandyCRM on your server.

================================================================================
  REQUIREMENTS
================================================================================

- PHP 7.4 or higher (PHP 8.x recommended)
- MySQL 5.7+ or MariaDB 10.2+
- Web server (Apache, Nginx, etc.)
- mod_rewrite enabled (for Apache)

================================================================================
  INSTALLATION STEPS
================================================================================

1. UPLOAD FILES
   ------------
   Upload all files to your web server (via FTP, cPanel File Manager, etc.)
   
   Example locations:
   - cPanel: /public_html/handycrm/
   - Direct domain: /public_html/
   - Subdomain: /public_html/crm/

2. CREATE DATABASE
   ---------------
   Create a new MySQL database through your hosting control panel:
   - Database name: handycrm (or any name you prefer)
   - Database user: Create a user with full privileges
   - Write down: hostname, database name, username, password

3. SET PERMISSIONS
   ---------------
   Make sure these directories are writable (chmod 755 or 775):
   - /uploads/
   - /backups/
   - /config/

4. VISIT YOUR URL
   --------------
   Open your browser and go to your HandyCRM URL:
   
   Examples:
   - https://yourdomain.com/handycrm/
   - https://crm.yourdomain.com/
   
   The system will AUTOMATICALLY redirect you to the installation wizard!

5. FOLLOW THE INSTALLATION WIZARD
   -------------------------------
   Step 1: Database Configuration
   - Enter your database details (from step 2)
   - Enter your application URL (e.g., https://yourdomain.com/handycrm)
   - Click "Εγκατάσταση" (Install)
   
   Step 2: Admin Account Setup
   - Create your admin username and password
   - Enter your email and company details
   - Click "Ολοκλήρωση Εγκατάστασης" (Complete Installation)

6. DONE!
   -----
   You're all set! Log in with your admin credentials.

================================================================================
  DEFAULT CREDENTIALS (If using the demo data)
================================================================================

Username: admin
Password: admin123
Email: admin@handycrm.local

⚠️ IMPORTANT: Change the admin password immediately after first login!

================================================================================
  POST-INSTALLATION
================================================================================

1. DELETE install.php
   After successful installation, DELETE the install.php file for security:
   - Via FTP/File Manager: Delete install.php
   - Via SSH: rm install.php

2. SECURITY SETTINGS
   - Go to Settings and update company information
   - Change admin password
   - Review user accounts

3. LANGUAGE SELECTION
   - Go to Settings
   - Choose your language (English or Greek)
   - Visit Settings > Translations to add more languages

================================================================================
  FEATURES
================================================================================

✓ Customer Management (CRM)
✓ Project Tracking
✓ Invoice Generation
✓ Quotes & Estimates
✓ Appointment Calendar
✓ Material/Inventory Management
✓ User Management (Multi-user)
✓ Automated Backups
✓ Auto-Update System
✓ Multi-Language Support (EN/EL)
✓ Translation Manager

================================================================================
  TROUBLESHOOTING
================================================================================

Problem: "Database connection failed"
Solution: Double-check database credentials, hostname, and database name

Problem: "Permission denied" errors
Solution: Set proper permissions on uploads/ and backups/ folders (755 or 775)

Problem: Blank page after installation
Solution: Check PHP error logs, ensure PHP version is 7.4+

Problem: Install.php doesn't show up
Solution: Make sure you're accessing the correct URL with /handycrm/ if installed in subfolder

Problem: Already installed message
Solution: To reinstall, delete config/config.php and visit URL again

================================================================================
  SUPPORT
================================================================================

GitHub: https://github.com/TheoSfak/handycrm
Email: theodore.sfakianakis@gmail.com

For updates, visit the Updates page in Settings.

================================================================================
  LICENSE
================================================================================

Copyright 2025 Theodore Sfakianakis. All rights reserved.

================================================================================

Enjoy using HandyCRM! 🚀
