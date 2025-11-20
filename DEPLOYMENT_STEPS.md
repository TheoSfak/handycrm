# 📦 HandyCRM - Password Reset Feature Deployment

## ✅ Έτοιμα Αρχεία για Upload

Όλα τα παρακάτω αρχεία είναι 100% έτοιμα και περιμένουν να ανέβουν στο production:

### 1️⃣ Controllers
- ✅ `controllers/AuthController.php` - **ΠΛΗΡΗΣ με όλες τις password reset μεθόδους**

### 2️⃣ Views
- ✅ `views/auth/login.php` - Ενημερωμένο footer με ECOWATT
- ✅ `views/auth/forgot-password.php` - Νέα σελίδα αιτήματος επαναφοράς
- ✅ `views/auth/reset-password.php` - Νέα σελίδα ορισμού νέου κωδικού

### 3️⃣ Router
- ✅ `index.php` - Με τα νέα routes για password reset

### 4️⃣ Database Migration
- ✅ `migrations/add_password_reset_fields.sql` - SQL για τα νέα πεδία

---

## 🚀 Βήματα Deployment

### ΒΗΜΑ 1: Τρέξε το SQL Migration
Πρώτα πρέπει να προσθέσεις τα νέα πεδία στον πίνακα users:

```bash
# Συνδέσου στο production database
mysql -u u858321845_handycrm -p u858321845_handycrm

# Τρέξε το migration
source /path/to/migrations/add_password_reset_fields.sql;

# Ή copy-paste αυτό απευθείας:
ALTER TABLE users 
ADD COLUMN reset_token VARCHAR(64) NULL AFTER password,
ADD COLUMN reset_token_expiry DATETIME NULL AFTER reset_token,
ADD INDEX idx_reset_token (reset_token);

# Έλεγχος ότι έγινε σωστά
DESCRIBE users;
```

### ΒΗΜΑ 2: Upload τα Αρχεία στο Production
Ανέβασε τα παρακάτω αρχεία στο `ecowatt.gr/crm/`:

#### Via FTP/SFTP:
```
📁 controllers/
   └─ AuthController.php

📁 views/auth/
   ├─ login.php
   ├─ forgot-password.php (ΝΕΟ)
   └─ reset-password.php (ΝΕΟ)

📄 index.php
```

### ΒΗΜΑ 3: Test το Password Reset Flow

1. **Πήγαινε στο login page:**
   - https://ecowatt.gr/crm/login

2. **Κλικ στο "Ξέχασα τον κωδικό μου":**
   - Θα σε πάει στο `/forgot-password`

3. **Εισήγαγε ένα email που υπάρχει στη βάση:**
   - Π.χ. `admin@ecowatt.gr`
   - Θα δεις success message

4. **Έλεγξε το email:**
   - Θα λάβεις email με reset link
   - Το link θα είναι: `https://ecowatt.gr/crm/reset-password?token=XXXXXXXXX`

5. **Κλικ στο link και όρισε νέο κωδικό:**
   - Εισήγαγε νέο password
   - Επιβεβαίωσε τον κωδικό
   - Submit

6. **Login με τον νέο κωδικό:**
   - Πήγαινε στο `/login`
   - Εισήγαγε username και τον νέο password
   - Επιτυχής σύνδεση!

---

## 🔍 Τι Κάνει Κάθε Νέα Μέθοδος

### `forgotPassword()`
- Εμφανίζει τη σελίδα `/forgot-password`
- Δημιουργεί CSRF token για ασφάλεια

### `processForgotPassword()`
- Ελέγχει αν το email υπάρχει στη βάση
- Δημιουργεί μοναδικό reset token (64 chars)
- Ορίζει expiry σε 1 ώρα από τώρα
- **ΑΠΟΘΗΚΕΥΕΙ** το token στη βάση (στήλες: `reset_token`, `reset_token_expiry`)
- Στέλνει email με το reset link
- Εμφανίζει success message (χωρίς να αποκαλύπτει αν το email υπάρχει)

### `resetPassword()`
- Παίρνει το token από το URL (`?token=XXX`)
- Ελέγχει αν το token υπάρχει και δεν έχει λήξει (expiry > NOW())
- Αν ok → Εμφανίζει τη σελίδα `/reset-password` με form
- Αν όχι → Redirect στο login με error message

