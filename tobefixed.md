# HandyCRM — Bug & Improvement Backlog

Generated: 2026-06-19 | Audit domains: Security · Performance · Database · UX/UI · Code Quality

---

## 🔴 PHASE 1 — Critical / Security (do first)

### SEC-1 · DEBUG_MODE = true in production
- **File:** `config/config.php:61`
- **Issue:** Exposes full stack traces, SQL errors, and bypasses CSRF validation.
- **Fix:** Set `define('DEBUG_MODE', false);` on the live server.

### SEC-2 · CSRF validation disabled when DEBUG_MODE is on
- **Files:** `controllers/AuthController.php:48` · `controllers/InvoiceController.php:120`
- **Issue:** All state-changing forms unprotected while debug is active.
- **Fix:** Remove the `if (!DEBUG_MODE)` guard — always call `$this->validateCsrfToken()`.

### SEC-3 · Database credentials in plaintext config
- **File:** `config/config.php:8–12`
- **Issue:** DB password and SECRET_KEY committed as plaintext. Anyone with repo access has production DB access.
- **Fix:** Rotate credentials immediately. Switch to `getenv('DB_PASS')` environment variables.

### SEC-4 · No session regeneration on login (session fixation)
- **File:** `controllers/AuthController.php:86`
- **Issue:** Missing `session_regenerate_id(true)` after successful login. Remember-token cookie also lacks `Secure` and `HttpOnly` flags.
- **Fix:**
  ```php
  session_regenerate_id(true);
  setcookie('remember_token', $token, time() + 86400*30, '/', '', true, true);
  ```

### SEC-5 · CSRF missing on API and payment POST endpoints
- **Files:** `controllers/ProjectTasksController.php:576` · `controllers/PaymentsController.php:171`
- **Issue:** `apiCheckOverlap`, `apiCheckTechnicianOverlap`, `markPaid`, `markUnpaid` accept POST without CSRF validation.
- **Fix:** Add `$this->validateCsrfToken();` at the start of each POST handler.

### SEC-6 · No .htaccess in uploads/ directory
- **Path:** `uploads/`
- **Issue:** Root .htaccess PHP block is fragile. An uploaded PHP file could execute.
- **Fix:** Create `uploads/.htaccess`:
  ```apache
  php_flag engine off
  AddType text/plain .php .php3 .php4 .php5 .phtml
  ```

### SEC-7 · Path traversal in file-serving endpoints
- **File:** `controllers/UploadedContractController.php:240`
- **Issue:** File paths from DB passed to `readfile()` without `realpath()` whitelist check.
- **Fix:**
  ```php
  $realPath = realpath($filePath);
  $uploadDir = realpath(__DIR__ . '/../uploads');
  if (!$realPath || strpos($realPath, $uploadDir) !== 0) { http_response_code(403); exit; }
  ```

---

## 🟠 PHASE 2 — High / Data Integrity

### DB-1 · Soft-delete filter missing in Project::getByCustomer, getStats, getRecent
- **File:** `models/Project.php:238, 250, 288`
- **Issue:** Deleted projects appear in customer history, statistics, and recent lists.
- **Fix:** Add `AND deleted_at IS NULL` (or `AND p.deleted_at IS NULL`) to all three methods.

### DB-2 · DailyTask::find() does not filter soft-deleted records
- **File:** `models/DailyTask.php:302`
- **Issue:** Fetching a deleted daily task by ID returns it, allowing it to be viewed and edited.
- **Fix:** Add `AND dt.deleted_at IS NULL` to the WHERE clause.

### DB-3 · Duplicate `language` column in users table schema
- **File:** `database/handycrm.sql:559 & 571`
- **Issue:** Column defined twice — breaks fresh installs.
- **Fix:** Remove the duplicate definition at line 571.

### DB-4 · task_labor.role_id has no FK constraint and no index
- **File:** `migrations/add_project_tasks_system.sql:95`
- **Issue:** References the roles table with no constraint, risking orphaned rows and slow joins.
- **Fix:**
  ```sql
  ALTER TABLE task_labor
    ADD CONSTRAINT fk_task_labor_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL,
    ADD INDEX idx_task_labor_role_id (role_id);
  ```

### CODE-1 · Debug error_log statements left in production code
- **Files:** `controllers/ProjectReportController.php:222–225` · `controllers/DailyTaskController.php:307–308`
- **Issue:** "=== MATERIALS QUERY DEBUG ===" and `print_r` of POST data log to PHP error log on every request.
- **Fix:** Delete lines 222–225 in ProjectReportController.php and lines 307–308 in DailyTaskController.php.

### SEC-8 · shell_exec / exec without command whitelist
- **Files:** `classes/BackupManager.php:161` · `classes/VersionManager.php:454`
- **Issue:** Commands currently hardcoded but no allowed-command whitelist. One future change from RCE.
- **Fix:** Add whitelist validation before calling `exec()`.

---

## 🟡 PHASE 3 — Performance

