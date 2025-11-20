# 📧 SMTP Email Fix για Password Reset

## 🐛 Το Πρόβλημα

Το password reset δεν έστελνε emails γιατί χρησιμοποιούσε τη βασική `mail()` function της PHP που δεν δουλεύει σε shared hosting.

## ✅ Η Λύση

Ενημερώθηκε το `AuthController.php` να χρησιμοποιεί την υπάρχουσα **`EmailService` class** με **PHPMailer** και **SMTP credentials** από τη βάση δεδομένων.

---

## 🔧 Τι Άλλαξε

### ΠΡΙΝ (δεν δούλευε):
```php
// Βασική mail() function
$headers = "From: noreply@ecowatt.gr\r\n";
mail($user['email'], $subject, $message, $headers);
```

### ΜΕΤΑ (δουλεύει με SMTP):
```php
// EmailService με PHPMailer
require_once __DIR__ . '/../classes/EmailService.php';
$emailService = new EmailService($db);

if ($emailService->isConfigured()) {
    $mail = $emailService->createMailer();
    $mail->addAddress($user['email']);
    $mail->isHTML(true);
    $mail->Subject = 'Ανάκτηση Κωδικού - HandyCRM';
    $mail->Body = '...HTML email...';
    $mail->send();
}
```

---

## 📋 SMTP Configuration

Το σύστημα χρησιμοποιεί SMTP credentials από τον πίνακα `smtp_settings`.

### Πώς να ρυθμίσεις τα SMTP settings:

#### Option 1: Μέσω Web Interface
1. Login ως **admin**
2. Πήγαινε στο `/email/email-settings-phpmailer.php`
3. Συμπλήρωσε:
   - SMTP Host (π.χ. `smtp.gmail.com` ή `mail.ecowatt.gr`)
   - Port (587 για TLS, 465 για SSL)
   - Username (το email σου)
   - Password (App Password αν Gmail)
   - Encryption (TLS ή SSL)
   - From Email
   - From Name

#### Option 2: Απευθείας στη Βάση
```sql
INSERT INTO smtp_settings (host, port, username, password, encryption, from_email, from_name)
VALUES (
    'mail.ecowatt.gr',
    587,
    'noreply@ecowatt.gr',
    'YOUR_PASSWORD',
    'tls',
    'noreply@ecowatt.gr',
    'ECOWATT HandyCRM'
);
```

---

## 🎨 Το Email που Στέλνεται

### HTML Format με:
- ✅ ECOWATT branding
- ✅ Κουμπί "Επαναφορά Κωδικού"
- ✅ Backup link (αν το κουμπί δεν δουλεύει)
- ✅ Warning για 1-hour expiry
- ✅ Security notice
- ✅ Professional footer
- ✅ UTF-8 encoding για Ελληνικά

### Plain Text Alternative
Για email clients που δεν υποστηρίζουν HTML

---

## 🧪 Testing

### Test SMTP Settings:
1. Login ως admin
2. `/email/email-settings-phpmailer.php`
3. Scroll down to "Test Email"
4. Εισάγαγε το email σου
5. Click "Send Test Email"
6. Έλεγξε το inbox

### Test Password Reset:
1. Logout
2. Click "Ξέχασα τον κωδικό μου"
3. Εισάγαγε valid email
4. Check email inbox
5. Click reset link
6. Set new password

---

## 📊 Email Logging

Τα emails καταγράφονται στον πίνακα `email_notifications`:

```sql
SELECT * FROM email_notifications 
WHERE type = 'password_reset' 
ORDER BY created_at DESC 
LIMIT 10;
```

Μπορείς να δεις:
- ✅ Ποια emails στάλθηκαν
- ✅ Status (sent/failed)
- ✅ Error messages αν απέτυχαν
- ✅ Timestamps

---

## 🚨 Troubleshooting

### "Email not received"
1. **Έλεγξε SMTP settings:** Πήγαινε στο `/email/email-settings-phpmailer.php`
2. **Test connection:** Στείλε test email
3. **Check spam folder**
4. **Check logs:** 
   ```sql
   SELECT * FROM email_notifications WHERE status = 'failed';
   ```

### "SMTP not configured" στα logs
1. Πήγαινε στο `/email/email-settings-phpmailer.php`
2. Συμπλήρωσε όλα τα SMTP fields
3. Save settings
4. Test email

### Gmail Issues
- ✅ Χρησιμοποίησε **App Password** (όχι κανονικό password)
- ✅ Enable "Less secure apps" ή better: Use App Password
- ✅ Host: `smtp.gmail.com`
- ✅ Port: 587
- ✅ Encryption: TLS

### cPanel/Shared Hosting
- ✅ Host: `mail.yourdomain.com` ή `localhost`
- ✅ Port: 587 (TLS) ή 465 (SSL)
- ✅ Username: το email account που έφτιαξες
- ✅ Password: το password του email account

---

## 📦 Files Changed

### 1. `controllers/AuthController.php`
**Line ~230:** Updated `processForgotPassword()` method
- Αντικατέστησε `mail()` με `EmailService`
- Πρόσθεσε HTML email template
- Πρόσθεσε error handling
- Πρόσθεσε email logging

---

## ✅ Deployment

**Ανέβασε στο production:**
```
controllers/AuthController.php
```

**Έλεγξε ότι υπάρχουν:**
- ✅ `classes/EmailService.php`
- ✅ `vendor/phpmailer/` (PHPMailer library)
- ✅ Πίνακας `smtp_settings` στη βάση
- ✅ Πίνακας `email_notifications` στη βάση

---

## 🎯 Next Steps

1. **Upload το AuthController.php**
2. **Configure SMTP settings** στο `/email/email-settings-phpmailer.php`
3. **Test password reset** με το δικό σου email
4. **Done!** 🎉

---

## 📝 Notes

- Το EmailService ελέγχει αν τα SMTP settings είναι configured πριν στείλει
- Αν δεν είναι configured, δεν θα στείλει email αλλά δεν θα δώσει error στον user
- Όλα τα errors καταγράφονται στο error log
- Το success message εμφανίζεται πάντα (security - δεν αποκαλύπτει αν το email υπάρχει)

---

**Last Updated:** 7 Νοεμβρίου 2025  
**Status:** ✅ FIXED & READY
