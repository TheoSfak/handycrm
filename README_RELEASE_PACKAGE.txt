╔════════════════════════════════════════════════════════════════╗
║           HandyCRM v1.3.7 - Release Package Index            ║
║                                                                ║
║  📦 Αυτό το folder περιέχει ΟΛΑ όσα χρειάζεσαι για            ║
║     να αναβαθμίσεις το ecowatt.gr/crm                         ║
╚════════════════════════════════════════════════════════════════╝

📁 C:\Users\user\Desktop\handycrm

═══════════════════════════════════════════════════════════════

📚 DOCUMENTATION (ΔΙΑΒΑΣΕ ΠΡΩΤΑ!)
═══════════════════════════════════════════════════════════════

📄 RELEASE_1.3.7_README.md
   → Αναλυτικές οδηγίες εγκατάστασης (ΔΙΑΒΑΣΕ ΑΥΤΟ ΠΡΩΤΑ!)
   → Βήμα-βήμα instructions
   → Troubleshooting tips

📄 RELEASE_1.3.7_SUMMARY.txt
   → Συνοπτική περιγραφή του release
   → Τι νέο, τι έχει διορθωθεί
   → Quick overview

📄 RELEASE_1.3.7_FILES_TO_UPLOAD.txt
   → Λίστα με όλα τα αρχεία που πρέπει να ανεβάσεις
   → Απλοποιημένος οδηγός

📄 DEPLOYMENT_CHECKLIST.txt
   → Checklist που μπορείς να τσεκάρεις κατά το deployment
   → Print it και τικάρε τα!

═══════════════════════════════════════════════════════════════

🗄️ DATABASE
═══════════════════════════════════════════════════════════════

📄 RELEASE_1.3.7_MIGRATION.sql
   → SQL script για database updates
   → Προσθέτει νέες στήλες στο task_labor
   → ΤΡΕΞΕ ΑΥΤΟ ΠΡΩΤΑ στο phpMyAdmin!

═══════════════════════════════════════════════════════════════

🔧 VALIDATION TOOL
═══════════════════════════════════════════════════════════════

📄 validate_install.php
   → PHP script για έλεγχο εγκατάστασης
   → Ανέβασέ το στο /crm/ και τρέξε το
   → ΔΙΑΓΡΑΨΕ ΤΟ μετά τον έλεγχο!

═══════════════════════════════════════════════════════════════

📂 SOURCE CODE (ΑΝΕΒΑΣΕ ΑΥΤΑ)
═══════════════════════════════════════════════════════════════

🔷 CONTROLLERS (2 files):
   ├─ controllers/ProjectReportController.php
   └─ controllers/ProjectTasksController.php

🔷 VIEWS (4 files):
   ├─ views/projects/show.php
   ├─ views/projects/tasks/add.php
   ├─ views/projects/tasks/edit.php
   └─ views/users/index.php

🔷 LANGUAGES (1 file):
   └─ languages/el.json

🔷 ASSETS (1 file):
   └─ assets/js/date-formatter.js

🔷 LIBRARIES (1 folder):
   └─ lib/tcpdf/ (ΟΛΟΚΛΗΡΟΣ! ~10MB)

🔷 CONFIG (1 file - MANUAL EDIT!):
   └─ config/config.php
      ⚠️ ΜΗΝ το ανεβάσεις ολόκληρο!
      ⚠️ Άλλαξε μόνο 2 συναρτήσεις (δες README)

═══════════════════════════════════════════════════════════════

🚀 QUICK START
═══════════════════════════════════════════════════════════════

1. 📖 Διάβασε το RELEASE_1.3.7_README.md
2. 💾 Κάνε backup (βάση + αρχεία)
3. 🗄️ Τρέξε το RELEASE_1.3.7_MIGRATION.sql
4. 📤 Ανέβασε τα αρχεία (δες FILES_TO_UPLOAD.txt)
5. ✏️ Edit το config.php χειροκίνητα (δες README)
6. ✅ Τρέξε το validate_install.php
7. 🧪 Κάνε testing
8. 🗑️ Διάγραψε το validate_install.php

═══════════════════════════════════════════════════════════════

⏱️ ESTIMATED TIME
═══════════════════════════════════════════════════════════════

- Backup: 5 minutes
- Database migration: 1 minute
- File upload: 5-10 minutes (ανάλογα με το lib/ folder)
- Config edit: 2 minutes
- Validation: 1 minute
- Testing: 5 minutes
─────────────────────────────────────────────────────────────
TOTAL: ~15-20 minutes

═══════════════════════════════════════════════════════════════

🎯 WHAT GETS FIXED
═══════════════════════════════════════════════════════════════

✅ Currency displays correctly (€ instead of 262145)
✅ Labor entries don't get zeroed on task edit
✅ Dates show as DD/MM/YYYY
✅ PDF reports work properly
✅ All 4 user roles display in users list
✅ Tasks can be created without materials/labor

═══════════════════════════════════════════════════════════════

📞 SUPPORT
═══════════════════════════════════════════════════════════════

Αν κάτι πάει στραβά:
1. Τρέξε το validate_install.php
2. Έλεγξε το README για troubleshooting
3. Restore το backup και ξανάδοκίμασε

═══════════════════════════════════════════════════════════════

✨ Credits: Theodore Sfakianakis
📅 Release Date: October 23, 2025
🔢 Version: 1.3.7
🌐 Production: ecowatt.gr/crm

═══════════════════════════════════════════════════════════════

🎉 Καλή επιτυχία με το deployment! 🚀

═══════════════════════════════════════════════════════════════
