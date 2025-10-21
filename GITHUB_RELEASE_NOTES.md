# 🎉 HandyCRM v1.3.5 - Advanced Payment Management & Role-Based Access Control

Αυτή η έκδοση φέρνει σημαντικές βελτιώσεις στο σύστημα διαχείρισης πληρωμών τεχνικών και ένα πλήρες σύστημα ρόλων και δικαιωμάτων χρηστών.

## 💰 Advanced Payment Management System

### Summary Statistics Dashboard
- 📊 **Grand Totals Card** με συνολικά κέρδη, πληρωμένα και απλήρωτα ποσά
- 📈 **Progress Bar** που δείχνει το ποσοστό ολοκλήρωσης πληρωμών
- 🎨 **Modern UI** με gradient styling και real-time calculations

### Quick Date Presets
- ⚡ **4 Κουμπιά Γρήγορης Επιλογής**:
  - Τρέχουσα Εβδομάδα
  - Προηγούμενη Εβδομάδα
  - Τρέχων Μήνας
  - Προηγούμενος Μήνας
- 🚀 Auto-fill των ημερομηνιών με ένα κλικ

### CSV Export
- 📥 **Εξαγωγή σε Excel/CSV** με όλα τα φίλτρα
- 📋 Περιλαμβάνει: τεχνικό, ημερομηνία, έργο, εργασία, ώρες, αμοιβή, ποσό, κατάσταση
- 🇬🇷 Σωστή υποστήριξη ελληνικών χαρακτήρων (UTF-8 BOM)

### Bulk Payment Actions
- ✅ **"Επισήμανση Όλων ως Πληρωμένα"** με ένα κλικ
- 📝 Confirmation modal με λίστα τεχνικών και ποσών
- 💪 Υποστήριξη **όλων των ρόλων** (Admin, Supervisor, Technician, Assistant)
- 🎯 AJAX endpoint για γρήγορη bulk ενημέρωση

### Visual Enhancements
- 📊 **Progress bars** σε κάθε κάρτα τεχνικού
- 🎨 **Color-coded amounts**: Πράσινο για πληρωμένα, κόκκινο για απλήρωτα
- 💬 **Enhanced tooltips** με λεπτομερείς πληροφορίες
- 🏷️ **Role badges** δίπλα σε κάθε όνομα (π.χ. "ΤΕΧΝΙΚΟΣ", "ΥΠΕΥΘΥΝΟΣ ΣΥΝΕΡΓΕΙΟΥ")
- 🌈 **Gradient headers** με βελτιωμένη οπτική ιεραρχία

## 👥 Role-Based Access Control System

### Four-Tier Role System
- 👑 **Admin (Διαχειριστής)**: Πλήρης πρόσβαση σε όλο το σύστημα
- 👨‍💼 **Supervisor (Υπεύθυνος Συνεργείου)**: Έργα, υλικά, προσωπική καρτέλα
- 🔧 **Technician (Τεχνικός)**: Μόνο προσωπική καρτέλα
- 🛠️ **Assistant (Βοηθός Τεχνικού)**: Μόνο προσωπική καρτέλα

### Permission System
- 🔐 **BaseController Permission Methods**: `canManageProjects()`, `canManageMaterials()`, `canViewUser()`
- 🚪 **Guard Methods**: `requireAdmin()`, `requireSupervisorOrAdmin()` για έλεγχο πρόσβασης
- 📱 **Dynamic Sidebar Menu**: Το menu προσαρμόζεται αυτόματα στο ρόλο του χρήστη
- ✅ **Permission Checks**: Έλεγχοι δικαιωμάτων σε controllers

### Database Updates
- 🗄️ **Automated Migration**: Τρέχει αυτόματα κατά το login
- 📝 **Idempotent**: Ασφαλές να τρέξει πολλές φορές
- 🔄 **Rollback Safe**: Transaction-based migration

## 🐛 Critical Bug Fixes

