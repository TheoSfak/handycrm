# HandyCRM Migration & Installation System

## Για Clean Installation (Νέα Εγκατάσταση)

1. Ανέβασε όλα τα αρχεία στον server
2. Τρέξε το `install.php` από τον browser
3. Ακολούθησε τα βήματα εγκατάστασης

Το install.php θα:
- Δημιουργήσει το database
- Τρέξει όλα τα SQL schemas
- Δημιουργήσει τον πρώτο χρήστη admin
- Ρυθμίσει το config.php

## Για Update από Παλιότερη Έκδοση

### Αυτόματο (Προτεινόμενο)

1. Πήγαινε στο **Ρυθμίσεις → Ενημέρωση**
2. Κλικ στο "Έλεγχος για Ενημέρωση"
3. Αν υπάρχει νέα έκδοση, κλικ "Ενημέρωση"

Το σύστημα θα:
- Κατεβάσει την νέα έκδοση από GitHub
- Θα κάνει αυτόματα backup της βάσης
- Θα τρέξει όλα τα απαραίτητα migrations
- Θα αντικαταστήσει τα αρχεία

### Χειροκίνητο

1. Κάνε backup της βάσης δεδομένων
2. Κατέβασε την νέα έκδοση από GitHub
3. Αντικατάστησε τα αρχεία (πρόσεχε να μην διαγράψεις το config.php)
4. Τρέξε το migration script ανάλογα με την παλιά σου έκδοση:

```bash
# Από 1.0.6 → 1.1.0
php migrations/migrate_to_1.1.0.php
```

## Migration Files

Τα migration files βρίσκονται στο φάκελο `migrations/`:

- `add_language_column.sql` - Προσθέτει language στους users
- `add_project_tasks_system.sql` - Προσθέτει το σύστημα εργασιών έργων
- `migrate_to_1.1.0.php` - Full migration script για v1.0.6 → v1.1.0

## Database Schema

Το πλήρες database schema βρίσκεται στο:
- `database/schema.sql` - Complete database structure
- `database/handycrm.sql` - Production-ready SQL για install

## Αυτόματο Migration System (v1.1.3+)

Από την έκδοση 1.1.3 και μετά, το σύστημα τρέχει αυτόματα τα migrations:

1. Όταν ενημερώνεις μέσω του UI
2. Όταν φορτώνεις το σύστημα και βλέπει ότι το VERSION είναι νεότερο

Τα migrations καταγράφονται στον πίνακα `migrations` για να μην τρέχουν ξανά.

## Troubleshooting

### Αν το migration αποτύχει:

1. Έλεγξε το PHP error log
2. Κάνε restore το backup της βάσης
3. Τρέξε το migration χειροκίνητα μέσω phpMyAdmin
4. Επικοινώνησε για support

### Αν το update δεν βλέπει νέα έκδοση:

1. Πήγαινε στο **Ρυθμίσεις → Ενημέρωση**
2. Κλικ "Καθαρισμός Cache" κάτω από το version info
3. Κάνε refresh τη σελίδα

### GitHub API Rate Limit:

Αν βλέπεις "rate limit exceeded", πρόσθεσε GitHub token στο `config/config.php`:

```php
define('GITHUB_TOKEN', 'your_github_token_here');
```

Πώς να πάρεις token:
1. GitHub → Settings → Developer settings → Personal access tokens
2. Generate new token (classic)
3. Επέλεξε: public_repo (μόνο αυτό)
4. Copy token και βάλ' το στο config

## Support

Για προβλήματα ή ερωτήσεις:
- GitHub Issues: https://github.com/TheoSfak/handycrm/issues
- Documentation: https://github.com/TheoSfak/handycrm/wiki
