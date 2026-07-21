# Επιλογή Συγκεκριμένων Εργασιών στην Αναφορά Έργου — Design Spec

**Ημερομηνία:** 2026-07-21
**Κατάσταση:** Εγκεκριμένο design, έτοιμο για implementation plan
**Stack:** PHP (MVC, χωρίς framework), MySQL/MariaDB, Bootstrap 5 + vanilla JS, TCPDF

---

## 1. Σκοπός

Στη σελίδα έργου (`views/projects/show.php`), το κουμπί **«Αναφορά Έργου» (Anafora
Ergou)** ανοίγει ένα modal (`#reportModal`) που παράγει PDF αναφορά μέσω
`ProjectReportController::generate()`. Σήμερα το modal φιλτράρει τις εργασίες
(«ergasies», πίνακας `project_tasks`) που μπαίνουν στην αναφορά **μόνο** με εύρος
ημερομηνιών (`from_date` / `to_date`).

Ο admin θέλει ένα **επιπλέον φίλτρο**: μέσα από ένα project με εργασίες
1, 2, 3, ..., 9, να μπορεί να επιλέξει **χειροκίνητα, με κλικ σε checkboxes,
ακριβώς ποιες εργασίες** θα μπουν στην τρέχουσα αναφορά — πέρα από το φιλτράρισμα
ημερομηνίας που ήδη υπάρχει.

### Απόφαση σχεδίασης (επιβεβαιωμένη με τον χρήστη)

- Το νέο φίλτρο **συνδυάζεται** με το εύρος ημερομηνιών, δεν το αντικαθιστά: το
  εύρος ημερομηνιών στενεύει ποιες εργασίες φαίνονται στη λίστα, και μετά ο admin
  μπορεί να ξε-τσεκάρει συγκεκριμένες μέσα σε αυτό το εύρος.
- Η επιλογή εργασιών **επηρεάζει ολόκληρη την αναφορά**: όχι μόνο ποιες γραμμές
  εμφανίζονται στον πίνακα «ΕΡΓΑΣΙΕΣ», αλλά και τα αθροίσματα Υλικών/Εργατικών και
  τις summary κάρτες — όλα υπολογίζονται ξανά με βάση **μόνο** τις επιλεγμένες
  εργασίες.
- Προεπιλογή: **όλες οι εργασίες τσεκαρισμένες** (η αναφορά συμπεριφέρεται ακριβώς
  όπως σήμερα αν ο admin δεν αγγίξει τίποτα). Αν ξε-τσεκάρει όλες, μπλοκάρεται η
  υποβολή με προειδοποίηση (εκτός αν το project απλά δεν έχει καμία εργασία).
- Η λίστα εργασιών είναι απλή scrollable λίστα με «Επιλογή Όλων / Καμία» — όχι
  search box (θα προστεθεί αργότερα αν χρειαστεί).

---

## 2. Τρέχουσα συμπεριφορά (baseline)

- Modal form: `views/projects/show.php:1204-1360`, POST στο
  `/projects/report/{projectId}`.
- Route: `index.php:248-251` → `ProjectReportController::generate($projectId)`.
- `generate()` διαβάζει `from_date`/`to_date` από POST, καλεί:
  - `getTasks($projectId, $fromDate, $toDate)` → πίνακας ΕΡΓΑΣΙΕΣ
  - `getAggregatedMaterials($projectId, $fromDate, $toDate)` → πίνακας ΥΛΙΚΑ
  - `getAggregatedLabor($projectId, $fromDate, $toDate)` → πίνακας ΗΜΕΡΟΜΙΣΘΙΑ
- Και οι τρεις μέθοδοι κάνουν `JOIN`/`WHERE` πάνω στο `project_tasks pt` με
  `pt.project_id = ?` + προαιρετικό φίλτρο ημερομηνίας (κοινή λογική επικάλυψης
  για `single_day` vs `date_range` tasks, επαναλαμβανόμενη και στις 3 μεθόδους).
- `calculateTotals()` αθροίζει ό,τι επιστρέψουν materials/labor.
- `buildHTMLContent()` / `generatePDF()` απλά renderάρουν ό,τι τους δοθεί — δεν
  ξέρουν τίποτα για φιλτράρισμα.

