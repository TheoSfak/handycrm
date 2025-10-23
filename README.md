# HandyCRM - Σύστημα Διαχείρισης Πελατών για Τεχνικους-Τεχνικες Εταιρίες

**Version:** 1.3.8  
**Author:** Theodore Sfakianakis  
**Email:** theodore.sfakianakis@gmail.com  
**Copyright:** © 2025 Theodore Sfakianakis. All rights reserved.

HandyCRM είναι ένα ολοκληρωμένο σύστημα διαχείρισης πελατών (CRM) σχεδιασμένο ειδικά για ηλεκτρολόγους και υδραυλικούς. Παρέχει όλα τα εργαλεία που χρειάζεστε για να διαχειριστείτε τους πελάτες σας, τα έργα, τα ραντεβού, τις προσφορές και τα τιμολόγια.

## 🎉 Τι Νέο στην v1.3.8

- 💶 **VAT Display Settings** - Διαχείριση εμφάνισης ΦΠΑ σε τιμές
  - **Admin Configuration** - Ρυθμίσεις για εμφάνιση σημειώσεων ΦΠΑ και αν οι τιμές περιλαμβάνουν ΦΠΑ
  - **Live Preview** - Real-time προεπισκόπηση στις ρυθμίσεις
  - **Automatic Labels** - Αυτόματη προσθήκη "(χωρίς ΦΠΑ)" ή "(με ΦΠΑ)" σε όλες τις τιμές
  - **PDF Reports** - Υποστήριξη σημειώσεων ΦΠΑ σε PDF αναφορές
- 📊 **Dashboard Improvements** - Βελτιώσεις στο dashboard
  - **Fixed Statistics** - Διόρθωση υπολογισμών εσόδων και ενεργών έργων
  - **Project Count Display** - Εμφάνιση αριθμού ολοκληρωμένων έργων
  - **Calculated Totals** - Σωστός υπολογισμός κόστους από materials + labor
- 🐛 **Bug Fixes**
  - Διόρθωση υπολογισμού total_cost σε projects (χρήση calculated values αντί για stale data)
  - Fix για missing invoices/quotes tables
  - Σωστή χρήση technician_id αντί για user_id στο ProjectReportController
  - Διόρθωση column names (completion_date, invoiced_at)

### Προηγούμενα - v1.3.5

- 💰 **Advanced Payment Management** - Ολοκληρωμένο σύστημα διαχείρισης πληρωμών τεχνικών
  - **Summary Statistics** - Grand totals card με συνολικά κέρδη, πληρωμένα/απλήρωτα ποσά και progress bar
  - **Quick Date Presets** - Κουμπιά γρήγορης επιλογής (Τρέχουσα/Προηγούμενη Εβδομάδα, Τρέχων/Προηγούμενος Μήνας)
  - **CSV Export** - Εξαγωγή όλων των εγγραφών πληρωμών με φίλτρα
  - **Bulk Payment Actions** - Επισήμανση όλων των εγγραφών ως πληρωμένες με ένα κλικ
  - **Visual Enhancements** - Progress bars, color-coded amounts, enhanced tooltips, role badges
- 👥 **Role-Based Access Control** - Πλήρες σύστημα ρόλων και δικαιωμάτων
  - **4 Επίπεδα Ρόλων**: Admin, Supervisor, Technician, Assistant
  - **Διαβαθμισμένα Δικαιώματα**: Admin (πλήρης πρόσβαση), Supervisor (έργα/υλικά), Technician/Assistant (μόνο προσωπική καρτέλα)
  - **Role-Based Menu** - Δυναμικό sidebar menu ανάλογα με το ρόλο
  - **Permission Guards** - Έλεγχος δικαιωμάτων σε controllers
- 🐛 **Critical Bug Fixes**
  - Διόρθωση duplicate technician cards (PHP foreach reference bug)
  - Συμπερίληψη supervisors στις λίστες πληρωμών
  - Σωστή ανάγνωση ποσών από DOM elements

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

### � Διαχείριση Πληρωμών Τεχνικών (ΝΕΟ v1.3.5)
- **📊 Summary Statistics** - Grand totals με συνολικά κέρδη, πληρωμένα/απλήρωτα
- **⚡ Quick Date Filters** - Γρήγορη επιλογή περιόδων (εβδομάδα, μήνας)
- **📥 CSV Export** - Εξαγωγή πληρωμών σε Excel
- **✅ Bulk Actions** - Μαζική επισήμανση πληρωμών
- **🎨 Visual Progress** - Progress bars, color-coded amounts, tooltips
- **👤 Role Badges** - Εμφάνιση ρόλου δίπλα σε κάθε τεχνικό
- Παρακολούθηση ωρών εργασίας και αμοιβών
- Ανά τεχνικό/supervisor breakdown

### 👥 Διαχείριση Χρηστών & Δικαιώματα (ΝΕΟ v1.3.5)
- **4 Επίπεδα Ρόλων**: Admin, Supervisor, Technician, Assistant
- **Role-Based Access Control**: Διαβαθμισμένα δικαιώματα ανά ρόλο
- **Dynamic Menu**: Sidebar προσαρμόζεται στο ρόλο του χρήστη
- **Permission Guards**: Έλεγχος πρόσβασης σε controllers
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
