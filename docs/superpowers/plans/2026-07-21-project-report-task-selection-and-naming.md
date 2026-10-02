# Project Report: Task Selection + Custom Report Name Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let an admin (1) pick exactly which project tasks are included in the "Αναφορά Έργου" (Anafora Ergou) PDF report, combined with the existing date-range filter, and (2) give the report a custom name that combines the project title with an optional typed suffix (e.g. "Creta Maris - Γραφεία").

**Architecture:** Both features modify the same two existing files — no new files, no new classes, no schema changes. `views/projects/show.php` gains new form fields/JS inside the existing `#reportModal`. `controllers/ProjectReportController.php` gains a new optional `$taskIds` parameter threaded through its three data-fetch methods (`getTasks`, `getAggregatedMaterials`, `getAggregatedLabor`) and a new `$reportName` value threaded through PDF title/filename/email-subject generation.

**Tech Stack:** PHP (MVC, no framework), MySQL/MariaDB via PDO, Bootstrap 5 + vanilla JS, TCPDF.

**Spec:** `docs/superpowers/specs/2026-07-21-project-report-task-selection-design.md` (approved; this plan implements its §1-9).

## Global Constraints

- Task selection **combines with** (does not replace) the date-range filter — both apply together as an intersection (AND), never either/or.
- Checking/unchecking tasks scopes the **entire** report: the ΕΡΓΑΣΙΕΣ table rows, plus Materials/Labor aggregated totals and summary cards.
- Default state: every task checked. Block form submission only when the project has ≥1 task total AND zero are currently visible-and-checked. A project with zero tasks never blocks.
- Task checklist is a plain scrollable list with Select All / Select None (scoped to currently-visible rows only) — no search box, no pagination.
- Report subtitle is optional free text. Combined name = `{project title} - {subtitle}` only when the subtitle is non-empty; otherwise it's just the project title (identical to current behavior). Affects: PDF visible title, downloaded/emailed filename, default email subject.
- Do **NOT** fix the pre-existing missing-CSRF-validation gap in `ProjectReportController::generate()` — out of scope, to be flagged separately.
- Do **NOT** remove the `error_log` debug lines in `getAggregatedMaterials()` or the `SHOW COLUMNS` runtime introspection in `getAggregatedLabor()` — pre-existing, unrelated, out of scope.
- Do **NOT** bump `VERSION`, edit `CHANGELOG.md`, or create any git tag/release as part of this plan — the user explicitly deferred versioning/release until after the feature is built and verified.
- **No automated PHP test suite exists in this codebase** (confirmed: no PHPUnit, no `require-dev` in `composer.json`; `testing/` only has unrelated exploratory Playwright scripts). Every "Verify" step below is a manual browser check against a running local instance — this matches the project's existing convention and the approved spec's own Testing Plan (§8), not an oversight of the "write a failing test first" default.

---

## File Structure

No files are created. Two existing files are modified:

- **`controllers/ProjectReportController.php`** — gains: (a) parsing of `task_ids[]` and `report_subtitle` POST fields in `generate()`; (b) a new `$taskIds` parameter on `getTasks()`, `getAggregatedMaterials()`, `getAggregatedLabor()` that adds `AND pt.id IN (...)` when non-null; (c) a `$reportName` value computed once in `generate()` and threaded through `generatePDF()` into the PDF title, both filename-generation blocks, and the default email subject.
- **`views/projects/show.php`** — gains: (a) a small PHP block right before the report modal that loads the project's full, unfiltered task list for the checklist; (b) a new "Επιλογή Εργασιών" checklist section inside `#reportModal`, between the date-range block and the existing price-hiding checkboxes; (c) a new "Υπότιτλος Αναφοράς" text input + live preview at the top of the modal; (d) new vanilla-JS functions appended inside the modal's existing `<script>` block for visibility-syncing, counting, select-all/none, the submit guard, and the subtitle live preview.

---

## Task 1: Task Selection Checklist (backend + frontend)

**Files:**
- Modify: `views/projects/show.php` (report modal markup + its `<script>` block)
- Modify: `controllers/ProjectReportController.php` (`generate()`, `getTasks()`, `getAggregatedMaterials()`, `getAggregatedLabor()`)
- Verify: manual browser check (no automated test suite — see Global Constraints)

**Interfaces:**
- Produces: a `task_ids[]` POST array of positive integers (project_task IDs) submitted by the report form; `.report-task-row` / `.report-task-checkbox` DOM elements; `getTasks($projectId, $fromDate, $toDate, $taskIds = null)`, `getAggregatedMaterials($projectId, $fromDate, $toDate, $taskIds = null)`, `getAggregatedLabor($projectId, $fromDate, $toDate, $taskIds = null)` — new 4th parameter, `null` meaning "no restriction", an array (possibly empty) meaning "restrict to exactly these task IDs".
- Consumes: nothing from outside this task. `$project['id']` and the `ProjectTask` model (`models/ProjectTask.php`, method `getByProject($projectId, $filters = [])` returning `SELECT *` rows ordered by date DESC) already exist and are unmodified.

### Step 1: Load the full task list for the checklist

In `views/projects/show.php`, find this exact block (the end of the `<style>` block right before the report modal):

```html
/* Ensure the tab comment doesn't create spacing */
</style>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
```

Replace it with:

```html
/* Ensure the tab comment doesn't create spacing */
</style>

<?php
if (!isset($reportTaskOptions)) {
    require_once __DIR__ . '/../../models/ProjectTask.php';
    $_ptm = new ProjectTask();
    $reportTaskOptions = $_ptm->getByProject($project['id'], []);
    unset($_ptm);
}
?>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
```

This deliberately calls `getByProject($project['id'], [])` with **no filters**, independent of whatever filters happen to be active on the project's "Εργασίες" tab — the report modal's checklist must always show the complete task list.

- [ ] Add the PHP block above.

### Step 2: Add the checklist markup

In the same file, find this exact block (the end of the date-range inputs, right before the `<hr>` that separates it from the price-hiding checkboxes):

```html
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="hideLaborPricesCheck" name="hide_labor_prices" value="1">
```

