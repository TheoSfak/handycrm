# HandyCRM - Οδηγίες Εγκατάστασης

## Απαιτήσεις Συστήματος

- PHP 7.4 ή νεότερο
- MySQL 5.7+ ή MariaDB 10.3+
- Apache/Nginx web server
- Υποστήριξη για mod_rewrite (Apache)
- PHP Extensions:
  - PDO
  - pdo_mysql
  - mbstring
  - json
  - session
  - fileinfo

## Οδηγίες Εγκατάστασης

### 1. Αποσυμπίεση Αρχείων

Αποσυμπιέστε το αρχείο zip στον φάκελο του web server σας:
- Apache/XAMPP: `htdocs/handycrm/`
- Nginx: `/var/www/html/handycrm/`

### 2. Δημιουργία Βάσης Δεδομένων

```sql
CREATE DATABASE handycrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Εισαγωγή Δεδομένων

Εισάγετε το αρχείο `handycrm.sql` στη βάση δεδομένων:

```bash
mysql -u root -p handycrm < handycrm.sql
```

Ή μέσω phpMyAdmin:
1. Ανοίξτε το phpMyAdmin
2. Επιλέξτε τη βάση δεδομένων `handycrm`
3. Πηγαίνετε στην καρτέλα "Import"
4. Επιλέξτε το αρχείο `handycrm.sql`
5. Πατήστε "Go"

### 4. Διαμόρφωση Εφαρμογής

Αντιγράψτε το `config/config.example.php` σε `config/config.php`:

```bash
cp config/config.example.php config/config.php
```

Επεξεργαστείτε το `config/config.php` και ρυθμίστε τα στοιχεία σύνδεσης με τη βάση δεδομένων:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'handycrm');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 5. Δικαιώματα Αρχείων

Βεβαιωθείτε ότι ο φάκελος `uploads/` είναι εγγράψιμος:

```bash
chmod 755 uploads/
```

### 6. Πρόσβαση στην Εφαρμογή

Ανοίξτε τον browser και πηγαίνετε στη διεύθυνση:

```
http://localhost/handycrm/
```

### 7. Προεπιλεγμένοι Λογαριασμοί

#### Admin Account:
- **Username:** admin
- **Password:** admin123

#### Technician Account:
- **Username:** tech
- **Password:** tech123

**ΣΗΜΑΝΤΙΚΟ:** Αλλάξτε τους κωδικούς πρόσβασης αμέσως μετά την πρώτη σύνδεση!

## Ρύθμιση Production

### 1. Απενεργοποίηση Debug Mode

Στο `config/config.php`:

```php
define('DEBUG_MODE', false);
```

### 2. Ασφαλής CSRF Token

Βεβαιωθείτε ότι το CSRF token είναι ενεργοποιημένο:

```php
define('CSRF_TOKEN_NAME', 'csrf_token');
```

### 3. Ασφαλή Δικαιώματα

```bash
chmod 644 config/config.php
chmod 755 uploads/
```

### 4. HTTPS

Ενεργοποιήστε SSL/TLS για ασφαλή σύνδεση:

```apache
# .htaccess
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## Αντιμετώπιση Προβλημάτων

### Σφάλμα σύνδεσης με τη βάση

Ελέγξτε:
- Το MySQL service τρέχει
- Τα στοιχεία σύνδεσης είναι σωστά
- Η βάση δεδομένων υπάρχει
- Ο χρήστης έχει δικαιώματα

### Σφάλμα 404 / Routing Issues

Ελέγξτε:
- Το mod_rewrite είναι ενεργοποιημένο (Apache)
- Το `.htaccess` υπάρχει στον root φάκελο
- Τα AllowOverride All είναι ρυθμισμένα

### Upload Errors

Ελέγξτε:
- Ο φάκελος `uploads/` υπάρχει
- Έχει τα σωστά δικαιώματα (755)
- Το `upload_max_filesize` στο php.ini είναι επαρκές

## Χαρακτηριστικά v1.0.6

✅ Διαχείριση Πελατών (Ιδιώτες & Εταιρίες)
✅ Διαχείριση Έργων με Κόστη
✅ Ραντεβού & Ημερολόγιο
✅ Προσφορές & Τιμολόγια
✅ Αποθήκη Υλικών
✅ Διαχείριση Χρηστών (Admin/Technician)
✅ CSV Import/Export για Πελάτες & Έργα
✅ Πολυγλωσσία (Ελληνικά/English)
✅ Responsive Design
✅ Dark/Light Theme

## Υποστήριξη

Για υποστήριξη επικοινωνήστε:
- Email: theodore.sfakianakis@gmail.com
- GitHub: https://github.com/TheoSfak/handycrm

## Άδεια Χρήσης

Αυτό το έργο αδειοδοτείται υπό την MIT License.

---

**HandyCRM v1.0.6**  
© 2024-2025 Theodore Sfakianakis
