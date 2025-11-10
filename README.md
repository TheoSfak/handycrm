# HandyCRM - Σύστημα Διαχείρισης Πελατών για Τεχνικους-Τεχνικες Εταιρίες

**Version:** 1.5.0  
**Author:** Theodore Sfakianakis  
**Email:** theodore.sfakianakis@gmail.com  
**Copyright:** © 2025 Theodore Sfakianakis. All rights reserved.

HandyCRM είναι ένα ολοκληρωμένο σύστημα διαχείρισης πελατών (CRM) σχεδιασμένο ειδικά για ηλεκτρολόγους, υδραυλικούς και τεχνικές εταιρίες. Παρέχει όλα τα εργαλεία που χρειάζεστε για να διαχειριστείτε τους πελάτες σας, τα έργα, τα ραντεβού, τις προσφορές και την επικοινωνία.

## 🎉 Τι Νέο στην v1.5.0

### � Email Infrastructure & PDF Reports
- **SMTP Configuration** - Ολοκληρωμένο σύστημα αποστολής emails
  - Διαμόρφωση SMTP server (host, port, encryption)
  - Support για SSL/TLS
  - Email templates με μεταβλητές
  - Καταγραφή όλων των απεσταλμένων emails
  
- **Maintenance Report Emails** - Αποστολή αναφορών συντήρησης μετασχηματιστών
  - Επιλογή αποστολής απευθείας με email από τη σελίδα συντήρησης
  - PDF generation με TCPDF
  - Ελληνικοί χαρακτήρες με DejaVu Sans font
  - Custom footer με στοιχεία εταιρίας ECOWATT
  - Περιλαμβάνει: στοιχεία πελάτη, ημερομηνίες, μετρήσεις, παρατηρήσεις
  
- **Project Report Emails** - Αποστολή αναφορών έργων
  - Αυτόματη συμπλήρωση email πελάτη
  - PDF attachment με professional formatting
  - Modal interface για επιλογή παραλήπτη και θέμα

### � Role & Permission System (RBAC)
- **Σύστημα Ρόλων** - Πλήρης διαχείριση ρόλων
  - CRUD λειτουργίες για ρόλους (δημιουργία, επεξεργασία, διαγραφή)
  - Interface διαχείρισης με Bootstrap 5
  - Default roles: Admin, Supervisor, Technician, Maintenance Technician
  
- **Σύστημα Δικαιωμάτων** - Granular access control
  - Δικαιώματα ανά module (customers, projects, tasks, maintenances, etc.)
  - Δικαιώματα ανά action (view, create, edit, delete, export)
  - Checkbox grid interface για εύκολη ανάθεση
  - Select All / Deselect All per module
  
- **AuthMiddleware** - Έλεγχος εξουσιοδότησης
  - Global helper functions: `can()`, `hasRole()`, `isAdmin()`
  - Permission checks σε controllers
  - 403 errors για μη εξουσιοδοτημένη πρόσβαση
  - Resource ownership validation

### 👥 Νέος Ρόλος Χρήστη
- **Maintenance Technician** - Τεχνικός Συντηρήσεων Μ/Σ
  - Ειδικός ρόλος για τεχνικούς συντηρήσεων μετασχηματιστών
  - Πρόσβαση μόνο σε maintenance modules
  - Μετάφραση στα Ελληνικά: "Τεχνικός Συντηρήσεων Υ/Σ"

### 🐛 Bug Fixes & Improvements
- Διόρθωση routing issues (404 errors)
- Fix για PDF class declaration errors
- Σωστά field names (project.title αντί για project.name)
- Διόρθωση redirect URLs
- Success messages μετά από email sending
- EmailService public methods
- Καθαρισμός deprecated invoice permissions

### Προηγούμενα - v1.4.0

- � **User Status Management** - Διαχείριση ενεργών/ανενεργών χρηστών
- 💼 **Payments Enhancements** - Custom date range, βελτιωμένα labels
- � **PDF Report Improvements** - Compressed columns, smaller fonts
- � **Task Safety Features** - Unsaved data warnings, photo delete buttons

Δείτε το [CHANGELOG.md](CHANGELOG.md) για πλήρη λίστα αλλαγών.

## �🎯 Χαρακτηριστικά