Replace it with:

```html
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0"><strong>Επιλογή Εργασιών</strong></label>
                            <?php if (!empty($reportTaskOptions)): ?>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllTasksBtn">Επιλογή Όλων</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="selectNoneTasksBtn">Καμία</button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if (empty($reportTaskOptions)): ?>
                            <div class="text-muted small">Το έργο δεν έχει καταχωρημένες εργασίες.</div>
                        <?php else: ?>
                            <div id="reportTasksList" style="max-height: 220px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 6px; padding: 10px;">
                                <?php foreach ($reportTaskOptions as $rto):
                                    if (($rto['task_type'] ?? 'single_day') === 'date_range' && !empty($rto['date_from']) && !empty($rto['date_to'])) {
                                        $rtoDisplayDate = date('d/m/Y', strtotime($rto['date_from'])) . ' έως ' . date('d/m/Y', strtotime($rto['date_to']));
                                    } else {
                                        $rtoDisplayDate = date('d/m/Y', strtotime($rto['task_date'] ?? $rto['date_from'] ?? 'now'));
                                    }
                                ?>
                                <div class="form-check report-task-row"
                                     data-type="<?= htmlspecialchars($rto['task_type'] ?? 'single_day') ?>"
                                     data-date="<?= htmlspecialchars($rto['task_date'] ?? '') ?>"
                                     data-date-from="<?= htmlspecialchars($rto['date_from'] ?? '') ?>"
                                     data-date-to="<?= htmlspecialchars($rto['date_to'] ?? '') ?>">
                                    <input class="form-check-input report-task-checkbox" type="checkbox"
                                           name="task_ids[]" value="<?= (int)$rto['id'] ?>" checked>
                                    <label class="form-check-label">
                                        <?= htmlspecialchars($rtoDisplayDate) ?> — <?= htmlspecialchars($rto['description']) ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <small class="text-muted" id="reportTasksCounter"></small>
                        <?php endif; ?>
                    </div>

                    <hr>
                    
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="hideLaborPricesCheck" name="hide_labor_prices" value="1">
```

- [ ] Add the checklist markup above.

### Step 3: Verify the checklist renders

- [ ] Open a project that has at least 3 tasks (ideally a mix of single-day and date-range tasks) in the browser, e.g. `{BASE_URL}/projects/{slug}`.
- [ ] Click **"Αναφορά Έργου"**. Confirm a new **"Επιλογή Εργασιών"** section appears between "Όλες οι Ημερομηνίες" and "Απόκρυψη Τιμών Εργατικών", listing every task in the project with its date and description, all checkboxes checked, plus "Επιλογή Όλων" / "Καμία" buttons.
- [ ] Open a project with **zero** tasks. Confirm the section shows "Το έργο δεν έχει καταχωρημένες εργασίες." with no checkboxes and no Select All/None buttons.

### Step 4: Add visibility-sync, counter, and select-all/none JS

