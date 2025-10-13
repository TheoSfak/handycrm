# HandyCRM v1.0.6 - Deployment Package

## 📦 Περιεχόμενα Package

Αυτό το package περιλαμβάνει:

- ✅ Όλα τα αρχεία εφαρμογής
- ✅ Ενημερωμένη βάση δεδομένων (handycrm.sql)
- ✅ Οδηγίες εγκατάστασης (INSTALL.md)
- ✅ Παράδειγμα config αρχείου

## 🚀 Γρήγορη Εκκίνηση

### 1. Αποσυμπίεση
```bash
unzip handycrm-v1.0.6-20251013.zip -d /path/to/webserver/
```

### 2. Δημιουργία Βάσης
```sql
CREATE DATABASE handycrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
mysql -u root -p handycrm < handycrm.sql
```

### 3. Ρύθμιση Config
```bash
cp config/config.example.php config/config.php
# Επεξεργασία config.php με τα στοιχεία της βάσης σας
```

### 4. Δικαιώματα
```bash
chmod 755 uploads/
chmod 644 config/config.php
```

### 5. Πρόσβαση
```
http://localhost/handycrm/
```

**Προεπιλεγμένα Credentials:**
- Admin: `admin` / `admin123`
- Tech: `tech` / `tech123`

## 📋 Απαιτήσεις

- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+
- Apache με mod_rewrite ή Nginx
- PHP Extensions: PDO, pdo_mysql, mbstring, json, session

## 📝 Αλλαγές v1.0.6

### 🐛 Bug Fixes
- Διόρθωση delete buttons σε όλες τις σελίδες
- Διόρθωση global confirmDelete() function conflict
- Προσθήκη CSRF token σε projects delete form
- Διόρθωση form action URLs (relative → absolute)
- Διόρθωση dashboard links (customers, appointments)
- Διόρθωση translation keys (profile, notifications)

### ⚡ Improvements
- Τυποποίηση όλων των delete forms
- Βελτιστοποίηση URL routing
- Ενημέρωση language files με νέα keys
- Καλύτερη διαχείριση redirects με session persistence

### 🆕 Features από προηγούμενες εκδόσεις
- CSV Import/Export για Πελάτες
- CSV Import/Export για Έργα
- Demo CSV files για δοκιμές
- Πολυγλωσσία (Ελληνικά/English)

## 📄 Δομή Αρχείων

```
handycrm/
├── classes/           # Core classes (Database, BaseController, etc.)
├── config/            # Configuration files
├── controllers/       # Application controllers
├── languages/         # Translation files (el.json, en.json)
├── models/            # Data models
├── public/            # Public assets (CSS, JS, images)
├── views/             # View templates
├── uploads/           # User uploaded files
├── index.php          # Main entry point
├── .htaccess          # Apache rewrite rules
└── handycrm.sql       # Database schema & data
```

## 🔒 Ασφάλεια Production

1. **Αλλάξτε προεπιλεγμένους κωδικούς** αμέσως!
2. Ρυθμίστε `DEBUG_MODE = false` στο config.php
3. Χρησιμοποιήστε HTTPS
4. Ρυθμίστε ισχυρούς κωδικούς βάσης
5. Περιορίστε δικαιώματα αρχείων

## 📞 Υποστήριξη

- **Email:** theodore.sfakianakis@gmail.com
- **GitHub:** https://github.com/TheoSfak/handycrm
- **Issues:** https://github.com/TheoSfak/handycrm/issues

## 📜 Changelog

### v1.0.6 (2025-10-13)
- Fixed delete button functionality across all pages
- Fixed dashboard links and translations
- Improved URL routing consistency
- Added missing translation keys
- Enhanced session management in redirects

### v1.0.5 (2025-10-10)
- Added CSV import/export for Projects
- Added CSV import/export for Customers
- Improved error handling
- Bug fixes

## 📄 Άδεια

MIT License - Ελεύθερο για χρήση και τροποποίηση

---

**Ευχαριστούμε που επιλέξατε το HandyCRM! 🎉**

Για οδηγίες εγκατάστασης, δείτε το αρχείο `INSTALL.md`