### ✅ Διαχείριση Πελατών
- Καταχώριση ιδιωτών και εταιρειών
- Ιστορικό επικοινωνίας (τηλέφωνα, emails, επισκέψεις)
- Προβολή προηγούμενων έργων και παραστατικών

### 🔧 Διαχείριση Έργων
- Κατηγοριοποίηση: Ηλεκτρολογικά, Υδραυλικά, Συντήρηση, Επείγον
- Παρακολούθηση προόδου: Νέο → Σε εξέλιξη → Ολοκληρωμένο → Τιμολογημένο
- **📸 Upload φωτογραφιών** με drag & drop (ΝΕΟ v1.2.0)
- **🖼️ Gallery με Lightbox** - Click για full-screen προβολή (ΝΕΟ v1.2.0)
- Υπολογισμός κόστους (υλικά, εργασία, ΦΠΑ)

### 📋 Διαχείριση Εργασιών Έργου (ΝΕΟ v1.1.0)
- Καταγραφή εργασιών ανά έργο (μονοήμερες ή πολυήμερες)
- Παρακολούθηση υλικών με ποσότητες, μονάδες μέτρησης και τιμές
- Διαχείριση εργατικών με ανάθεση τεχνικών, ώρες και αμοιβές
- Έλεγχος επικάλυψης τεχνικών (overlap detection)
- Ημερήσια ανάλυση κόστους για πολυήμερες εργασίες
- CSV Export εργασιών με πλήρη στοιχεία
- Στατιστικά έργου με γραφήματα (Chart.js):
  - Κατανομή κόστους (υλικά vs εργατικά)
  - Κατάταξη τεχνικών
  - Ανάλυση ανά ημέρα εβδομάδας
  - Top 5 ακριβότερες εργασίες

### 📅 Ημερολόγιο & Ραντεβού
- Προγραμματισμός ραντεβού τεχνικών
- Υπενθυμίσεις μέσω email
- Ομαδικός προγραμματισμός για συνεργεία

### 💰 Διαχείριση Πληρωμών Τεχνικών
- **📊 Summary Statistics** - Grand totals με συνολικά κέρδη, πληρωμένα/απλήρωτα
- **⚡ Quick Date Filters** - Γρήγορη επιλογή περιόδων (εβδομάδα, μήνας)
- **📥 CSV Export** - Εξαγωγή πληρωμών σε Excel
- **✅ Bulk Actions** - Μαζική επισήμανση πληρωμών
- **🎨 Visual Progress** - Progress bars, color-coded amounts, tooltips
- **👤 Role Badges** - Εμφάνιση ρόλου δίπλα σε κάθε τεχνικό
- Παρακολούθηση ωρών εργασίας και αμοιβών
- Ανά τεχνικό/supervisor breakdown

### � Role & Permission System (ΝΕΟ v1.5.0)
- **Σύστημα Ρόλων** - Δημιουργία και διαχείριση custom ρόλων
- **Granular Permissions** - Δικαιώματα ανά module και action
- **Permission Interface** - Checkbox grid για εύκολη ανάθεση
- **AuthMiddleware** - Helper functions: `can()`, `hasRole()`, `isAdmin()`
- **Default Roles**: Admin, Supervisor, Technician, Maintenance Technician
- **Permission Modules**: customers, projects, tasks, maintenances, materials, reports, users, roles, settings
- **Permission Actions**: view, create, edit, delete, export, assign, permissions

### 📧 Email System (ΝΕΟ v1.5.0)
- **SMTP Configuration** - Διαμόρφωση email server μέσω UI
- **Email Templates** - Προσαρμόσιμα templates με μεταβλητές
- **Email Log** - Καταγραφή όλων των απεσταλμένων emails
- **PDF Attachments** - TCPDF integration με ελληνικούς χαρακτήρες
- **Maintenance Reports** - Αποστολή αναφορών συντήρησης Μ/Σ
- **Project Reports** - Αποστολή αναφορών έργων σε πελάτες
- **Custom Branding** - Company footer στα PDFs

### 👥 Διαχείριση Χρηστών
- **5 Επίπεδα Ρόλων**: Admin, Supervisor, Technician, Assistant, Maintenance Technician
- **Active/Inactive Status** - Toggle χρηστών με ένα κλικ
- **Smart Filtering** - Ανενεργοί χρήστες δεν εμφανίζονται σε dropdowns
- **Role-Based Menu** - Δυναμικό sidebar ανάλογα με δικαιώματα
- **Permission Guards** - Έλεγχος πρόσβασης σε controllers
- Προσωπική καρτέλα τεχνικού/βοηθού

