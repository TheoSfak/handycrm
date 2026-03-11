# HandyCRM - Σύστημα Διαχείρισης Πελατών για Τεχνικους-Τεχνικες Εταιρίες

**Version:** 1.7.6  
**Author:** Theodore Sfakianakis  
**Email:** theodore.sfakianakis@gmail.com  
**Copyright:** © 2025–2026 Theodore Sfakianakis. All rights reserved.

HandyCRM είναι ένα ολοκληρωμένο σύστημα διαχείρισης πελατών (CRM) σχεδιασμένο ειδικά για ηλεκτρολόγους και υδραυλικούς. Παρέχει όλα τα εργαλεία που χρειάζεστε για να διαχειριστείτε τους πελάτες σας, τα έργα, τα ραντεβού, τις προσφορές και τις πληρωμές.

## 🎉 Τι Νέο στην v1.7.6

- 📄 **Συμφωνητικό (Contract)** — Γεννάει Word (.docx) συμφωνητικό κατ' αποκοπή για κάθε έργο
  - Νέο κουμπί "Συμφωνητικό" στη σελίδα έργου — κατεβάζει αρχείο `Συμφωνητικό_<τίτλος>_<ημ/νία>.docx`
  - Αυτόματη συμπλήρωση στοιχείων πελάτη, εταιρείας, εργασιών, κόστους, ΦΠΑ και υπογραφών
  - Πλήρεις νομικοί όροι (ΓΕΝΙΚΟΙ ΟΡΟΙ ΤΙΜΟΛΟΓΙΟΥ - 10 σημεία, ΛΟΙΠΟΙ ΟΡΟΙ - 4 ρήτρες)
  - Δωσιδικία Ηρακλείου

Δείτε το [CHANGELOG.md](CHANGELOG.md) για πλήρη λίστα αλλαγών.

## 🎯 Χαρακτηριστικά

### ✅ Διαχείριση Πελατών
- Καταχώριση ιδιωτών και εταιρειών
- Ιστορικό επικοινωνίας (τηλέφωνα, emails, επισκέψεις)
- Προβολή προηγούμενων έργων και παραστατικών

### 🔧 Διαχείριση Έργων
- Κατηγοριοποίηση: Ηλεκτρολογικά, Υδραυλικά, Συντήρηση, Επείγον
- Παρακολούθηση προόδου: Νέο → Σε εξέλιξη → Ολοκληρωμένο → Τιμολογημένο
- 📸 Upload φωτογραφιών με drag & drop
- 🖼️ Gallery με Lightbox — click για full-screen προβολή
- Υπολογισμός κόστους (υλικά, εργασία, ΦΠΑ) από εργασίες
- 📄 **Συμφωνητικό** — Λήψη Word (.docx) συμφωνητικού κατ' αποκοπή
- 📊 PDF Αναφορά έργου

### 📋 Διαχείριση Εργασιών Έργου
- Καταγραφή εργασιών ανά έργο (μονοήμερες ή πολυήμερες)
- Παρακολούθηση υλικών με ποσότητες, μονάδες μέτρησης και τιμές
- Διαχείριση εργατικών με ανάθεση τεχνικών, ώρες και αμοιβές
- Έλεγχος επικάλυψης τεχνικών (overlap detection)
- CSV Export εργασιών
- Στατιστικά έργου με γραφήματα (Chart.js)

### 📅 Ημερολόγιο & Ραντεβού
- Προγραμματισμός ραντεβού τεχνικών
- Υπενθυμίσεις μέσω email
- Ομαδικός προγραμματισμός

### 💰 Διαχείριση Πληρωμών Τεχνικών
- Summary Statistics — grand totals, πληρωμένα/απλήρωτα
- ⚡ Quick Date Filters — γρήγορη επιλογή εβδομάδας/μήνα
- 📥 CSV Export πληρωμών
- ✅ Bulk Actions — μαζική επισήμανση πληρωμών
- 🎨 Visual progress bars, color-coded amounts, role badges

### 👥 Διαχείριση Χρηστών & Δικαιώματα
- **4 Επίπεδα Ρόλων**: Admin, Supervisor, Technician, Assistant
- Role-Based Access Control με granular permissions
- Dynamic sidebar menu ανάλογα με ρόλο
- Permission Guards σε controllers

### 📦 Κατάλογος Υλικών & Έξυπνη Αναζήτηση
- 🔍 Greeklish Search — "kalodio" βρίσκει "Καλώδιο"
- 🌍 Synonym Matching — "cable" βρίσκει "Καλώδιο"
- 🤖 Auto-Aliases — αυτόματη δημιουργία λέξεων-κλειδιών
- ⚡ Real-time Autocomplete
- 📊 Category Management
- 💰 Auto-Fill Prices από κατάλογο

