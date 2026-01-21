# Deployment Guide - HandyCRM v1.6.5
## Production Deployment to ecowatt.gr/crm

**Date:** January 21, 2026  
**Version:** 1.6.5  
**Target:** Production server at ecowatt.gr/crm

---

## 📋 Pre-Deployment Checklist

- [ ] All changes committed and pushed to GitHub ✅
- [ ] Version tag created (v1.6.5) ✅
- [ ] Local testing completed
- [ ] Production backup ready to be taken
- [ ] Maintenance window scheduled (optional)

---

## 🔄 Deployment Steps

### Step 1: Backup Production Site

**SSH into your production server:**
```bash
ssh your_user@ecowatt.gr
cd /path/to/crm  # or wherever your CRM is installed
```

**Backup Database:**
```bash
# Create backup directory if it doesn't exist
mkdir -p backups

# Backup database
mysqldump -u handycrm_user -p handycrm > backups/handycrm_backup_$(date +%Y%m%d_%H%M%S).sql

# Or if using a different database name/user, adjust accordingly
mysqldump -u YOUR_DB_USER -p YOUR_DB_NAME > backups/backup_$(date +%Y%m%d_%H%M%S).sql
```

**Backup Files:**
```bash
# Backup current installation
cd ..
tar -czf crm_backup_$(date +%Y%m%d_%H%M%S).tar.gz crm/

# Or copy to backup location
cp -r crm/ crm_backup_$(date +%Y%m%d_%H%M%S)/
```

---

### Step 2: Pull Latest Changes from GitHub

```bash
cd /path/to/crm

# Check current branch
git branch

# Ensure you're on main branch
git checkout main

# Pull latest changes
git pull origin main

# Checkout the specific version tag (recommended for production)
git checkout v1.6.5

# Or alternatively, just pull the latest from main
# git pull origin main
```

---

### Step 3: Update Configuration Files

**Important:** Your production `config/config.php` should NOT be overwritten!

```bash
# Check if config.php was modified
git status

# If config.php shows as modified, restore your production settings
# Option 1: Keep your production config (recommended)
git checkout config/config.php

# Option 2: Manually merge changes
# Compare your production config with the new config.example.php
# and add any new settings (like session security flags)
```

**Required Updates in config/config.php:**

Add these lines if not present (around line 80, before `session_start()`):
```php
// Session Security Configuration
ini_set('session.cookie_httponly', 1); // Prevent XSS attacks
ini_set('session.cookie_secure', 1);   // Set to 1 for HTTPS (IMPORTANT for production!)
ini_set('session.use_strict_mode', 1); // Prevent session fixation
```

**⚠️ IMPORTANT:** Change `session.cookie_secure` to `1` for HTTPS sites!

---

### Step 4: Check File Permissions

```bash
# Ensure uploads directory is writable
chmod -R 755 uploads/
chown -R www-data:www-data uploads/  # or your web server user

# Check .htaccess files exist
ls -la uploads/.htaccess

# If .htaccess is missing in uploads/, create it:
cat > uploads/.htaccess << 'EOF'
# HandyCRM Upload Security
# Prevent PHP execution in uploads directory

<FilesMatch "\.php$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Prevent directory listing
Options -Indexes

# Only allow specific file types
<FilesMatch "\.(jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>
EOF
```

---

### Step 5: Clear Cache (if applicable)

```bash
# Clear any PHP opcode cache
# For OPcache:
# systemctl restart php8.1-fpm  # or your PHP version

# Clear application cache if you have one
rm -rf cache/*  # if you have a cache directory

# Clear sessions if needed (optional - will log out all users)
# rm -rf /tmp/sess_*  # or wherever sessions are stored
```

---

### Step 6: Verify Installation

**Check version:**
```bash
cat VERSION
# Should show: 1.6.5
```

**Check git status:**
```bash
git status
git log -1
# Should show the v1.6.5 commit
```

---

### Step 7: Test Production Site

**Visit your site:** https://ecowatt.gr/crm

**Test checklist:**
- [ ] Login works
- [ ] Version shows 1.6.5 in footer
- [ ] No PHP errors in browser
- [ ] Check error logs: `tail -f /var/log/apache2/error.log` (or nginx)
- [ ] Test file upload (should work, PHP execution blocked)
- [ ] Test customer/project creation
- [ ] Test critical workflows

---

### Step 8: Monitor for Issues