### �📄 Προσφορές & Τιμολόγηση
- Δημιουργία επαγγελματικών προσφορών σε PDF
- Μετατροπή προσφοράς σε έργο ή τιμολόγιο
- Παρακολούθηση πληρωμών και υπολοίπων

### 📦 Καταλογος Υλικών & Έξυπνη Αναζήτηση (ΝΕΟ v1.2.0)
- **🔍 Greeklish Search** - "kalodio" βρίσκει "Καλώδιο"
- **🌍 Synonym Matching** - "cable" βρίσκει "Καλώδιο"
- **🔢 Code Search** - "nym" βρίσκει "Καλώδιο NYM 3x1.5"
- **🤖 Auto-Aliases** - Αυτόματη δημιουργία λέξεων-κλειδιών
- **⚡ Real-time Autocomplete** - Άμεση εμφάνιση αποτελεσμάτων
- **📊 Category Management** - Οργάνωση σε κατηγορίες
- **💰 Auto-Fill Prices** - Αυτόματη συμπλήρωση τιμής από κατάλογο
- Παρακολούθηση αποθεμάτων (legacy system)

### 📱 Mobile-Friendly
- Responsive design για όλες τις συσκευές
- Δυνατότητα offline χρήσης
- Camera API για φωτογραφίες έργων

## 🛠️ Τεχνολογίες

- **Backend**: PHP 7.4+ / 8.0+ με MVC αρχιτεκτονική
- **Database**: MySQL 5.7+ / MariaDB 10.2+
- **Email**: PHPMailer 6.9+ με SMTP support
- **PDF Generation**: TCPDF με Greek UTF-8 support
- **Frontend**: Bootstrap 5, JavaScript (ES6+)
- **Charts**: Chart.js 4.4.0
- **Styling**: Custom CSS με gradients
- **Icons**: Font Awesome 6
- **Dependencies**: Composer για package management

## 📋 Απαιτήσεις Συστήματος

- PHP 7.4 ή νεότερο (8.0+ συνιστάται)
- MySQL 5.7 ή MariaDB 10.2+
- Apache ή Nginx web server
- Composer (για dependencies)
- 100MB ελεύθερος χώρος δίσκου
- SSL Certificate (προτείνεται για email encryption)

## 🚀 Εγκατάσταση

### Επιλογή 1: Νέα Εγκατάσταση (Fresh Install)

#### 1. Κλωνοποίηση του Repository

```bash
git clone https://github.com/TheoSfak/handycrm.git
cd handycrm
```

#### 2. Εγκατάσταση Dependencies

```bash
composer install
```

#### 3. Ρύθμιση Βάσης Δεδομένων

1. Δημιουργήστε μια νέα βάση δεδομένων MySQL:
```sql
CREATE DATABASE handycrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'handycrm_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON handycrm.* TO 'handycrm_user'@'localhost';
FLUSH PRIVILEGES;
```

2. Εισάγετε το σχήμα της βάσης για v1.5.0:
```bash
mysql -u handycrm_user -p handycrm < migrations/v1.5.0_fresh_install_schema.sql
```

#### 4. Ρύθμιση Εφαρμογής

1. Αντιγράψτε και επεξεργαστείτε το αρχείο ρυθμίσεων:
```bash
cp config/config.php.example config/config.php
```

2. Ενημερώστε τις ρυθμίσεις βάσης δεδομένων στο `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'handycrm');
define('DB_USER', 'handycrm_user');
define('DB_PASS', 'your_secure_password');
```

3. Δημιουργήστε τον φάκελο για uploads:
```bash
mkdir uploads
chmod 755 uploads
```

#### 5. Ρύθμιση Email (Προαιρετικό αλλά συνιστάται)

Μετά την πρώτη σύνδεση, πηγαίνετε στο **Ρυθμίσεις → Email Settings** και ρυθμίστε:
- SMTP Host (π.χ. smtp.titan.email)
- SMTP Port (465 για SSL, 587 για TLS)
- Encryption Type (SSL/TLS)
- Username και Password
- From Email και From Name

### Επιλογή 2: Ενημέρωση από v1.4.x

#### 1. Backup της Βάσης Δεδομένων