### PERF-1 · N+1 query — materials fetched per task in a loop
- **File:** `controllers/ProjectController.php:300–315`
- **Issue:** One DB query per task. A project with 20 tasks fires 21 queries.
- **Fix:** Single JOIN query, group results by task_id in PHP post-processing.

### PERF-2 · 3× SHOW COLUMNS on every report generation
- **File:** `controllers/ProjectReportController.php:235, 249, 254`
- **Issue:** Runtime schema introspection on `task_labor` runs every time a PDF is generated.
- **Fix:** Cache with a static property or remove entirely (columns now stable post-migration).
  ```php
  static $schema = null;
  if ($schema === null) { /* check once */ }
  ```

### PERF-3 · Project::getAll() has no LIMIT — loads entire table
- **File:** `models/Project.php:186`
- **Issue:** Called in ProjectTasksController for dropdowns. Loads all projects into PHP memory.
- **Fix:** Add `LIMIT 200` or create a separate `getAllForSelect()` method with a limit.

### PERF-4 · Correlated subqueries per row in paginated project list
- **File:** `models/Project.php:133–140`
- **Issue:** Two cost subqueries execute per project row. 20 results = 40 subquery executions.
- **Fix:** Cache `total_cost` on the projects table or aggregate at query time with GROUP BY.

### PERF-5 · SELECT * in BaseModel
- **File:** `classes/BaseModel.php:20, 28, 127`
- **Issue:** All find/findAll/paginate fetch every column. Wastes memory on wide tables.
- **Fix:** Add optional `$columns = ['*']` parameter to base methods.

### PERF-6 · Dashboard fires 3 heavy aggregation queries uncached
- **File:** `controllers/DashboardController.php:111–176`
- **Issue:** Revenue aggregations re-run on every dashboard page load.
- **Fix:** Cache results in `$_SESSION` with a 30-minute TTL.

### PERF-7 · 8 file reads + 4 base64_encode calls per maintenance PDF
- **File:** `controllers/MaintenanceOfferController.php:419–426`
- **Issue:** Logo and cert images re-read from disk and base64-encoded on every PDF export.
- **Fix:** Pre-cache encoded strings in a static property or database field on first use.

---

## 🔵 PHASE 4 — Database (schema & integrity)

### DB-5 · Old users.role enum vs new role_id FK — migration incomplete
- **Files:** `models/User.php:14–16` · `database/schema.sql:30`
- **Issue:** Code JOINs on the `roles` table but `schema.sql` still shows the old enum column. Verify the roles table exists in production.
- **Fix:** Reconcile schema.sql with the actual database state.

### DB-6 · ProjectTask::checkTechnicianOverlap missing deleted_at filter
- **File:** `models/ProjectTask.php:444–466`
- **Issue:** Overlap detection returns false positives against soft-deleted tasks.
- **Fix:** Add `AND pt.deleted_at IS NULL` to the overlap query.

### DB-7 · Material model vs materials_catalog table mismatch
- **File:** `models/Material.php:8` · `database/schema.sql`
- **Issue:** Model uses `materials_catalog` but the master schema only defines `materials`.
- **Fix:** Add `materials_catalog` and `material_categories` table definitions to schema.sql.

### DB-8 · Missing composite indexes on common filter patterns
- **Tables:** `projects`, `appointments`, `task_labor`
- **Issue:** Paginated queries filter on (deleted_at, status, customer_id) with no composite index.
- **Fix:**
  ```sql
  ALTER TABLE projects ADD INDEX idx_deleted_status_customer (deleted_at, status, customer_id);
  ALTER TABLE appointments ADD INDEX idx_tech_date (technician_id, appointment_date);
  ALTER TABLE task_labor ADD INDEX idx_payment (technician_id, paid_at, paid_by);
  ```

### DB-9 · utf8 charset declarations in SQL dump
- **File:** `database/handycrm.sql:24, 71, 105`
- **Issue:** Some `SET character_set_client = utf8` lines instead of utf8mb4 — could corrupt Greek chars.
- **Fix:** Replace all instances with `utf8mb4`.

### DB-10 · Two migration directories with unclear execution order
- **Paths:** `migrations/` · `database/migrations/`
- **Issue:** Unclear which runs first. Schema drift between schema.sql and handycrm.sql.
- **Fix:** Consolidate into one directory, document execution order.

---

## 🟢 PHASE 5 — UX / UI

### UX-1 · No breadcrumbs on any content page
- **File:** `views/includes/header.php`
- **Fix:** Add a `$breadcrumbs` variable to each view and render a Bootstrap breadcrumb component in the header.

### UX-2 · All destructive actions use browser confirm() instead of Bootstrap modals
- **Files:** `views/quotes/index.php:148` · `views/users/index.php:88` · `views/materials/index.php:57`
- **Fix:** Create a reusable `#confirmModal` Bootstrap modal in footer.php, trigger it via data attributes.

### UX-3 · No loading states for PDF generation and AJAX calls
- **Fix:** Disable submit buttons on form submit. Add a global jQuery `ajaxSetup` spinner in footer.php.