In the same file, find this exact block (the end of `toggleEmailInput()`, right before the closing `</script>` tag of the modal's script block):

```html
function toggleEmailInput() {
    const sendByEmailCheck = document.getElementById('sendByEmailCheck');
    const emailInputs = document.getElementById('emailInputs');
    const recipientEmail = document.getElementById('recipient_email');
    const submitBtn = document.getElementById('submitReportBtn');
    const reportForm = document.getElementById('reportForm');
    
    if (sendByEmailCheck.checked) {
        emailInputs.style.display = 'block';
        recipientEmail.setAttribute('required', 'required');
        submitBtn.innerHTML = '<i class="fas fa-envelope"></i> Αποστολή με Email';
        submitBtn.classList.remove('btn-info');
        submitBtn.classList.add('btn-success');
        // Remove target="_blank" for email sending (stay in same page)
        reportForm.removeAttribute('target');
    } else {
        emailInputs.style.display = 'none';
        recipientEmail.removeAttribute('required');
        recipientEmail.value = '';
        submitBtn.innerHTML = '<i class="fas fa-file-pdf"></i> <?= __("projects.generate_report") ?>';
        submitBtn.classList.remove('btn-success');
        submitBtn.classList.add('btn-info');
        // Set target="_blank" for PDF download (open in new tab)
        reportForm.setAttribute('target', '_blank');
    }
}


</script>
```

Replace it with (unchanged `toggleEmailInput()` followed by the new functions):

```html
function toggleEmailInput() {
    const sendByEmailCheck = document.getElementById('sendByEmailCheck');
    const emailInputs = document.getElementById('emailInputs');
    const recipientEmail = document.getElementById('recipient_email');
    const submitBtn = document.getElementById('submitReportBtn');
    const reportForm = document.getElementById('reportForm');
    
    if (sendByEmailCheck.checked) {
        emailInputs.style.display = 'block';
        recipientEmail.setAttribute('required', 'required');
        submitBtn.innerHTML = '<i class="fas fa-envelope"></i> Αποστολή με Email';
        submitBtn.classList.remove('btn-info');
        submitBtn.classList.add('btn-success');
        // Remove target="_blank" for email sending (stay in same page)
        reportForm.removeAttribute('target');
    } else {
        emailInputs.style.display = 'none';
        recipientEmail.removeAttribute('required');
        recipientEmail.value = '';
        submitBtn.innerHTML = '<i class="fas fa-file-pdf"></i> <?= __("projects.generate_report") ?>';
        submitBtn.classList.remove('btn-success');
        submitBtn.classList.add('btn-info');
        // Set target="_blank" for PDF download (open in new tab)
        reportForm.setAttribute('target', '_blank');
    }
}

function getReportTaskRows() {
    return Array.prototype.slice.call(document.querySelectorAll('.report-task-row'));
}

function taskRowMatchesDateFilter(row, allDates, fromDate, toDate) {
    if (allDates || !fromDate || !toDate) {
        return true;
    }
    var type = row.getAttribute('data-type');
    if (type === 'date_range') {
        var dFrom = row.getAttribute('data-date-from');
        var dTo = row.getAttribute('data-date-to');
        if (!dFrom || !dTo) return false;
        return dFrom <= toDate && dTo >= fromDate;
    }
    var d = row.getAttribute('data-date');
    if (!d) return false;
    return d >= fromDate && d <= toDate;
}

function updateTaskVisibility() {
    var allDatesCheck = document.getElementById('allDatesCheck');
    var fromDateEl = document.getElementById('from_date');
    var toDateEl = document.getElementById('to_date');
    var allDates = allDatesCheck.checked;
    var fromDate = fromDateEl.value;
    var toDate = toDateEl.value;

    getReportTaskRows().forEach(function(row) {
        var visible = taskRowMatchesDateFilter(row, allDates, fromDate, toDate);
        row.style.display = visible ? '' : 'none';
    });
    updateTaskCounter();
}

function updateTaskCounter() {
    var counter = document.getElementById('reportTasksCounter');
    if (!counter) return;
    var rows = getReportTaskRows();
    var visibleRows = rows.filter(function(row) { return row.style.display !== 'none'; });
    var checkedVisible = visibleRows.filter(function(row) {
        return row.querySelector('.report-task-checkbox').checked;
    });
    counter.textContent = checkedVisible.length + ' από ' + visibleRows.length + ' εργασίες επιλεγμένες';
}

document.addEventListener('DOMContentLoaded', function() {
    var allDatesCheck = document.getElementById('allDatesCheck');
    var fromDateEl = document.getElementById('from_date');
    var toDateEl = document.getElementById('to_date');

    if (allDatesCheck) allDatesCheck.addEventListener('change', updateTaskVisibility);
    if (fromDateEl) fromDateEl.addEventListener('change', updateTaskVisibility);
    if (toDateEl) toDateEl.addEventListener('change', updateTaskVisibility);

    getReportTaskRows().forEach(function(row) {
        row.querySelector('.report-task-checkbox').addEventListener('change', updateTaskCounter);
    });

    updateTaskVisibility();

    var selectAllBtn = document.getElementById('selectAllTasksBtn');
    var selectNoneBtn = document.getElementById('selectNoneTasksBtn');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            getReportTaskRows().forEach(function(row) {
                if (row.style.display !== 'none') {
                    row.querySelector('.report-task-checkbox').checked = true;
                }
            });
            updateTaskCounter();
        });
    }
    if (selectNoneBtn) {
        selectNoneBtn.addEventListener('click', function() {
            getReportTaskRows().forEach(function(row) {
                if (row.style.display !== 'none') {
                    row.querySelector('.report-task-checkbox').checked = false;
                }
            });
            updateTaskCounter();
        });
    }
});


</script>
```

- [ ] Add the JS above.

### Step 5: Verify visibility sync, counter, and select-all/none

- [ ] Reopen the report modal on the project with ≥3 tasks. Confirm the counter under the checklist reads e.g. "3 από 3 εργασίες επιλεγμένες".
- [ ] Uncheck one task. Confirm the counter updates to "2 από 3 εργασίες επιλεγμένες".
- [ ] Click "Όλες οι Ημερομηνίες" off, then pick a date range that excludes at least one task. Confirm that task's row disappears from the list and the counter's denominator drops accordingly.
- [ ] Widen the date range back to include it again. Confirm the row reappears in whatever checked state it was last in.
- [ ] With a narrowed date range active, click "Επιλογή Όλων". Confirm only the currently-visible rows become checked (hidden rows are untouched). Click "Καμία" and confirm only visible rows become unchecked.

### Step 6: Add the submit guard

In the same `<script>` block, find the closing of the `DOMContentLoaded` listener added in Step 4 — the end of the `if (selectNoneBtn) { ... }` block:

```javascript
    if (selectNoneBtn) {
        selectNoneBtn.addEventListener('click', function() {
            getReportTaskRows().forEach(function(row) {
                if (row.style.display !== 'none') {
                    row.querySelector('.report-task-checkbox').checked = false;
                }
            });
            updateTaskCounter();
        });
    }
});
```

Replace it with:

```javascript
    if (selectNoneBtn) {
        selectNoneBtn.addEventListener('click', function() {
            getReportTaskRows().forEach(function(row) {
                if (row.style.display !== 'none') {
                    row.querySelector('.report-task-checkbox').checked = false;
                }
            });
            updateTaskCounter();
        });
    }

    var reportForm = document.getElementById('reportForm');
    reportForm.addEventListener('submit', function(e) {
        var rows = getReportTaskRows();
        if (rows.length === 0) {
            return; // project has no tasks at all — nothing to guard
        }
        var visibleChecked = rows.filter(function(row) {
            return row.style.display !== 'none' && row.querySelector('.report-task-checkbox').checked;
        });
        if (visibleChecked.length === 0) {
            e.preventDefault();
            alert('Επιλέξτε τουλάχιστον μία εργασία για την αναφορά.');
        }
    });
});
```

- [ ] Add the submit guard above.

### Step 7: Verify the submit guard

- [ ] On the project with ≥3 tasks, uncheck every visible task. Click "Δημιουργία Αναφοράς". Confirm submission is blocked and an alert reads "Επιλέξτε τουλάχιστον μία εργασία για την αναφορά."
- [ ] Recheck at least one task. Confirm submission now proceeds (PDF opens in a new tab).
- [ ] On the project with **zero** tasks, click "Δημιουργία Αναφοράς" without touching anything. Confirm it submits normally (no blocking alert).

### Step 8: Parse `task_ids` in the controller

In `controllers/ProjectReportController.php`, find this exact block (the start of `generate()`):

```php
    public function generate($projectId) {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        
        // Get date filters
        $fromDate = isset($_POST['from_date']) && !empty($_POST['from_date']) ? $_POST['from_date'] : null;
        $toDate = isset($_POST['to_date']) && !empty($_POST['to_date']) ? $_POST['to_date'] : null;
        
        // Get hide prices options
```

Replace it with:

```php
    public function generate($projectId) {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        
        // Get date filters
        $fromDate = isset($_POST['from_date']) && !empty($_POST['from_date']) ? $_POST['from_date'] : null;
        $toDate = isset($_POST['to_date']) && !empty($_POST['to_date']) ? $_POST['to_date'] : null;

        // Get selected task IDs (null = no restriction, empty array = nothing selected)
        $taskIds = null;
        if (isset($_POST['task_ids']) && is_array($_POST['task_ids'])) {
            $taskIds = array_values(array_unique(array_filter(
                array_map('intval', $_POST['task_ids']),
                function($id) { return $id > 0; }
            )));
        }
        
        // Get hide prices options
```

- [ ] Add the `$taskIds` parsing above.

### Step 9: Thread `$taskIds` into the three data-fetch calls

In the same file, find this exact block (later in `generate()`):

```php
        // Get tasks with date filter
        $tasks = $this->getTasks($projectId, $fromDate, $toDate);
        
        // Get aggregated materials (only if needed)
        $materials = [];
        if ($reportContent === 'both' || $reportContent === 'materials') {
            $materials = $this->getAggregatedMaterials($projectId, $fromDate, $toDate);
        }
        
        // Get aggregated labor (only if needed)
        $labor = [];
        if ($reportContent === 'both' || $reportContent === 'labor') {
            $labor = $this->getAggregatedLabor($projectId, $fromDate, $toDate);
        }
```

Replace it with:

```php
        // Get tasks with date filter
        $tasks = $this->getTasks($projectId, $fromDate, $toDate, $taskIds);
        
        // Get aggregated materials (only if needed)
        $materials = [];
        if ($reportContent === 'both' || $reportContent === 'materials') {
            $materials = $this->getAggregatedMaterials($projectId, $fromDate, $toDate, $taskIds);
        }
        
        // Get aggregated labor (only if needed)
        $labor = [];
        if ($reportContent === 'both' || $reportContent === 'labor') {
            $labor = $this->getAggregatedLabor($projectId, $fromDate, $toDate, $taskIds);
        }
```

- [ ] Thread `$taskIds` through as shown above.

### Step 10: Add the `$taskIds` parameter and `IN` clause to the three query methods

In the same file, find `getTasks()` in full:

```php
    private function getTasks($projectId, $fromDate = null, $toDate = null) {
        $pdo = $this->db->getPdo();
        $sql = "
            SELECT pt.*,
                   CASE WHEN pt.task_type = 'single_day' THEN pt.task_date ELSE pt.date_from END as display_date,
                   COUNT(DISTINCT tl.technician_name) as tech_count,
                   COALESCE(SUM(tl.hours_worked), 0) as task_total_hours,
                   COALESCE(SUM(CEIL(tl.hours_worked / 8)), 0) as task_hmeromisthia
            FROM project_tasks pt
            LEFT JOIN task_labor tl ON tl.task_id = pt.id
            WHERE pt.project_id = ? AND pt.deleted_at IS NULL
        ";
        $params = [$projectId];
        
        if ($fromDate && $toDate) {
            $sql .= " AND (
                (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                OR (pt.task_type = 'date_range' AND pt.date_from <= ? AND pt.date_to >= ?)
            )";
            $params[] = $fromDate;
            $params[] = $toDate;
            $params[] = $toDate;
            $params[] = $fromDate;
        }
        
        $sql .= " GROUP BY pt.id ORDER BY CASE WHEN pt.task_type = 'single_day' THEN pt.task_date ELSE pt.date_from END ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
```

Replace it with:

```php
    private function getTasks($projectId, $fromDate = null, $toDate = null, $taskIds = null) {
        if ($taskIds !== null && empty($taskIds)) {
            return [];
        }

        $pdo = $this->db->getPdo();
        $sql = "
            SELECT pt.*,
                   CASE WHEN pt.task_type = 'single_day' THEN pt.task_date ELSE pt.date_from END as display_date,
                   COUNT(DISTINCT tl.technician_name) as tech_count,
                   COALESCE(SUM(tl.hours_worked), 0) as task_total_hours,
                   COALESCE(SUM(CEIL(tl.hours_worked / 8)), 0) as task_hmeromisthia
            FROM project_tasks pt
            LEFT JOIN task_labor tl ON tl.task_id = pt.id
            WHERE pt.project_id = ? AND pt.deleted_at IS NULL
        ";
        $params = [$projectId];
        
        if ($fromDate && $toDate) {
            $sql .= " AND (
                (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                OR (pt.task_type = 'date_range' AND pt.date_from <= ? AND pt.date_to >= ?)
            )";
            $params[] = $fromDate;
            $params[] = $toDate;
            $params[] = $toDate;
            $params[] = $fromDate;
        }

        if ($taskIds !== null) {
            $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
            $sql .= " AND pt.id IN ($placeholders)";
            $params = array_merge($params, $taskIds);
        }
        
        $sql .= " GROUP BY pt.id ORDER BY CASE WHEN pt.task_type = 'single_day' THEN pt.task_date ELSE pt.date_from END ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
```

Next, find `getAggregatedMaterials()` in full:

```php
    private function getAggregatedMaterials($projectId, $fromDate = null, $toDate = null) {
        $pdo = $this->db->getPdo();
        $sql = "
            SELECT 
                tm.description as material_name,
                tm.unit_type as unit,
                SUM(tm.quantity) as total_quantity,
                AVG(tm.unit_price) as unit_cost,
                SUM(tm.subtotal) as total_cost
            FROM task_materials tm
            LEFT JOIN project_tasks pt ON tm.task_id = pt.id
            WHERE pt.project_id = ? AND pt.deleted_at IS NULL
        ";
        $params = [$projectId];
        
        if ($fromDate && $toDate) {
            $sql .= " AND (
                (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                OR (pt.task_type = 'date_range' AND pt.date_from <= ? AND pt.date_to >= ?)
            )";
            $params[] = $fromDate;
            $params[] = $toDate;
            $params[] = $toDate;
            $params[] = $fromDate;
        }
        
        $sql .= " GROUP BY tm.description, tm.unit_type ORDER BY tm.description";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Log the materials
        error_log("=== MATERIALS QUERY DEBUG ===");
        error_log("Total materials found: " . count($results));
        foreach ($results as $mat) {
            error_log("Material: " . $mat['material_name'] . " | Unit: " . $mat['unit'] . " | Qty: " . $mat['total_quantity'] . " | Unit Price: " . $mat['unit_cost'] . " | Total: " . $mat['total_cost']);
        }
        
        return $results;
    }
```

Replace it with:

```php
    private function getAggregatedMaterials($projectId, $fromDate = null, $toDate = null, $taskIds = null) {
        if ($taskIds !== null && empty($taskIds)) {
            return [];
        }

        $pdo = $this->db->getPdo();
        $sql = "
            SELECT 
                tm.description as material_name,
                tm.unit_type as unit,
                SUM(tm.quantity) as total_quantity,
                AVG(tm.unit_price) as unit_cost,
                SUM(tm.subtotal) as total_cost
            FROM task_materials tm
            LEFT JOIN project_tasks pt ON tm.task_id = pt.id
            WHERE pt.project_id = ? AND pt.deleted_at IS NULL
        ";
        $params = [$projectId];
        
        if ($fromDate && $toDate) {
            $sql .= " AND (
                (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                OR (pt.task_type = 'date_range' AND pt.date_from <= ? AND pt.date_to >= ?)
            )";
            $params[] = $fromDate;
            $params[] = $toDate;
            $params[] = $toDate;
            $params[] = $fromDate;
        }

        if ($taskIds !== null) {
            $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
            $sql .= " AND pt.id IN ($placeholders)";
            $params = array_merge($params, $taskIds);
        }
        
        $sql .= " GROUP BY tm.description, tm.unit_type ORDER BY tm.description";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Log the materials
        error_log("=== MATERIALS QUERY DEBUG ===");
        error_log("Total materials found: " . count($results));
        foreach ($results as $mat) {
            error_log("Material: " . $mat['material_name'] . " | Unit: " . $mat['unit'] . " | Qty: " . $mat['total_quantity'] . " | Unit Price: " . $mat['unit_cost'] . " | Total: " . $mat['total_cost']);
        }
        
        return $results;
    }
```

Finally, find `getAggregatedLabor()` in full:

```php
    private function getAggregatedLabor($projectId, $fromDate = null, $toDate = null) {
        $pdo = $this->db->getPdo();
        
        // Check if technician_name column exists, otherwise use user_id with JOIN
        $checkColumn = $pdo->query("SHOW COLUMNS FROM task_labor LIKE 'technician_name'");
        $hasTechnicianName = $checkColumn->rowCount() > 0;
        
        if ($hasTechnicianName) {
            // Use technician_name if column exists
            $workerNameField = "COALESCE(tl.technician_name, CONCAT(u.first_name, ' ', u.last_name))";
            $groupByField = "tl.technician_name";
        } else {
            // Fall back to user_id with JOIN to users table
            $workerNameField = "CONCAT(u.first_name, ' ', u.last_name)";
            $groupByField = "tl.user_id";
        }
        
        // Check if hours_worked column exists, otherwise use hours
        $checkHours = $pdo->query("SHOW COLUMNS FROM task_labor LIKE 'hours_worked'");
        $hasHoursWorked = $checkHours->rowCount() > 0;
        $hoursField = $hasHoursWorked ? "tl.hours_worked" : "tl.hours";
        
        // Check which ID column to use for JOIN
        $checkTechId = $pdo->query("SHOW COLUMNS FROM task_labor LIKE 'technician_id'");
        $hasTechnicianId = $checkTechId->rowCount() > 0;
        $userIdField = $hasTechnicianId ? "tl.technician_id" : "tl.user_id";
        
        $sql = "
            SELECT 
                $workerNameField as worker_name,
                SUM($hoursField) as total_hours,
                CEIL(SUM($hoursField) / 8) as total_days,
                COUNT(DISTINCT pt.id) as days_worked,
                AVG(tl.hourly_rate) as hourly_rate,
                SUM(tl.subtotal) as total_cost
            FROM task_labor tl
            LEFT JOIN project_tasks pt ON tl.task_id = pt.id
            LEFT JOIN users u ON $userIdField = u.id
            WHERE pt.project_id = ? AND pt.deleted_at IS NULL
        ";
        $params = [$projectId];
        
        if ($fromDate && $toDate) {
            $sql .= " AND (
                (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                OR (pt.task_type = 'date_range' AND pt.date_from <= ? AND pt.date_to >= ?)
            )";
            $params[] = $fromDate;
            $params[] = $toDate;
            $params[] = $toDate;
            $params[] = $fromDate;
        }
        
        $sql .= " GROUP BY $groupByField ORDER BY worker_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
```

Replace it with:

```php
    private function getAggregatedLabor($projectId, $fromDate = null, $toDate = null, $taskIds = null) {
        if ($taskIds !== null && empty($taskIds)) {
            return [];
        }

        $pdo = $this->db->getPdo();
        
        // Check if technician_name column exists, otherwise use user_id with JOIN
        $checkColumn = $pdo->query("SHOW COLUMNS FROM task_labor LIKE 'technician_name'");
        $hasTechnicianName = $checkColumn->rowCount() > 0;
        
        if ($hasTechnicianName) {
            // Use technician_name if column exists
            $workerNameField = "COALESCE(tl.technician_name, CONCAT(u.first_name, ' ', u.last_name))";
            $groupByField = "tl.technician_name";
        } else {
            // Fall back to user_id with JOIN to users table
            $workerNameField = "CONCAT(u.first_name, ' ', u.last_name)";
            $groupByField = "tl.user_id";
        }
        
        // Check if hours_worked column exists, otherwise use hours
        $checkHours = $pdo->query("SHOW COLUMNS FROM task_labor LIKE 'hours_worked'");
        $hasHoursWorked = $checkHours->rowCount() > 0;
        $hoursField = $hasHoursWorked ? "tl.hours_worked" : "tl.hours";
        
        // Check which ID column to use for JOIN
        $checkTechId = $pdo->query("SHOW COLUMNS FROM task_labor LIKE 'technician_id'");
        $hasTechnicianId = $checkTechId->rowCount() > 0;
        $userIdField = $hasTechnicianId ? "tl.technician_id" : "tl.user_id";
        
        $sql = "
            SELECT 
                $workerNameField as worker_name,
                SUM($hoursField) as total_hours,
                CEIL(SUM($hoursField) / 8) as total_days,
                COUNT(DISTINCT pt.id) as days_worked,
                AVG(tl.hourly_rate) as hourly_rate,
                SUM(tl.subtotal) as total_cost
            FROM task_labor tl
            LEFT JOIN project_tasks pt ON tl.task_id = pt.id
            LEFT JOIN users u ON $userIdField = u.id
            WHERE pt.project_id = ? AND pt.deleted_at IS NULL
        ";
        $params = [$projectId];
        
        if ($fromDate && $toDate) {
            $sql .= " AND (
                (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                OR (pt.task_type = 'date_range' AND pt.date_from <= ? AND pt.date_to >= ?)
            )";
            $params[] = $fromDate;
            $params[] = $toDate;
            $params[] = $toDate;
            $params[] = $fromDate;
        }

        if ($taskIds !== null) {
            $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
            $sql .= " AND pt.id IN ($placeholders)";
            $params = array_merge($params, $taskIds);
        }
        
        $sql .= " GROUP BY $groupByField ORDER BY worker_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
```

- [ ] Apply all three method replacements above.

### Step 11: Verify end-to-end scoping, then commit

- [ ] On the project used above, note the Materials/Labor totals with all tasks checked (generate one baseline PDF).
- [ ] Uncheck one task that has materials and/or labor entries. Generate the PDF again. Confirm: that task's row is missing from ΕΡΓΑΣΙΕΣ, and the Materials/Labor totals and summary cards are lower than the baseline by exactly that task's contribution.
- [ ] Repeat with "Αποστολή με Email" checked and a subset of tasks selected — confirm the emailed PDF reflects the same subset.
- [ ] Commit:

```bash
git add controllers/ProjectReportController.php views/projects/show.php
git commit -m "$(cat <<'EOF'
Add task selection filter to project report

Admins can now check/uncheck individual project tasks in the Anafora
Ergou modal, combined with the existing date-range filter. Selection
scopes the entire report: the tasks table plus materials/labor totals
and summary cards recalculate around only the checked tasks.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

## Task 2: Custom Report Name (subtitle)

**Files:**
- Modify: `views/projects/show.php` (report modal markup + its `<script>` block — runs after Task 1's edits)
- Modify: `controllers/ProjectReportController.php` (`generate()`, `generatePDF()`, `buildHTMLContent()` — runs after Task 1's edits)
- Verify: manual browser check

**Interfaces:**
- Produces: a `report_subtitle` POST string field; `$reportName` (string) computed in `generate()` as `{$project['title']}` or `{$project['title']} - {$reportSubtitle}`; new 4th `$taskIds` parameter added in Task 1 is untouched by this task.
- Consumes: `generatePDF()` and `buildHTMLContent()` signatures as modified by nothing in Task 1 (Task 1 doesn't touch these two methods) — this task adds their `$reportName` parameter directly against the original signatures.

### Step 1: Add the subtitle input and live-preview markup

In `views/projects/show.php`, find this exact block (start of the modal body, still exactly as in the original file — Task 1 did not touch this region):

```html
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <?= __('projects.report_filters') ?>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="allDatesCheck" checked onchange="toggleDateInputs()">
```

Replace it with:

```html
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <?= __('projects.report_filters') ?>
                    </div>

                    <div class="mb-3">
                        <label for="report_subtitle" class="form-label"><strong>Υπότιτλος Αναφοράς (προαιρετικό)</strong></label>
                        <input type="text" class="form-control" id="report_subtitle" name="report_subtitle" placeholder="π.χ. Γραφεία">
                        <small class="text-muted" id="reportNamePreview">Τίτλος αναφοράς: <?= htmlspecialchars($project['title']) ?></small>
                    </div>

                    <hr>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="allDatesCheck" checked onchange="toggleDateInputs()">
```

- [ ] Add the markup above.

### Step 2: Add the live-preview JS

In the same file, find the `</script>` tag that now closes the modal's script block (the very end of the block Task 1 extended). Find this exact tail (the end of Task 1's new submit-guard code):

```javascript
    var reportForm = document.getElementById('reportForm');
    reportForm.addEventListener('submit', function(e) {
        var rows = getReportTaskRows();
        if (rows.length === 0) {
            return; // project has no tasks at all — nothing to guard
        }
        var visibleChecked = rows.filter(function(row) {
            return row.style.display !== 'none' && row.querySelector('.report-task-checkbox').checked;
        });
        if (visibleChecked.length === 0) {
            e.preventDefault();
            alert('Επιλέξτε τουλάχιστον μία εργασία για την αναφορά.');
        }
    });
});