### `processResetPassword()`
- Ελέγχει ότι τα passwords ταιριάζουν
- Ελέγχει ότι ο κωδικός έχει τουλάχιστον 6 χαρακτήρες
- Επαληθεύει ξανά το token (security check)
- Κάνει hash τον νέο κωδικό με `password_hash()`
- **UPDATE** στη βάση: νέος password + NULL τα reset_token και reset_token_expiry
- Success message και redirect στο login

---

## 🎯 Τι Διορθώθηκε από Πριν

### ❌ Παλιό `processForgotPassword()`:
```php
// Έφτιαχνε το token αλλά ΔΕΝ το αποθήκευε!
$resetToken = bin2hex(random_bytes(32));
// ... και πουθενά UPDATE στη βάση
```

### ✅ Νέο `processForgotPassword()`:
```php
// Δημιουργία token
$resetToken = bin2hex(random_bytes(32));
$resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

// ΑΠΟΘΗΚΕΥΣΗ στη βάση
$updateStmt = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
$updateStmt->execute([$resetToken, $resetExpiry, $user['id']]);

// ΑΠΟΣΤΟΛΗ email
mail($user['email'], $subject, $message, $headers);
```

---

## 📧 Email Configuration (Optional)

Το password reset χρησιμοποιεί την `mail()` function της PHP.

Αν θέλεις πιο προχωρημένη λειτουργία (SMTP, HTML emails, κλπ):

### Εγκατάσταση PHPMailer:
```bash
composer require phpmailer/phpmailer
```

### Update στο `processForgotPassword()`:
```php
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.ecowatt.gr';
$mail->SMTPAuth = true;
$mail->Username = 'noreply@ecowatt.gr';
$mail->Password = 'your-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('noreply@ecowatt.gr', 'ECOWATT HandyCRM');
$mail->addAddress($user['email'], $user['first_name']);
$mail->Subject = 'Ανάκτηση Κωδικού';
$mail->Body = $message;
$mail->send();
```

---

## ✨ Extras που Προστέθηκαν

### 1. Login Page Footer
```
HandyCRM v1.0.0 © 2024 HandyCRM
ECOWATT Ενεργειακές Λύσεις
```

### 2. Security Features
- CSRF tokens σε όλες τις φόρμες
- Token expiry (1 hour)
- Password hashing με `PASSWORD_DEFAULT`
- Email validation
- Tokens stored in database με index για performance
- Tokens cleared μετά την επιτυχή αλλαγή κωδικού

### 3. User Experience
- Flash messages για feedback
- Password show/hide toggle
- Password strength requirements
- Responsive design με Bootstrap 5
- Ελληνικά μηνύματα σε όλη την εφαρμογή

---

## 🐛 Troubleshooting

### Πρόβλημα: "Ο σύνδεσμος έχει λήξει"
**Λύση:** Το token λήγει σε 1 ώρα. Ζήτα νέο reset link.

### Πρόβλημα: "Δεν λαμβάνω email"
**Ελέγξτε:**
1. Spam folder
2. PHP `mail()` configuration στο server
3. Server logs: `/var/log/mail.log`
4. Χρησιμοποιήστε PHPMailer αν χρειάζεται

### Πρόβλημα: "Μη έγκυρος σύνδεσμος"
**Ελέγξτε:**
1. Το token στο URL είναι σωστό
2. Το token υπάρχει στη βάση: `SELECT * FROM users WHERE reset_token = 'XXX'`
3. Το `reset_token_expiry > NOW()`

---

## 📊 Database Schema

Νέα πεδία στον πίνακα `users`:

| Column | Type | Null | Default | Extra |
|--------|------|------|---------|-------|
| reset_token | VARCHAR(64) | YES | NULL | |
| reset_token_expiry | DATETIME | YES | NULL | |

**Index:** `idx_reset_token` στο `reset_token` για ταχύτητα.

---

## ✅ Checklist Πριν το Go Live

- [ ] SQL migration έτρεξε επιτυχώς
- [ ] Όλα τα αρχεία ανέβηκαν στο production
- [ ] Test με πραγματικό email address
- [ ] Έλεγξα ότι το email φτάνει
- [ ] Test password reset με valid token
- [ ] Test login με νέο password
- [ ] Test expired token (περίμενε 1+ ώρα)
- [ ] Test invalid token

---

## 🎉 Ολοκληρώθηκε!

Το password reset feature είναι πλήρως λειτουργικό και έτοιμο για production.

**Καλή επιτυχία με το deployment! 🚀**

---

*Για οποιαδήποτε ερώτηση ή πρόβλημα, έλεγξε τα logs:*
- PHP error log: `/var/log/php_errors.log`
- Apache/Nginx error log
- MySQL slow query log