Το task list tab («Εργασίες» tab μέσα στο project) φορτώνει τα δικά του
`$tasks` στο `ProjectController::details()` με **δικά του** φίλτρα από `$_GET`
(`task_type`, `date_from`, `date_to`, `search`) — αυτά είναι άσχετα με το report
modal και δεν πρέπει να τα επηρεάσουν.

---

## 3. Σχεδίαση — UI (modal αναφοράς)

Νέα ενότητα **«Επιλογή Εργασιών»** μέσα στο `#reportModal`, τοποθετημένη αμέσως
μετά το block ημερομηνιών (`#dateInputs`) και πριν τα checkboxes απόκρυψης τιμών.

```
[x] Όλες οι Ημερομηνίες
    (αν ξε-τσεκαριστεί: εμφανίζονται from_date / to_date)
────────────────────────────────────────
Επιλογή Εργασιών                [Επιλογή Όλων] [Καμία]
┌─────────────────────────────────────────────┐
│ [x] 12/03/2026 — Τοποθέτηση σωληνώσεων       │ ← scrollable,
│ [x] 15/03/2026 — Έλεγχος δικτύου             │   max-height + overflow-y:auto
│ [ ] 18/03/2026 — Επισκευή βλάβης             │
│ ...                                          │
└─────────────────────────────────────────────┘
8 από 9 εργασίες επιλεγμένες
────────────────────────────────────────
[ ] Απόκρυψη Τιμών Εργατικών
...
```

### Πηγή δεδομένων για τη λίστα

Στο `views/projects/show.php`, ακριβώς πριν το modal, ήδη υπάρχει το pattern
(γραμμές ~414-420) που φορτώνει `$otherProjects` απευθείας μέσα στη view για το
«Move Task» modal. Με τον ίδιο τρόπο, θα προστεθεί:

```php
if (!isset($reportTaskOptions)) {
    require_once __DIR__ . '/../../models/ProjectTask.php';
    $_ptm = new ProjectTask();
    $reportTaskOptions = $_ptm->getByProject($project['id'], []); // χωρίς φίλτρα
    unset($_ptm);
}
```

Αυτό εγγυάται ότι η λίστα είναι πάντα το **πλήρες, ανεπηρέαστο** σύνολο εργασιών
του project — όχι ό,τι τυχαίνει να είναι φιλτραρισμένο στο tab «Εργασίες» εκείνη
τη στιγμή.

Κάθε γραμμή renderάρεται ως:

```html
<div class="form-check report-task-row"
     data-type="<?= $task['task_type'] ?>"
     data-date="<?= $task['task_date'] ?>"
     data-date-from="<?= $task['date_from'] ?>"
     data-date-to="<?= $task['date_to'] ?>">
    <input class="form-check-input report-task-checkbox" type="checkbox"
           name="task_ids[]" value="<?= $task['id'] ?>" checked>
    <label class="form-check-label">
        <?= $displayDate ?> — <?= htmlspecialchars($task['description']) ?>
    </label>
</div>
```

Όπου `$displayDate` ακολουθεί ακριβώς το ίδιο pattern που ήδη χρησιμοποιείται στον
πίνακα ΕΡΓΑΣΙΕΣ του PDF (`buildHTMLContent()`, ~γραμμή 683-687): για
`task_type === 'single_day'` δείχνει `task_date` μορφοποιημένη `d/m/Y`· για
`date_range` δείχνει `date_from έως date_to`.

### JS συμπεριφορά

1. **Συγχρονισμός με ημερομηνίες** — συνάρτηση `updateTaskVisibility()` καλείται
   στο `DOMContentLoaded`, και σε κάθε `change` των `allDatesCheck`, `from_date`,
   `to_date`. Για κάθε `.report-task-row`, ελέγχει αν η ημερομηνία(ες) της
   εργασίας επικαλύπτεται με το επιλεγμένο εύρος (ίδια λογική με το backend:
   `single_day` → `task_date BETWEEN from AND to`, `date_range` → επικάλυψη
   διαστημάτων) και δείχνει/κρύβει τη γραμμή (`display: none`) ανάλογα.
   Αν είναι τσεκαρισμένο το «Όλες οι Ημερομηνίες», όλες οι γραμμές είναι ορατές.
   Το checkbox **δεν** αλλάζει κατάσταση όταν κρύβεται — απλώς δεν φαίνεται.