</script>
```

Replace it with:

```javascript
    var reportForm = document.getElementById('reportForm');
    reportForm.addEventListener('submit', function(e) {
        var rows = getReportTaskRows();
        if (rows.length === 0) {
            return; // project has no tasks at all — nothing to guard
        }
        var visibleChecked = rows.filter(function(row) {
            return row.style.display !== 'none' && row.querySelector('.report-task-checkbox').checked;
        });
        if (visibleChecked.length === 0) {
            e.preventDefault();
            alert('Επιλέξτε τουλάχιστον μία εργασία για την αναφορά.');
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    var subtitleInput = document.getElementById('report_subtitle');
    var preview = document.getElementById('reportNamePreview');
    var baseTitle = <?= json_encode($project['title']) ?>;

    if (subtitleInput && preview) {
        subtitleInput.addEventListener('input', function() {
            var suffix = subtitleInput.value.trim();
            preview.textContent = 'Τίτλος αναφοράς: ' + (suffix !== '' ? baseTitle + ' - ' + suffix : baseTitle);
        });
    }
});


</script>
```

- [ ] Add the JS above.

### Step 3: Verify the live preview

- [ ] Open the report modal. Confirm the preview under the new field reads "Τίτλος αναφοράς: {project title}" with no suffix.
- [ ] Type "Γραφεία" into the field. Confirm the preview updates live, character by character, to "Τίτλος αναφοράς: {project title} - Γραφεία".
- [ ] Clear the field. Confirm the preview reverts to just the project title.

### Step 4: Compute `$reportName` in `generate()`

In `controllers/ProjectReportController.php`, find this exact block (as left by Task 1 — the project lookup, right before `getCustomer`):

```php
        // Get project data
        $project = $this->getProject($projectId);
        if (!$project) {
            die('Project not found');
        }
        
        // Get customer data
        $customer = $this->getCustomer($project['customer_id']);
```

Replace it with:

```php
        // Get project data
        $project = $this->getProject($projectId);
        if (!$project) {
            die('Project not found');
        }

        // Optional report subtitle — combined with the project title (e.g. "Creta Maris - Grafeia")
        $reportSubtitle = isset($_POST['report_subtitle']) ? trim($_POST['report_subtitle']) : '';
        $reportName = $project['title'];
        if ($reportSubtitle !== '') {
            $reportName .= ' - ' . $reportSubtitle;
        }
        
        // Get customer data
        $customer = $this->getCustomer($project['customer_id']);
```

- [ ] Add the `$reportName` computation above.

### Step 5: Pass `$reportName` into `generatePDF()`

In the same file, find this exact line (the call at the end of `generate()`):

```php
        // Generate PDF
        $this->generatePDF($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate, $hideLaborPrices, $hideMaterialsPrices, $reportNotes, $showTasks, $projectTotal);
```

Replace it with:

```php
        // Generate PDF
        $this->generatePDF($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate, $hideLaborPrices, $hideMaterialsPrices, $reportNotes, $showTasks, $projectTotal, $reportName);
```

- [ ] Update the call above.

### Step 6: Use `$reportName` inside `generatePDF()`

In the same file, find this exact block (the start of `generatePDF()` through the `SetTitle` call):

```php
    private function generatePDF($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate, $hideLaborPrices = false, $hideMaterialsPrices = false, $reportNotes = null, $showTasks = true, $projectTotal = null) {
        // Create new PDF document with custom footer
        $pdf = new CustomPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Pass settings to PDF for footer
        $pdf->setCompanySettings($settings);
        
        // Set document information
        $pdf->SetCreator('HandyCRM');
        $pdf->SetAuthor($settings['company_name'] ?? 'HandyCRM');
        $pdf->SetTitle(__('projects.project_report') . ' - ' . $project['title']);
```

Replace it with:

```php
    private function generatePDF($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate, $hideLaborPrices = false, $hideMaterialsPrices = false, $reportNotes = null, $showTasks = true, $projectTotal = null, $reportName = null) {
        if ($reportName === null) {
            $reportName = $project['title'];
        }

        // Create new PDF document with custom footer
        $pdf = new CustomPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Pass settings to PDF for footer
        $pdf->setCompanySettings($settings);
        
        // Set document information
        $pdf->SetCreator('HandyCRM');
        $pdf->SetAuthor($settings['company_name'] ?? 'HandyCRM');
        $pdf->SetTitle(__('projects.project_report') . ' - ' . $reportName);
```

Next, find this exact line (the `buildHTMLContent()` call inside `generatePDF()`):

```php
        // Build HTML content
        $html = $this->buildHTMLContent($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate, $hideLaborPrices, $hideMaterialsPrices, $reportNotes, $showTasks, $projectTotal);
```

Replace it with:

```php
        // Build HTML content
        $html = $this->buildHTMLContent($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate, $hideLaborPrices, $hideMaterialsPrices, $reportNotes, $showTasks, $projectTotal, $reportName);
```

Next, find this exact line (the default email subject):

```php
            $subject = $_POST['email_subject'] ?? 'Αναφορά Έργου - ' . $project['title'];
```

Replace it with:

```php
            $subject = $_POST['email_subject'] ?? 'Αναφορά Έργου - ' . $reportName;
```

Next, find this exact block (the email-attachment filename, identifiable by the `sys_get_temp_dir()` line right after it):

```php
            // Generate PDF to temp file
            // Transliterate Greek to Latin for filename compatibility
            $customerName = !empty($customer['name']) ? $customer['name'] : (!empty($project['title']) ? $project['title'] : 'Report');
            $customerName = $this->transliterateGreek($customerName);
            $customerName = preg_replace('/[^a-zA-Z0-9\s]/', '', $customerName);
            $customerName = str_replace(' ', '_', $customerName);
            $dateFormatted = date('d_m_Y');
            $filename = 'Anafora_Ergou_' . $customerName . '_' . $dateFormatted . '.pdf';
            $tempPdfPath = sys_get_temp_dir() . '/' . $filename;
```

Replace it with:

```php
            // Generate PDF to temp file
            // Transliterate Greek to Latin for filename compatibility
            $filenameBase = !empty($reportName) ? $reportName : 'Report';
            $filenameBase = $this->transliterateGreek($filenameBase);
            $filenameBase = preg_replace('/[^a-zA-Z0-9\s]/', '', $filenameBase);
            $filenameBase = str_replace(' ', '_', $filenameBase);
            $dateFormatted = date('d_m_Y');
            $filename = 'Anafora_Ergou_' . $filenameBase . '_' . $dateFormatted . '.pdf';
            $tempPdfPath = sys_get_temp_dir() . '/' . $filename;
```

Finally, find this exact block (the direct-download filename, identifiable by the `// Normal PDF download` comment and the `$pdf->Output($filename, 'I');` call right after it):

```php
        } else {
            // Normal PDF download
            // Transliterate Greek to Latin for filename compatibility
            $customerName = !empty($customer['name']) ? $customer['name'] : (!empty($project['title']) ? $project['title'] : 'Report');
            $customerName = $this->transliterateGreek($customerName);
            $customerName = preg_replace('/[^a-zA-Z0-9\s]/', '', $customerName);
            $customerName = str_replace(' ', '_', $customerName);
            $dateFormatted = date('d_m_Y');
            $filename = 'Anafora_Ergou_' . $customerName . '_' . $dateFormatted . '.pdf';
            $pdf->Output($filename, 'I');
        }
    }
```

Replace it with:

```php
        } else {
            // Normal PDF download
            // Transliterate Greek to Latin for filename compatibility
            $filenameBase = !empty($reportName) ? $reportName : 'Report';
            $filenameBase = $this->transliterateGreek($filenameBase);
            $filenameBase = preg_replace('/[^a-zA-Z0-9\s]/', '', $filenameBase);
            $filenameBase = str_replace(' ', '_', $filenameBase);
            $dateFormatted = date('d_m_Y');
            $filename = 'Anafora_Ergou_' . $filenameBase . '_' . $dateFormatted . '.pdf';
            $pdf->Output($filename, 'I');
        }
    }
```

- [ ] Apply all five edits above. (Note: `$customer['name']` never existed as a column — `customers` only has `first_name`/`last_name`/`company_name` — so this was dead code that always fell through to `$project['title']`; renaming to `$filenameBase` driven by `$reportName` preserves that exact fallback behavior for an empty subtitle while removing the misleading dead branch.)

### Step 7: Use `$reportName` for the visible PDF title

In the same file, find this exact block (`buildHTMLContent()` signature plus the title lines):

```php
    private function buildHTMLContent($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate, $hideLaborPrices = false, $hideMaterialsPrices = false, $reportNotes = null, $showTasks = true, $projectTotal = null) {
```

Replace it with:

```php
    private function buildHTMLContent($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate, $hideLaborPrices = false, $hideMaterialsPrices = false, $reportNotes = null, $showTasks = true, $projectTotal = null, $reportName = null) {
        if ($reportName === null) {
            $reportName = $project['title'];
        }
```

Next, find this exact block (the report title inside the generated HTML):

```php
        // Report Title and Date
        $html .= '<h1 class="text-center" style="margin-top: 30px; margin-bottom: 15px;">ΑΝΑΦΟΡΑ ΕΡΓΟΥ</h1>';
        $html .= '<h2 class="text-center" style="border: none; margin-bottom: 15px; font-size: 20px;">' . htmlspecialchars($project['title']) . '</h2>';
```

Replace it with:

```php
        // Report Title and Date
        $html .= '<h1 class="text-center" style="margin-top: 30px; margin-bottom: 15px;">ΑΝΑΦΟΡΑ ΕΡΓΟΥ</h1>';
        $html .= '<h2 class="text-center" style="border: none; margin-bottom: 15px; font-size: 20px;">' . htmlspecialchars($reportName) . '</h2>';
```

- [ ] Apply both edits above.

### Step 8: Verify end-to-end, then commit

- [ ] Generate a report with the subtitle field **blank**. Confirm the PDF title and downloaded filename are identical in format to before this change (project title only).
- [ ] Generate a report with subtitle **"Γραφεία"** on a project titled e.g. "Creta Maris". Confirm: the PDF's visible title reads "Creta Maris - Γραφεία"; the downloaded filename is `Anafora_Ergou_Creta_Maris_Grafeia_<date>.pdf` (Greek transliterated, spaces as underscores, matching the existing filename convention).
- [ ] Repeat with "Αποστολή με Email" checked and a subtitle filled in. Confirm the email's default subject line also reads "Αναφορά Έργου - Creta Maris - Γραφεία".
- [ ] Commit:

```bash
git add controllers/ProjectReportController.php views/projects/show.php
git commit -m "$(cat <<'EOF'
Add optional custom name (subtitle) to project report

Admins can type a subtitle in the Anafora Ergou modal that combines
with the project title (e.g. "Creta Maris - Grafeia") for the PDF's
visible title, the downloaded/emailed filename, and the default email
subject. Also removes a dead customer-name check in the filename
logic ($customer['name'] is not a column that exists on `customers`).

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

## Task 3: Integrated Regression Pass

**Files:** none (verification only — fix inline only if a scenario below fails)

**Interfaces:** consumes everything produced by Tasks 1 and 2; produces nothing new.

### Step 1: Run the full spec testing checklist

Using a project with ≥3 tasks (mixed single-day and date-range):

- [ ] All tasks checked by default and visible when the modal opens.
- [ ] Unchecking one task removes it from ΕΡΓΑΣΙΕΣ and excludes its materials/labor from the totals (re-verify — this is the core of Task 1).
- [ ] A date range that excludes some tasks hides them from the checklist; the counter reflects only visible rows.
- [ ] Unchecking every visible task blocks submission with the warning message.
- [ ] "Επιλογή Όλων" after narrowing the date range only checks visible rows.
- [ ] A project with zero tasks opens the modal with no checklist/guard and generates a report exactly as before this work.
- [ ] Send-by-email with a subset of tasks selected reflects that subset in the emailed PDF.
- [ ] Blank subtitle leaves title/filename/email-subject unchanged from pre-existing behavior.
- [ ] Subtitle "Γραφεία" produces title "{Project} - Γραφεία" and a matching transliterated filename.
- [ ] Subtitle + send-by-email together: email subject also reflects the combined name.
- [ ] **Combined scenario (not in the original spec list, but the natural intersection of both features):** pick a date range, uncheck one visible task, AND fill in a subtitle, then generate. Confirm the tasks table/totals reflect the date+checkbox intersection from Task 1 *and* the title/filename reflect the subtitle from Task 2 — i.e. the two features don't interfere with each other.

### Step 2: Wrap up

- [ ] If every scenario above passes with no changes needed: nothing further to do — Tasks 1 and 2 already committed working, verified code. Do not bump `VERSION`, edit `CHANGELOG.md`, or create a tag/release (explicitly deferred — see Global Constraints).
- [ ] If any scenario fails: fix the issue in the relevant file, re-run that specific scenario to confirm the fix, then commit the fix on its own with a message describing what was wrong (e.g. `git commit -m "Fix: <what was broken> in project report task selection"`).