### 📄 Προσφορές & Τιμολόγηση
- Δημιουργία επαγγελματικών προσφορών σε PDF
- Μετατροπή προσφοράς σε έργο
- Παρακολούθηση πληρωμών και υπολοίπων

### 📱 Mobile-Friendly
- Responsive design για όλες τις συσκευές
- Camera API για φωτογραφίες έργων

## 🛠️ Τεχνολογίες

- **Backend**: PHP 7.4+ με MVC αρχιτεκτονική
- **Database**: MySQL 5.7+ / MariaDB 10.2+
- **Frontend**: Bootstrap 5, JavaScript (ES6+)
- **Charts**: Chart.js 4.4.0
- **Icons**: Font Awesome 6
- **Export**: TCPDF, PHPWord, PHPSpreadsheet

## 📋 Απαιτήσεις Συστήματος

- PHP 7.4 ή νεότερο
- MySQL 5.7 ή MariaDB 10.2+
- Apache ή Nginx web server
- 50MB ελεύθερος χώρος δίσκου
- SSL Certificate (προτείνεται)

## 🚀 Εγκατάσταση

### 1. Κλωνοποίηση του Repository

```bash
git clone https://github.com/TheoSfak/handycrm.git
cd handycrm
```

### 2. Ρύθμιση Βάσης Δεδομένων

```sql
CREATE DATABASE handycrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
mysql -u username -p handycrm < database/schema.sql
mysql -u username -p handycrm < database/sample_data.sql
```

### 3. Ρύθμιση Εφαρμογής

```bash
cp config/config.example.php config/config.php
```

Ενημερώστε τις ρυθμίσεις στο `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'handycrm');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('BASE_URL', 'http://your-domain.com/handycrm');
```

### 4. Πρώτη Σύνδεση

- **Username**: admin  
- **Password**: admin123  

⚠️ **Αλλάξτε αμέσως τον κωδικό του διαχειριστή!**

## 🔄 Ενημέρωση (Update)

### Αυτόματη (Συνιστάται)

1. **Ρυθμίσεις → Ενημέρωση**
2. Κλικ "Έλεγχος για Ενημέρωση"
3. Κλικ "Ενημέρωση Τώρα"

### Χειροκίνητη