```bash
mysqldump -u handycrm_user -p handycrm > backup_before_1.5.0.sql
```

#### 2. Pull τις Τελευταίες Αλλαγές

```bash
cd handycrm
git pull origin main
```

#### 3. Ενημέρωση Dependencies

```bash
composer update
```

#### 4. Εκτέλεση Migration Script

```bash
mysql -u handycrm_user -p handycrm < migrations/v1.5.0_upgrade_from_1.4.sql
```

Το migration script θα:
- ✅ Δημιουργήσει τις νέες email tables (smtp_settings, email_templates, email_notifications)
- ✅ Δημιουργήσει τις permissions tables (roles, permissions, role_permissions, user_role)
- ✅ Ενημερώσει το users.role ENUM (προσθήκη maintenance_technician)
- ✅ Εισάγει default permissions για όλα τα modules
- ✅ Διαγράψει deprecated invoice permissions
- ✅ Δημιουργήσει default roles

#### 5. Ρύθμιση Email & Permissions

1. Ρυθμίστε SMTP settings στο admin panel
2. Ανθέστε δικαιώματα στους υπάρχοντες ρόλους
3. Εκχωρήστε ρόλους στους χρήστες μέσω του `/roles` menu
```bash
mkdir uploads
chmod 755 uploads
```

### 4. Ρύθμιση Web Server

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## 👤 Πρώτη Σύνδεση

Μετά την εγκατάσταση, μπορείτε να συνδεθείτε με τα προεπιλεγμένα στοιχεία:

- **Username**: admin
- **Password**: admin123
- **URL**: http://your-domain.com/handycrm

⚠️ **Σημαντικό**: Αλλάξτε αμέσως τον κωδικό του διαχειριστή!

## 🔄 Ενημέρωση (Update)

### Αυτόματη Ενημέρωση (Συνιστάται)

1. Πηγαίνετε στο **Ρυθμίσεις → Ενημέρωση**
2. Κλικ "Έλεγχος για Ενημέρωση"
3. Αν υπάρχει νέα έκδοση, κλικ "Ενημέρωση Τώρα"

Το σύστημα θα:
- ✅ Κατεβάσει αυτόματα τη νέα έκδοση από GitHub
- ✅ Δημιουργήσει backup της βάσης δεδομένων
- ✅ Τρέξει αυτόματα όλα τα απαραίτητα database migrations
- ✅ Αντικαταστήσει τα αρχεία με τη νέα έκδοση

### Χειροκίνητη Ενημέρωση