**Watch logs for 15-30 minutes:**
```bash
# Apache
tail -f /var/log/apache2/error.log

# Or Nginx
tail -f /var/log/nginx/error.log

# PHP-FPM logs
tail -f /var/log/php8.1-fpm.log  # adjust version

# Application error log
tail -f /path/to/crm/logs/error.log  # if you have one
```

---

## 🚨 Rollback Plan (If Something Goes Wrong)

### Quick Rollback to Previous Version

**Option 1: Git Rollback**
```bash
cd /path/to/crm

# Go back to previous commit
git log --oneline -5  # see recent commits
git checkout <previous-commit-hash>

# Or go back to previous tag
git tag -l  # list tags
git checkout v1.6.0  # or whatever previous version
```

**Option 2: Full Restore from Backup**
```bash
# Stop web server (optional)
sudo systemctl stop apache2  # or nginx

# Restore files
cd /path/to
rm -rf crm/
tar -xzf crm_backup_YYYYMMDD_HHMMSS.tar.gz

# Restore database
mysql -u handycrm_user -p handycrm < backups/handycrm_backup_YYYYMMDD_HHMMSS.sql

# Start web server
sudo systemctl start apache2  # or nginx
```

---

## 📊 What Changed in v1.6.5

### Security Enhancements
✅ Added `.htaccess` in uploads/ directory (prevents PHP execution)  
✅ Session security flags (HttpOnly, secure, strict mode)  
✅ Enhanced XSS protection

### Code Quality
✅ 64+ catch blocks now have proper error logging  
✅ Debug output wrapped in DEBUG_MODE checks  
✅ Better error tracking throughout application

### Cleanup
✅ Debug files removed from production  
✅ Standardized version references  
✅ Improved maintainability

### Files Modified
- 25+ files updated with error logging
- Session configuration enhanced
- Upload security hardened
- Debug code cleaned

---

## 🔧 Troubleshooting

### Issue: Permission Denied Errors
```bash
# Fix ownership
sudo chown -R www-data:www-data /path/to/crm
# Or for your specific user
sudo chown -R your_user:www-data /path/to/crm

# Fix permissions
find /path/to/crm -type d -exec chmod 755 {} \;
find /path/to/crm -type f -exec chmod 644 {} \;
chmod -R 775 uploads/
```

### Issue: Git Pull Shows Conflicts
```bash
# Stash your local changes
git stash

# Pull changes
git pull origin main

# Apply your changes back
git stash pop

# Or discard local changes and force update
git reset --hard origin/main
```

### Issue: Session Issues After Update
```bash
# Clear all sessions
rm -rf /var/lib/php/sessions/*  # or your session path
# Users will need to login again
```

### Issue: .htaccess Not Working
```bash
# Check Apache mod_rewrite is enabled
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check AllowOverride in Apache config
# Edit: /etc/apache2/sites-available/your-site.conf
# Ensure: AllowOverride All (not None)
sudo systemctl restart apache2
```

---

## 📞 Support

**If issues persist:**
1. Check error logs first
2. Verify file permissions
3. Test database connectivity
4. Check PHP version compatibility (PHP 7.4+ required)
5. Verify all config settings

**Emergency Contact:**
- Developer: theodore.sfakianakis@gmail.com
- Repository: https://github.com/TheoSfak/handycrm

---

## ✅ Post-Deployment Tasks

After successful deployment:

- [ ] Verify version in footer shows 1.6.5
- [ ] Notify team of update
- [ ] Monitor error logs for 24 hours
- [ ] Update internal documentation
- [ ] Delete old backup files (keep last 3-5)
- [ ] Mark deployment as complete

---

**Deployment Date:** _____________  
**Deployed By:** _____________  
**Status:** ☐ Success  ☐ Rolled Back  ☐ Issues  
**Notes:** _____________________________________________

---

## Alternative: FTP/Manual Upload Method

If you don't have SSH/Git access on production:

1. **Download from GitHub:**
   - Go to https://github.com/TheoSfak/handycrm/releases/tag/v1.6.5
   - Download source code (ZIP)

2. **Extract locally**

3. **Upload via FTP:**
   - **DO NOT** overwrite `config/config.php`
   - Upload all other files
   - Ensure `uploads/.htaccess` is uploaded
   - Manually add session security lines to production `config/config.php`

4. **Set permissions via FTP:**
   - `uploads/` directory: 755 or 775
   - `uploads/.htaccess`: 644

---

**Good luck with your deployment! 🚀**