2. **Επιλογή Όλων / Καμία** — δύο `<button type="button">` που τσεκάρουν/
   ξε-τσεκάρουν μόνο τα **ορατά** `.report-task-checkbox` τη στιγμή του κλικ.
3. **Μετρητής** — μετά από κάθε αλλαγή ορατότητας ή τσεκαρίσματος, ενημερώνεται
   ένα `<small>` με «X από Y εργασίες επιλεγμένες» (X = ορατά+τσεκαρισμένα,
   Y = σύνολο ορατών).
4. **Submit guard** — στο `submit` handler της φόρμας (μαζί με το ήδη υπάρχον
   validation), αν υπάρχει τουλάχιστον μία γραμμή εργασίας συνολικά **και** ο
   αριθμός ορατών+τσεκαρισμένων είναι 0 → `preventDefault()` + εμφάνιση
   προειδοποίησης («Επιλέξτε τουλάχιστον μία εργασία»). Αν το project δεν έχει
   καμία εργασία (η λίστα είναι κενή εξαρχής), δεν εφαρμόζεται κανένα guard.

---

## 4. Σχεδίαση — Backend / data flow

Στο `controllers/ProjectReportController.php`:

### `generate($projectId)`

```php
$taskIds = null; // null = καμία επιπλέον περιοριστική συνθήκη (defensive fallback)
if (isset($_POST['task_ids']) && is_array($_POST['task_ids'])) {
    $taskIds = array_values(array_unique(array_filter(
        array_map('intval', $_POST['task_ids']),
        fn($id) => $id > 0
    )));
}
```

- Αν το key `task_ids` λείπει εντελώς από το POST → `null` → καμία αλλαγή
  συμπεριφοράς (fallback ασφαλείας, μια και αυτό είναι το μόνο σημείο κλήσης
  του controller — βλ. §6 — αλλά προστατεύει από μελλοντικές παραλλαγές του
  form).
- Αν υπάρχει αλλά είναι άδειο array (ο admin ξε-τσεκάρισε τα πάντα και το
  client-side guard παρακάμφθηκε κάπως) → κενό array, όχι `null`.

### `getTasks()`, `getAggregatedMaterials()`, `getAggregatedLabor()`

Και οι τρεις παίρνουν νέα παράμετρο `$taskIds = null`:

```php
private function getTasks($projectId, $fromDate = null, $toDate = null, $taskIds = null) {
    if ($taskIds !== null && empty($taskIds)) {
        return []; // καμία επιλεγμένη εργασία → κανένα αποτέλεσμα, χωρίς SQL error
    }
    // ... υπάρχον SQL ...
    if ($taskIds !== null) {
        $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
        $sql .= " AND pt.id IN ($placeholders)";
        $params = array_merge($params, $taskIds);
    }
    // ...
}
```

Το ίδιο pattern (early-return σε κενό array + `AND pt.id IN (...)` στο τέλος,
μετά το υπάρχον φίλτρο ημερομηνίας) εφαρμόζεται και στις άλλες δύο μεθόδους.
Το `pt.id IN (...)` μπαίνει **επιπλέον** του υπάρχοντος φίλτρου ημερομηνίας —
τομή (AND), όχι αντικατάσταση, όπως αποφασίστηκε στο §1.

### Τι ΔΕΝ αλλάζει

- `calculateTotals()`, `buildHTMLContent()`, `generatePDF()`, το email-sending
  path — όλα δουλεύουν πάνω στα ήδη σωστά φιλτραρισμένα `$tasks`/`$materials`/
  `$labor` arrays. Η επιλογή εργασιών «διαπερνά» ολόκληρη την αναφορά χωρίς να
  χρειαστεί καμία αλλαγή στο rendering layer — ακριβώς η "scope everything"
  συμπεριφορά που ζητήθηκε, δωρεάν.

---

## 5. Edge cases & προεπιλογές (σύνοψη)