### PHP Foreach Reference Bug
- ❌ **Πρόβλημα**: Duplicate technician cards (π.χ. "Σφακιανάκης" εμφανιζόταν δύο φορές)
- ✅ **Λύση**: Προστέθηκε `unset($tech)` μετά το foreach loop
- 🎯 **Αποτέλεσμα**: Κάθε τεχνικός εμφανίζεται μία φορά

### Payment Query Role Restriction
- ❌ **Πρόβλημα**: Supervisors δεν εμφανίζονταν στη λίστα πληρωμών
- ✅ **Λύση**: Αφαιρέθηκε το role restriction από Payment model
- 🎯 **Αποτέλεσμα**: Όλοι οι ρόλοι εμφανίζονται πλέον

### Bulk Payment Amount Detection
- ❌ **Πρόβλημα**: Λάθος ποσά στο modal (π.χ. 15000.00€ αντί για 150.00€)
- ✅ **Λύση**: Προστέθηκε `data-unpaid-amount` attribute
- 🎯 **Αποτέλεσμα**: Σωστή εμφάνιση ποσών

### UI Improvements
- ❌ **Πρόβλημα**: Μπλε κείμενο σε μωβ gradient (δύσκολο στην ανάγνωση)
- ✅ **Λύση**: Αλλαγή σε λευκό κείμενο
- 🎯 **Αποτέλεσμα**: Καλύτερη αναγνωσιμότητα

## 📦 Installation & Upgrade

### Fresh Installation
```bash
# Download and extract
unzip handycrm-v1.3.5.zip -d /var/www/html/handycrm

# Import database
mysql -u username -p handycrm < database/schema.sql

# Configure
cp config/config.php.example config/config.php
# Edit config.php with your settings

# Set permissions
chmod 755 uploads logs

# Access via browser
http://localhost/handycrm
```

**Default Login**: 
- Username: `admin`
- Password: `admin123`
- ⚠️ Change immediately after first login!

### Upgrade from v1.3.0+
```bash
# Backup first!
mysqldump -u username -p handycrm > backup.sql

# Update files
git pull origin main
git checkout v1.3.5

# Login to trigger automatic migration
# Or run manually:
mysql -u username -p handycrm < migrations/migrate_to_1.3.5.sql
```

### Migration Details
The migration adds:
- `supervisor` role to users.role ENUM
- `paid_at` column to task_labor (if missing)
- `paid_by` column to task_labor (if missing)
- `is_active` column to users (if missing)
- Performance indexes for payment queries

**Migration is IDEMPOTENT**: Safe to run multiple times!

## 📝 Files Changed

### New Files
- `migrations/migrate_to_1.3.5.sql` - Main migration
- `migrations/verify_1.3.5_ready.sql` - Verification script
- `INSTALL.md` - Installation guide
- `DEPLOYMENT_CHECKLIST.md` - Release checklist

### Modified Files
- `controllers/PaymentsController.php` - Bulk payment, bug fixes
- `models/Payment.php` - Role inclusion, query optimization
- `classes/BaseController.php` - Permission methods
- `views/includes/header.php` - Role-based menu
- `views/payments/index.php` - All payment enhancements
- `views/users/edit.php` & `create.php` - Supervisor role
- `README.md` - v1.3.5 documentation
- `CHANGELOG.md` - Complete changelog
- `VERSION` - Updated to 1.3.5

## 🔍 What's Next?

Check out the [full changelog](CHANGELOG.md) for detailed technical information.

For installation help, see [INSTALL.md](INSTALL.md).

## 🙏 Acknowledgments

Thank you to all users who provided feedback and bug reports!

## 📞 Support

- 📖 [Documentation](README.md)
- 🐛 [Report Issues](https://github.com/TheoSfak/handycrm/issues)
- 📧 Email: theodore.sfakianakis@gmail.com

---

**Full Changelog**: [v1.3.0...v1.3.5](https://github.com/TheoSfak/handycrm/compare/v1.3.0...v1.3.5)

**Release Date**: October 21, 2025  
**Build Status**: ✅ Stable  
**Download**: handycrm-v1.3.5.zip (0.39 MB)
