# ⚡ QUICK GUIDE - SMTP Setup για Password Reset

## 🎯 Τι Πρέπει να Κάνεις ΤΩΡΑ

Το password reset **ΔΕΝ θα στείλει emails** μέχρι να ρυθμίσεις τα SMTP settings!

---

## 📧 ΒΗΜΑ 1: Configure SMTP Settings

### Option A: Web Interface (Εύκολο)

1. **Login** ως admin στο HandyCRM
2. **Navigate** to: `https://ecowatt.gr/crm/email/email-settings-phpmailer.php`
3. **Fill in** τα SMTP settings:

```
SMTP Host: mail.ecowatt.gr  (ή το SMTP server σου)
Port: 587                     (για TLS) ή 465 (για SSL)
Username: noreply@ecowatt.gr  (το email account)
Password: [YOUR_EMAIL_PASSWORD]
Encryption: TLS               (ή SSL)
From Email: noreply@ecowatt.gr
From Name: ECOWATT HandyCRM
```

4. **Save Settings**
5. **Send Test Email** με το δικό σου email
6. **Check inbox** - θα πρέπει να λάβεις test email

---

### Option B: Direct SQL (Γρήγορο)

```sql
-- Connect to database
mysql -u u858321845_handycrm -p u858321845_handycrm

-- Insert SMTP settings
INSERT INTO smtp_settings (host, port, username, password, encryption, from_email, from_name)
VALUES (
    'mail.ecowatt.gr',
    587,
    'noreply@ecowatt.gr',
    'YOUR_PASSWORD_HERE',
    'tls',
    'noreply@ecowatt.gr',
    'ECOWATT HandyCRM'
)
ON DUPLICATE KEY UPDATE
    host = 'mail.ecowatt.gr',
    port = 587,
    username = 'noreply@ecowatt.gr',
    password = 'YOUR_PASSWORD_HERE',
    encryption = 'tls',
    from_email = 'noreply@ecowatt.gr',
    from_name = 'ECOWATT HandyCRM';
```

---

## 🔐 SMTP Credentials για διάφορους providers

### cPanel / Shared Hosting (ecowatt.gr)
```
Host: mail.ecowatt.gr
Port: 587 (TLS) ή 465 (SSL)
Username: noreply@ecowatt.gr
Password: [Το password του email account]
Encryption: TLS ή SSL
```

### Gmail (Backup option)
```
Host: smtp.gmail.com
Port: 587
Username: your-email@gmail.com
Password: [App Password - ΟΧΙ το κανονικό password!]
Encryption: TLS

⚠️ ΣΗΜΑΝΤΙΚΟ για Gmail:
1. Πήγαινε στο https://myaccount.google.com/apppasswords
2. Create App Password για "Mail"
3. Χρησιμοποίησε αυτό το 16-digit password
```

### Office 365 / Outlook
```
Host: smtp.office365.com
Port: 587
Username: your-email@outlook.com
Password: [Your password]
Encryption: TLS
```

---

## ✅ ΒΗΜΑ 2: Upload το Updated File

Upload to production:
```
controllers/AuthController.php
```

---

## 🧪 ΒΗΜΑ 3: Test Password Reset

1. **Logout** από το HandyCRM
2. Click **"Ξέχασα τον κωδικό μου"**
3. Enter: **admin@ecowatt.gr** (ή άλλο valid email)
4. Click **Submit**
5. **Check email inbox** - θα λάβεις HTML email με reset link
6. Click το link και άλλαξε password
7. **Success!** 🎉

---

## 🐛 Troubleshooting

### "Email not received"

**Check 1: SMTP Settings**
```sql
SELECT * FROM smtp_settings;
```
Αν είναι άδειο → Configure SMTP settings!

**Check 2: Email Log**
```sql
SELECT * FROM email_notifications 
WHERE type = 'password_reset' 
ORDER BY created_at DESC 
LIMIT 5;
```
- `status = 'sent'` → Email στάλθηκε, έλεγξε spam
- `status = 'failed'` → Δες το `error_message` column

**Check 3: SMTP Test**
1. Go to `/email/email-settings-phpmailer.php`
2. Test Email section
3. Send test to your email
4. If fails → SMTP credentials wrong

---

### Common Issues

**❌ "SMTP connect() failed"**
- Wrong host or port
- Firewall blocking
- SSL/TLS mismatch

**Fix:** Double-check host, port, encryption

---

**❌ "Authentication failed"**
- Wrong username/password
- Gmail: Need App Password

**Fix:** Verify credentials, use App Password for Gmail

---

**❌ "No error but email not received"**
- Spam folder
- Email filtering rules
- Domain not verified (some SMTP require domain verification)

**Fix:** Check spam, check email server logs

---

## 📊 Verify It's Working

### Check Email Logs
```sql
SELECT 
    recipient_email,
    subject,
    status,
    error_message,
    created_at
FROM email_notifications
WHERE type = 'password_reset'
ORDER BY created_at DESC
LIMIT 10;
```

### Success Indicators:
- ✅ `status = 'sent'`
- ✅ `error_message = NULL`
- ✅ Email received in inbox

---

## 🎯 Summary

1. **Configure SMTP** → `/email/email-settings-phpmailer.php` ή SQL
2. **Upload** → `controllers/AuthController.php`
3. **Test** → Forgot password flow
4. **Check** → Email inbox & logs

**Μόλις ρυθμίσεις τα SMTP settings, το password reset θα δουλεύει τέλεια!**

---

## 📝 Important Notes

- **Security:** Το system δεν αποκαλύπτει αν το email υπάρχει (πάντα λέει "θα λάβετε email")
- **Expiry:** Reset tokens λήγουν σε 1 ώρα
- **Logging:** Όλα τα emails καταγράφονται για debugging
- **Fallback:** Αν SMTP δεν είναι configured, δεν θα σταλεί email αλλά δεν θα crashάρει

---

**Status:** ✅ Code is ready - Just configure SMTP!  
**ETA:** 5 λεπτά για setup SMTP  
**Date:** 7 Νοεμβρίου 2025
