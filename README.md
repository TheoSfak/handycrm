# HandyCRM - Σύστημα Διαχείρισης Πελατών για Τεχνικους-Τεχνικες Εταιρίες

**Version:** 1.1.0  
**Author:** Theodore Sfakianakis  
**Email:** theodore.sfakianakis@gmail.com  
**Copyright:** © 2025 Theodore Sfakianakis. All rights reserved.

HandyCRM είναι ένα ολοκληρωμένο σύστημα διαχείρισης πελατών (CRM) σχεδιασμένο ειδικά για ηλεκτρολόγους και υδραυλικούς. Παρέχει όλα τα εργαλεία που χρειάζεστε για να διαχειριστείτε τους πελάτες σας, τα έργα, τα ραντεβού, τις προσφορές και τα τιμολόγια.

## � Τι Νέο στην v1.1.0

- ✅ **Πλήρες Σύστημα Διαχείρισης Εργασιών Έργου** - Καταγράψτε εργασίες με υλικά και εργατικά
- ✅ **Στατιστικά με Γραφήματα** - Οπτικοποίηση δεδομένων με Chart.js
- ✅ **CSV Export Εργασιών** - Εξαγωγή εργασιών για ανάλυση σε Excel
- ✅ **Έλεγχος Επικάλυψης Τεχνικών** - Αποφυγή διπλού προγραμματισμού
- ✅ **Βελτιώσεις UI/UX** - Καλύτερη πλοήγηση και εμφάνιση

Δείτε το [CHANGELOG.md](CHANGELOG.md) για πλήρη λίστα αλλαγών.

## �🎯 Χαρακτηριστικά

### ✅ Διαχείριση Πελατών
- Καταχώριση ιδιωτών και εταιρειών
- Ιστορικό επικοινωνίας (τηλέφωνα, emails, επισκέψεις)
- Προβολή προηγούμενων έργων και παραστατικών

### 🔧 Διαχείριση Έργων
- Κατηγοριοποίηση: Ηλεκτρολογικά, Υδραυλικά, Συντήρηση, Επείγον
- Παρακολούθηση προόδου: Νέο → Σε εξέλιξη → Ολοκληρωμένο → Τιμολογημένο
- Επισύναψη φωτογραφιών και εγγράφων
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

### 📄 Προσφορές & Τιμολόγηση
- Δημιουργία επαγγελματικών προσφορών σε PDF
- Μετατροπή προσφοράς σε έργο ή τιμολόγιο
- Παρακολούθηση πληρωμών και υπολοίπων

### 📦 Διαχείριση Υλικών
- Παρακολούθηση αποθεμάτων
- Ειδοποιήσεις χαμηλού stock
- Διαχείριση προμηθευτών

### 📱 Mobile-Friendly
- Responsive design για όλες τις συσκευές
- Δυνατότητα offline χρήσης
- Camera API για φωτογραφίες έργων

## 🛠️ Τεχνολογίες

- **Backend**: PHP 7.4+ με MVC αρχιτεκτονική
- **Database**: MySQL 5.7+ / MariaDB 10.2+
- **Frontend**: Bootstrap 5, JavaScript (ES6+)
- **Charts**: Chart.js 4.4.0
- **Styling**: Custom CSS με gradients
- **Icons**: Font Awesome 6

## 📋 Απαιτήσεις Συστήματος

- PHP 7.4 ή νεότερο
- MySQL 5.7 ή MariaDB 10.2+
- Apache ή Nginx web server
- 50MB ελεύθερος χώρος δίσκου
- SSL Certificate (προτείνεται)

## 🚀 Εγκατάσταση

### 1. Κλωνοποίηση του Repository

```bash
git clone https://github.com/your-username/handycrm.git
cd handycrm
```

### 2. Ρύθμιση Βάσης Δεδομένων

1. Δημιουργήστε μια νέα βάση δεδομένων MySQL:
```sql
CREATE DATABASE handycrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Εισάγετε το σχήμα της βάσης:
```bash
mysql -u username -p handycrm < database/schema.sql
```

3. Εισάγετε τα δεδομένα εκκίνησης:
```bash
mysql -u username -p handycrm < database/sample_data.sql
```

### 3. Ρύθμιση Εφαρμογής

1. Αντιγράψτε και επεξεργαστείτε το αρχείο ρυθμίσεων:
```bash
cp config/config.php.example config/config.php
```

2. Ενημερώστε τις ρυθμίσεις βάσης δεδομένων στο `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'handycrm');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

3. Δημιουργήστε τον φάκελο για uploads:
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