| Περίπτωση | Συμπεριφορά |
|---|---|
| Project χωρίς καμία εργασία | Άδεια λίστα, κανένα guard, αναφορά όπως σήμερα (κενή ενότητα ΕΡΓΑΣΙΕΣ) |
| «Όλες οι Ημερομηνίες» + καμία αλλαγή από τον admin | Ίδιο αποτέλεσμα με το σημερινό behavior (όλα τσεκαρισμένα, όλα ορατά) |
| Στένεμα εύρους ημερομηνιών | Κρύβει (όχι ξε-τσεκάρει) εργασίες εκτός εύρους· state τους διατηρείται |
| Επιλογή Όλων / Καμία | Επηρεάζει μόνο τις τρέχουσες ορατές γραμμές |
| Ξε-τσεκάρισμα όλων των ορατών εργασιών | Block submit με προειδοποίηση, εκτός αν δεν υπήρχε καμία εργασία εξαρχής |
| `task_ids` key απών από το POST | Backend fallback: καμία επιπλέον restriction (defensive, ο μόνος caller είναι το ίδιο form) |
| `task_ids` = άδειο array | Backend επιστρέφει κενά αποτελέσματα και στις 3 μεθόδους, χωρίς SQL error |
| Email-report path | Ίδια λογική, αφού είναι το ίδιο `generate()` |

---

## 6. Εκτός scope

- **CSRF gap σε `ProjectReportController::generate()`:** ο controller δεν καλεί
  `validateCsrfToken()` καθόλου, παρότι η φόρμα στέλνει CSRF token (προϋπάρχον
  εύρημα από audit, ανεξάρτητο από αυτό το feature). Δεν διορθώνεται εδώ· θα
  σημειωθεί ξεχωριστά ώστε να μη μπλέξει με αυτή την αλλαγή.
- Search/φιλτράρισμα κειμένου μέσα στη λίστα εργασιών του modal (αποφασίστηκε να
  μείνει εκτός για τώρα).
- Οποιαδήποτε αλλαγή στο tab «Εργασίες» του project ή στα δικά του φίλτρα.
- Persistence της επιλογής μεταξύ ανοιγμάτων του modal (κάθε άνοιγμα ξεκινάει
  από την προεπιλογή: όλα τσεκαρισμένα).

---

## 7. Testing plan

Δεν υπάρχει αυτοματοποιημένο test coverage για το reporting flow (επιβεβαιωμένο
από το `tobefixed.md` / audit — `testing/` έχει μόνο exploratory Playwright).
Άρα το testing εδώ είναι **χειροκίνητο, μέσω browser**, μετά το implementation:

1. Project με ≥3 εργασίες, διαφορετικές ημερομηνίες (single_day + date_range) →
   άνοιγμα modal → επιβεβαίωση όλες τσεκαρισμένες, ορατές.
2. Ξε-τσεκάρισμα 1 εργασίας → generate PDF → επιβεβαίωση ότι λείπει από ΕΡΓΑΣΙΕΣ
   **και** τα υλικά/εργατικά της δεν προσμετρώνται στα σύνολα.
3. Εύρος ημερομηνιών που αποκλείει κάποιες εργασίες → επιβεβαίωση ότι κρύβονται
   στη λίστα, μετρητής ενημερώνεται σωστά.
4. Ξε-τσεκάρισμα όλων των ορατών → προσπάθεια submit → επιβεβαίωση block +
   μήνυμα.
5. «Επιλογή Όλων» μετά από στένεμα ημερομηνίας → επιβεβαίωση ότι τσεκάρει μόνο
   τα ορατά, όχι τα κρυμμένα.
6. Project χωρίς καμία εργασία → modal ανοίγει κανονικά, καμία λίστα/guard,
   generate PDF δουλεύει όπως πριν.
7. Send-by-email path με επιλεγμένο υποσύνολο εργασιών → επιβεβαίωση ότι το PDF
   που φτάνει στο email αντανακλά το ίδιο υποσύνολο.

---

## 8. Αρχεία που επηρεάζονται

- `views/projects/show.php` — νέο section στο `#reportModal` (~γραμμή 1240-1260),
  νέο JS (`updateTaskVisibility`, select all/none, counter, submit guard) κοντά
  στο υπάρχον `<script>` block του modal (~γραμμή 1362-1429), νέο data-fetch
  block πριν το modal (κοντά στη γραμμή 414-420, ίδιο pattern με `$otherProjects`).
- `controllers/ProjectReportController.php` — `generate()` (parsing `task_ids`),
  `getTasks()`, `getAggregatedMaterials()`, `getAggregatedLabor()` (νέα
  παράμετρος `$taskIds` + `AND pt.id IN (...)` clause).