1. Backup βάσης δεδομένων
2. Κατεβάστε από [GitHub Releases](https://github.com/TheoSfak/handycrm/releases)
3. Αντικαταστήστε τα αρχεία (**μην διαγράψετε** το `config/config.php`)
4. Τα migrations τρέχουν αυτόματα κατά το πρώτο login

## 📁 Δομή Έργου

```
handycrm/
├── classes/           # Core PHP classes
├── config/            # Configuration files
├── controllers/       # MVC Controllers
├── models/            # MVC Models
├── views/             # MVC Views
├── migrations/        # SQL migration files
├── database/          # Schema & sample data
├── uploads/           # Uploaded files
├── assets/            # Static assets
└── index.php          # Application entry point
```

## 🔄 Changelog

### v1.7.6 — 2026-03-11
- 📄 Συμφωνητικό: Πλήρεις νομικοί Γενικοί Όροι Τιμολογίου (10 σημεία) και Λοιποί Όροι (4 νομικές ρήτρες: έκπτωτος εργολάβος, εντεχνία, ασφάλεια εργαζομένων, σταθερή τιμή + δωσιδικία Ηρακλείου)

### v1.7.5 — 2026-03-11
- 🔄 Συμφωνητικό: Αφαίρεση στήλης κόστους από πίνακα χρονοδιαγράμματος εργασιών

### v1.7.4 — 2026-03-11
- 🔄 Συμφωνητικό: Ενοποίηση κόστους υλικών + εργασίας σε única γραμμή "Κόστος υλικών και εργασίας = X €"

### v1.7.3 — 2026-03-11
- 🐛 Συμφωνητικό: Ελληνικό όνομα αρχείου (`Συμφωνητικό_...`), RFC 5987 encoding, αλλαγή default πόλης σε Ηράκλειο Κρήτης

### v1.7.2 — 2026-03-11
- 🐛 Συμφωνητικό: Διόρθωση — το αρχείο Word πλέον κατεβαίνει αντί να ανοίγει στο browser (`Content-Disposition: attachment`)

### v1.7.1 — 2026-03-11
- ✨ **Νέο: Συμφωνητικό** — Κουμπί στη σελίδα έργου που παράγει Word (.docx) συμφωνητικό με στοιχεία πελάτη, εργασίες, κόστος, ΦΠΑ, πίνακα πληρωμών και υπογραφές

### v1.7.0 — 2026-03-10
- 🐛 Διόρθωση: Διπλά κόστη/υλικά μετά από αντιγραφή και διαγραφή εργασίας (soft-delete) — όλα τα queries εξαιρούν πλέον `deleted_at IS NULL`
- 🐛 Διόρθωση: PDF Αναφορά έργου συμπεριλάμβανε κόστη από διαγραμμένες εργασίες
- 🐛 Διόρθωση: Badge αριθμού εργασιών έδειχνε διαγραμμένες εργασίες
- 🐛 Διόρθωση: `calculated_total_cost` στη λίστα έργων συμπεριλάμβανε κόστη deleted tasks

### v1.6.9 — 2026-02-06
- 🔴 Critical fixes: UpdateController, TrashController, Settings Model, Material Model, TechniciansController
- 🛡️ Security: 38+ XSS fixes, CSRF protection, CSRF meta tag στο header
- ⚡ Performance: User data caching σε `getCurrentUser()` (εξάλειψη N+1)
- 🌍 Translations: Προσθήκη `common.update` / `common.create`

### v1.6.8
- Διάφορες βελτιώσεις και διορθώσεις

### v1.6.5
- Διάφορες βελτιώσεις και διορθώσεις

### v1.6.0
- Προσθήκη PDF/Word/Excel export λειτουργιών

### v1.5.0
- Ενημέρωση διαχείρισης αναφορών και στατιστικών

### v1.4.0
- Βελτιώσεις UI/UX, νέα χαρακτηριστικά διαχείρισης υλικών

### v1.3.6 — 2025-10-22
- Αφαίρεση Invoices module — τα κόστη υπολογίζονται πλέον από εργασίες
- Νέο σύστημα υπολογισμού κόστους βάσει `task_labor` + `task_materials`

### v1.3.5 — 2025-10-21
- ✨ Advanced Payment Management (statistics, date presets, CSV export, bulk actions)
- ✨ Role-Based Access Control (Admin, Supervisor, Technician, Assistant)
- 🐛 PHP foreach reference bug (duplicate technician cards)

### v1.3.0
- Σύστημα ρόλων και δικαιωμάτων (αρχική υλοποίηση)

### v1.2.5
- Βελτιώσεις Greeklish search και autocomplete υλικών

### v1.2.0
- ✨ Upload φωτογραφιών έργου με Lightbox gallery
- ✨ Κατάλογος Υλικών με Greeklish/synonym search, auto-aliases

### v1.1.3 / v1.1.2 / v1.1.1
- Bugfixes στο σύστημα εργασιών έργου

### v1.1.0
- ✨ Σύστημα Εργασιών Έργου (project tasks) με υλικά, εργατικά, ημερήσια κόστη
- Στατιστικά έργου με Chart.js

### v1.0.6 / v1.0.4 / v1.0.3 / v1.0.2 / v1.0.1
- Bugfixes και βελτιώσεις αρχικής έκδοσης

### v1.0.0 — Αρχική Έκδοση
- Διαχείριση πελατών, έργων, ραντεβού, προσφορών
- Βασική διαχείριση υλικών
- Σύστημα authentication
- Responsive UI (Bootstrap 5)

## 🔍 Troubleshooting

**Πρόβλημα**: Λάθος 500 κατά τη σύνδεση  
**Λύση**: Ελέγξτε τις ρυθμίσεις βάσης δεδομένων στο `config/config.php`

**Πρόβλημα**: Δεν φορτώνουν οι εικόνες  
**Λύση**: `chmod 755 uploads/`

**Πρόβλημα**: Δεν λειτουργεί το URL rewriting  
**Λύση**: Ενεργοποιήστε `mod_rewrite` στον Apache

## 📞 Υποστήριξη

- **Email**: theodore.sfakianakis@gmail.com
- **Issues**: https://github.com/TheoSfak/handycrm/issues
- **Releases**: https://github.com/TheoSfak/handycrm/releases

## 📄 Άδεια Χρήσης

**Copyright © 2025–2026 Theodore Sfakianakis**

- ✓ Ελεύθερη χρήση για προσωπικούς και επαγγελματικούς σκοπούς
- ✓ Επιτρέπεται η αναδιανομή με αναφορά στον δημιουργό
- ✗ ΔΕΝ επιτρέπεται η πώληση ή χρέωση τελών
- ✗ ΔΕΝ επιτρέπεται η τροποποίηση χωρίς άδεια
- ✗ ΔΕΝ επιτρέπεται η αφαίρεση των πνευματικών δικαιωμάτων

Δείτε το αρχείο [LICENSE](LICENSE) για πλήρεις λεπτομέρειες.

---

**HandyCRM** - Κάνοντας τη διαχείριση των πελατών σας εύκολη και αποδοτική! 🔧