1. **Κάντε backup** της βάσης δεδομένων
2. Κατεβάστε τη νέα έκδοση από [GitHub Releases](https://github.com/TheoSfak/handycrm/releases)
3. Αντικαταστήστε τα αρχεία (⚠️ **μην διαγράψετε** το `config/config.php`)
4. Τα migrations θα τρέξουν αυτόματα κατά το πρώτο login

Για περισσότερα, δείτε το [migrations/README.md](migrations/README.md)

### GitHub Token για Ενημερώσεις (Προαιρετικό)

Αν αντιμετωπίζετε rate limiting από το GitHub API, προσθέστε ένα token:

```php
// Στο config/config.php
define('GITHUB_TOKEN', 'your_github_personal_access_token');
```

**Πώς να δημιουργήσετε token:**
1. GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Generate new token
3. Επιλέξτε μόνο: `public_repo` (Read access to public repositories)
4. Copy το token και προσθέστε το στο config

## 📁 Δομή Έργου

```
handycrm/
├── classes/           # Core PHP classes
│   ├── Database.php
│   ├── Router.php
│   ├── BaseModel.php
│   └── BaseController.php
├── config/            # Configuration files
│   └── config.php
├── controllers/       # MVC Controllers
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── CustomerController.php
│   └── ...
├── models/           # MVC Models
│   ├── User.php
│   ├── Customer.php
│   ├── Project.php
│   └── ...
├── views/            # MVC Views
│   ├── includes/     # Header/Footer
│   ├── auth/         # Login pages
│   ├── dashboard/    # Dashboard
│   ├── customers/    # Customer views
│   └── ...
├── database/         # Database files
│   ├── schema.sql
│   └── sample_data.sql
├── uploads/          # Uploaded files
├── assets/           # Static assets
│   ├── css/
│   ├── js/
│   └── images/
└── index.php         # Application entry point
```

## 🔧 Ρυθμίσεις

### Email Ρυθμίσεις
Για αποστολή ειδοποιήσεων μέσω email, ενημερώστε τις ρυθμίσεις SMTP στο `config/config.php`:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
```

### Ασφάλεια
1. Αλλάξτε το `SECRET_KEY` στο config
2. Ενεργοποιήστε HTTPS
3. Ρυθμίστε τα δικαιώματα αρχείων σωστά
4. Κάντε regular backups

## 📖 Οδηγός Χρήσης

### Διαχείριση Πελατών
1. Πηγαίνετε στο μενού "Πελάτες"
2. Κλικ "Νέος Πελάτης"
3. Επιλέξτε τύπο (Ιδιώτης/Εταιρεία)
4. Συμπληρώστε τα στοιχεία
5. Αποθηκεύστε

### Δημιουργία Έργου
1. Πηγαίνετε στο μενού "Έργα"
2. Κλικ "Νέο Έργο"
3. Επιλέξτε πελάτη και τεχνικό
4. Ορίστε κατηγορία και προτεραιότητα
5. Προσθέστε περιγραφή και εκτίμηση κόστους

### Προγραμματισμός Ραντεβού
1. Πηγαίνετε στο "Ημερολόγιο"
2. Κλικ σε ημερομηνία ή "Νέο Ραντεβού"
3. Επιλέξτε πελάτη και τεχνικό
4. Ορίστε ώρα και διάρκεια
5. Προσθέστε σημειώσεις

## 🔍 Troubleshooting

### Συχνά Προβλήματα

**Πρόβλημα**: Λάθος 500 κατά τη σύνδεση
**Λύση**: Ελέγξτε τις ρυθμίσεις βάσης δεδομένων και τα δικαιώματα αρχείων

**Πρόβλημα**: Δεν φορτώνουν οι εικόνες
**Λύση**: Ελέγξτε ότι ο φάκελος uploads έχει δικαιώματα εγγραφής (chmod 755)

**Πρόβλημα**: Δεν λειτουργεί το URL rewriting
**Λύση**: Ενεργοποιήστε το mod_rewrite στον Apache ή ρυθμίστε τον Nginx σωστά

## 📞 Υποστήριξη

- **Email**: support@handycrm.gr
- **Documentation**: https://docs.handycrm.gr
- **Issues**: https://github.com/your-username/handycrm/issues

## 📄 Άδεια Χρήσης

**Copyright © 2025 Theodore Sfakianakis** (theodore.sfakianakis@gmail.com)

**Όροι Χρήσης:**
- ✓ Ελεύθερη χρήση για προσωπικούς και επαγγελματικούς σκοπούς
- ✓ Επιτρέπεται η αναδιανομή με αναφορά στον δημιουργό
- ✗ ΔΕΝ επιτρέπεται η πώληση ή χρέωση τελών
- ✗ ΔΕΝ επιτρέπεται η τροποποίηση ή επεξεργασία του κώδικα
- ✗ ΔΕΝ επιτρέπεται η αφαίρεση των πνευματικών δικαιωμάτων

Δείτε το αρχείο [LICENSE](LICENSE) για πλήρεις λεπτομέρειες.

## 🤝 Επικοινωνία & Υποστήριξη

**Δημιουργός:** Theodore Sfakianakis  
**Email:** theodore.sfakianakis@gmail.com  

Για βοήθεια, ερωτήσεις ή προτάσεις, επικοινωνήστε μέσω email.

**Σημαντικό:** Το λογισμικό δεν μπορεί να τροποποιηθεί. Οι συνεισφορές μπορούν να γίνουν μόνο μέσω του δημιουργού.

## 🔄 Changelog

### v1.0.0 (Αρχική Έκδοση)
- Διαχείριση πελατών
- Διαχείριση έργων
- Σύστημα ραντεβού
- Προσφορές και τιμολόγηση
- Βασική διαχείριση υλικών
- Responsive UI
- Σύστημα authentication

## 🚧 Επόμενες Ενημερώσεις

- [ ] Google Calendar integration
- [ ] SMS notifications
- [ ] Advanced reporting
- [ ] Mobile app
- [ ] API για integrations
- [ ] Multi-language support

---

**HandyCRM** - Κάνοντας τη διαχείριση των πελατών σας εύκολη και αποδοτική! 🔧