### UX-4 · Mobile filter forms unusable (12 columns on narrow screens)
- **Files:** `views/daily-tasks/index.php:33` · `views/invoices/index.php:28`
- **Fix:** Move filters into a Bootstrap offcanvas/accordion panel on mobile. Use `col-12 col-md-3` at minimum.

### UX-5 · Hardcoded Greek strings not using translation system
- **Files:** `views/maintenances/` · `views/daily-tasks/` · `views/materials/`
- **Fix:** Replace all hardcoded strings with `__('key', 'default')` calls.

### UX-6 · Empty states don't indicate active filters
- **Files:** `views/customers/index.php` · `views/projects/index.php` · `views/appointments/index.php`
- **Fix:** Show active filter summary and a "Clear filters" link when a search returns no results.

### UX-7 · Accessibility — form labels not linked to inputs, no aria-labels on icon buttons
- **Fix:** Ensure every `<input>` has a `<label for="...">`. Add `aria-label` to icon-only buttons. Add `aria-hidden="true"` to decorative icons.

### UX-8 · Form submit allows double-click (no disabled state)
- **Fix:** Add JS to disable all submit buttons on first click:
  ```js
  document.querySelectorAll('form').forEach(f => f.addEventListener('submit', () => {
    f.querySelectorAll('button[type=submit]').forEach(b => b.disabled = true);
  }));
  ```

### UX-9 · No search term highlighting in list results
- **Fix:** Wrap matched text in `<mark>` tags in the controller output or via JS.

### UX-10 · Dashboard lacks KPI comparison (no % change vs last month)
- **File:** `views/dashboard/index.php`
- **Fix:** Add previous-period comparison to revenue stats: "€5,000 (+12% vs last month)".

### UX-11 · Nav active link detection can produce false positives
- **File:** `views/includes/header.php:668–690`
- **Issue:** `strpos($currentRoute, '/customers')` matches both `/customers` and `/customers-something`.
- **Fix:** Use exact match or add word-boundary check.

---

## ⚪ PHASE 6 — Code Quality / Refactoring

### CODE-2 · God controllers — 6 controllers over 700 lines
- **Files:** `TransformerMaintenanceController.php` (1625 lines) · `DailyTaskController.php` (1208) · `ProjectController.php` (1040)
- **Fix:** Extract `PhotoUploadService`, `PdfGeneratorService`, `ExcelExportService` classes. Target <300 lines per controller.

### CODE-3 · resizeImage() duplicated in 3 controllers
- **Files:** `TransformerMaintenanceController.php:546` · `DailyTaskController.php:682` · `ProjectTasksController.php`
- **Fix:** Create `classes/PhotoService.php` with a single `resizeImage()` method. Move magic numbers (1920, 1080, 85) to config constants.

### CODE-4 · Input validation gaps
- **Files:** `controllers/CustomerController.php` · `controllers/MaterialsController.php:124` · `models/DailyTask.php:54`
- **Issue:** Only `empty()` checks. No `filter_var` for email, no range validation for prices, no date format check.
- **Fix:** Create a `classes/Validator.php` helper class with `email()`, `integer()`, `date()`, `string()` methods.

### CODE-5 · index.php router — 800 lines, 190+ elseif blocks
- **File:** `index.php:96–850`
- **Issue:** Mixes exact match, strpos, and preg_match routing. Duplicate auth checks on every route.
- **Fix:** Extract to a `classes/Router.php` class with a declarative routes array.

### CODE-6 · die() used in controllers
- **File:** `controllers/ProjectReportController.php:98`
- **Fix:** Replace `die('Project not found')` with `$this->redirect('/projects?error=not_found')`.

### CODE-7 · Magic numbers hardcoded instead of constants
- **Files:** `TransformerMaintenanceController.php:546` · `DailyTaskController.php:682` · `TrashController.php`
- **Fix:** Add to `config/config.php`:
  ```php
  define('IMAGE_MAX_WIDTH', 1920);
  define('IMAGE_MAX_HEIGHT', 1080);
  define('IMAGE_QUALITY', 85);
  define('TRASH_PER_PAGE', 50);
  ```

### CODE-8 · Expand test coverage
- **Path:** `testing/`
- **Issue:** Only 2 test files. Critical flows untested (create project → tasks → invoice, role permissions, overlap detection).
- **Fix:** Add Playwright tests for the main customer/project/invoice workflow and admin vs technician permission checks.

### SEC-9 · Password minimum length only 6 characters
- **File:** `controllers/ProfileController.php:110`
- **Fix:** Require minimum 12 characters with upper/lower/digit/special char checks.

### PERF-8 · Temp image files from maintenance PDF not cleaned up
- **File:** `controllers/MaintenanceOfferController.php:400–414`
- **Fix:** Delete extracted temp images after PDF generation completes.

---

## Notes

- Items marked with the same phase can be worked on in any order within that phase.
- Security items (Phase 1) should never be deferred to a later release.
- Each fix should get its own commit and a version bump following the `v1.8.x` tag scheme.
