# 🔍 HandyCRM — Multi-Agent Audit Report

**Date:** 2026-06-25
**Branch:** main
**Scope:** Full codebase (excludes `vendor/` and `lib/` third-party code)
**Method:** Four specialized agents audited in parallel — Security, Database & Performance, UX/UI & Accessibility, Code Quality & Architecture.
**Total findings:** 94

---

## Executive Summary

### The headline: one config line breaks security app-wide

`config/config.php:61` ships with `define('DEBUG_MODE', true)`. Both the Security and Code Quality agents independently found this is the single worst issue because it:
- **Disables CSRF on every protected form** (`if (!DEBUG_MODE) { validateCsrfToken(); }` — pattern in ~20 controllers)
- **Exposes raw SQL, stack traces, and schema** to users (`classes/Database.php:45,95`)

Flipping it to `false` and making CSRF *unconditional* closes the most severe findings at once.

### Recommended order of attack

1. **Today (1-line + small):** `DEBUG_MODE=false`; rotate the leaked DB password + SECRET_KEY; move CSRF validation into `BaseController` for all POST so it's unconditional and central. → closes ~6 critical findings.
2. **This week:** customer authorization checks (IDOR); convert GET deletes to POST forms; add `uploads/.htaccess`; `session_regenerate_id` + login throttling; fix the `uploadForm` JS typo.
3. **DB pass:** add the `deleted_at` composite indexes; make `Database` a singleton; fix the report N+1 and `getPaginated` subqueries.
4. **Then:** consolidate migrations to one runner/dir, split god controllers, extract `PhotoService`/`PdfService`, fix the i18n + inline-style consistency for the mobile field-tech experience.

---

## 🔴 Critical / High — Cross-Cutting (fix before anything else)

| # | Finding | Evidence |
|---|---------|----------|
| 1 | **`DEBUG_MODE=true` disables CSRF + leaks errors** | `config/config.php:61` |
| 2 | **Plaintext DB password & SECRET_KEY committed to git history** (`Tgyhuj123&*(`) — present in history even though file is now gitignored | `config/config.php:8-21`, git commit `0e176fa` |
| 3 | **IDOR on customers** — `show/edit/update/delete` have *no* permission/ownership check; any technician can read/modify/delete any customer by ID | `controllers/CustomerController.php:165,191,211,313` |
| 4 | **Financial AJAX endpoints unprotected** — `markPaid`/`markUnpaid` lack CSRF *and* permission gates | `controllers/PaymentsController.php:171,227` |
| 5 | **CSRF missing entirely in 11 POST controllers** (DailyTask, Payments, ProjectTasks, Materials, Role, Profile, etc.) | grep across controllers |
| 6 | **Destructive delete via GET link** (quotes) — crawlable/prefetchable, only a JS `confirm()` guard | `views/quotes/index.php:148-153` |
| 7 | **Session fixation** — no `session_regenerate_id()` on login | `controllers/AuthController.php:73-82` |
| 8 | **No brute-force protection** on login (unlimited attempts, `logActivity` is a no-op stub) | `AuthController::authenticate` |
| 9 | **`uploads/` has no `.htaccess`** and is web-served directly; `BaseController::uploadFile` validates by extension only | `uploads/`, `BaseController.php:324` |
| 10 | **Broken photo-upload JS** — handler binds to `photoUploadForm`, form id is `uploadForm` → null error aborts the script, no upload feedback | `views/projects/tasks/photos.php:498` |

**DB/Perf High:**
- **No index on `deleted_at`** on any core table, yet *every* list query filters `deleted_at IS NULL` → full scans on every page.
- **No connection pooling/singleton** — `new Database()` called **91 times across 41 files**; each = a fresh MySQL handshake.
- **N+1 in project report** + **correlated subqueries per row** in `Project::getPaginated()` (note `getAll()` already does it correctly with a JOIN).
- **`SHOW COLUMNS`/`SHOW TABLES` on hot report/dashboard paths** — runtime schema introspection on every PDF generation.

---

## ✅ What's already solid (don't touch)

- **SQL injection: clean** — all queries use PDO prepared statements with `ATTR_EMULATE_PREPARES=false`; sort columns whitelisted.
- **Passwords:** `password_hash`/`password_verify`, no md5/sha1.
- Money columns correctly `decimal(10,2)` (no floats); thorough FK constraints on core tables.
- `auth/login.php` is an accessibility exemplar; `customers/index.php` card-toggle + empty states; `photos.php` breadcrumb + lightbox patterns — propagate these.
- `migrate_to_1.3.5.sql` uses proper idempotent `INFORMATION_SCHEMA` guards — the pattern other migrations should adopt.

---

# 🔒 Security Audit

**Summary:** 5 Critical · 4 High · 5 Medium · 3 Low (17 total)

The app has good bones (PDO prepared statements everywhere, `password_hash`/`password_verify`, MIME-validated uploads in newer controllers, most views escape with `htmlspecialchars`), but a cluster of configuration and auth issues makes it currently exploitable.

### 🔴 CRITICAL

**C1 — CSRF globally disabled in production via `DEBUG_MODE`**
- `config/config.php:61` → `define('DEBUG_MODE', true);`
- Repeated in ~20 controllers, e.g. `controllers/InvoiceController.php:120`, `UserController.php:88`, `ProjectController.php:405`, `AppointmentController.php:165`, `SettingsController.php:76`, `AuthController.php:48`:
  ```php
  if (!DEBUG_MODE) { $this->validateCsrfToken(); }
  ```
- Risk: With `DEBUG_MODE=true`, **every** state-changing POST (create/update/delete users, invoices, projects, settings, even login) skips CSRF validation. An attacker page can forge any action against a logged-in user/admin.
- Fix: Set `DEBUG_MODE=false` on the live server, and remove the `if (!DEBUG_MODE)` guard so `validateCsrfToken()` always runs.

**C2 — Hardcoded DB password & SECRET_KEY, present in git history**
- `config/config.php:11-12`: `define('DB_PASS', 'Tgyhuj123&*(');` and `define('SECRET_KEY', 'bc1f...9380');`
- Same plaintext password and key exist in git history (commit `0e176fa`), even though `config.php` was later untracked (`79975f5`). `git log -p -- config/config.php` still reveals them.
- Risk: Anyone with repo/history access has production DB credentials and the app secret key.
- Fix: **Rotate the DB password and SECRET_KEY now.** Load both from environment variables (`getenv('DB_PASS')`). Purge from history (e.g. `git filter-repo`) or treat the repo as compromised.

**C3 — Verbose DB/error disclosure in production**
- `classes/Database.php:45-46, 94-96`: in `DEBUG_MODE`, raw `PDOException` messages and full SQL are surfaced. `AuthController` and others echo `$e->getMessage()` to flash messages.
- `config/config.php:69-71`: `display_errors = 1`, `error_reporting(E_ALL)`.
- Risk: SQL/schema/stack-trace leakage aids injection and recon.
- Fix: Disable display_errors and debug error surfacing in production (resolved by fixing C1).

**C4 — Broken access control on Customer records (IDOR)**
- `controllers/CustomerController.php`: `show()` (165), `edit()` (191), `update()` (211), `delete()` (313) perform **no** permission/ownership check. Only `index()` (22) checks `customers.view`.
- Risk: Any authenticated user (e.g. a low-privilege technician) can read, modify, or soft-delete **any** customer by ID/slug.
- Fix: Add `if (!$this->isAdmin() && !can('customers.view'/'customers.edit'/'customers.delete')) { redirect }` at the top of each action, matching `UserController::show` (which correctly calls `canViewUser($id)`).

**C5 — Privileged AJAX endpoints lack CSRF and permission checks**
- `controllers/PaymentsController.php:171 markPaid()` and `:227 markUnpaid()`: only check `REQUEST_METHOD === 'POST'` and that a session user exists. No `validateCsrfToken()`, no permission gate.
- Risk: Any logged-in user can mark technician payment weeks paid/unpaid; also CSRF-forgeable.
- Fix: Add `$this->validateCsrfToken();` and a `can('payments.edit')` check at the start of each.

### 🟠 HIGH

**H1 — No session regeneration on login (session fixation)**
- `controllers/AuthController.php:73-82`: session vars set after auth with **no** `session_regenerate_id(true)`.
- Fix: Call `session_regenerate_id(true);` immediately after successful credential verification.

**H2 — Insecure "remember me" cookie**
- `controllers/AuthController.php:86-87`: `setcookie('remember_token', $cookieToken, time()+(86400*30), '/');` — no `Secure`, no `HttpOnly`, no `SameSite`. (Token is never stored server-side, so currently non-functional, but cookie is still set.)
- Fix: `setcookie('remember_token', $token, ['expires'=>..., 'path'=>'/', 'secure'=>true, 'httponly'=>true, 'samesite'=>'Lax']);` and validate against a hashed server-side token.

**H3 — No brute-force / rate limiting on login**
- `controllers/AuthController.php:authenticate()` and `User::authenticate()`: unlimited password attempts; `logActivity()` is a stub (no-op). No lockout, no throttle, no CAPTCHA.
- Fix: Track failed attempts per username/IP and apply progressive delay or temporary lockout.

**H4 — No `.htaccess` in `uploads/`; web-accessible upload tree**
- `uploads/` has no `.htaccess` (only a fragile root rule using `<If "%{REQUEST_URI} =~ m#^/uploads/#">`, which silently fails on Nginx or if `<If>` is unavailable). The rewrite explicitly excludes `/uploads/` from routing so files are served directly.
- Risk: If any non-image file is written here (e.g. via `BaseController::uploadFile()` at line 324, which validates by **extension only** and permits `doc/docx/xls/...`), it could be served/executed.
- Fix: Add `uploads/.htaccess` with `php_flag engine off` + `RemoveHandler/RemoveType` for PHP, and `Require all denied` on script extensions. Store uploads outside webroot and stream via a controller (as KnowledgeBase/PriceList already do).

### 🟡 MEDIUM

**M1 — Reflected XSS via unescaped `$_GET` in views**
- `views/projects/show.php:610,615`: `value="<?= $_GET['materials_date_from'] ?? '' ?>"`
- Also `views/technicians/view.php:152,156`, `views/projects/tasks/index.php:111,116`, `views/knowledge_base/categories.php:16`.
- Risk: Attribute-breakout XSS, e.g. `?materials_date_from="><script>...`. These params bypass `sanitize()`.
- Fix: Wrap every `$_GET`/user echo in `htmlspecialchars(..., ENT_QUOTES)`.

**M2 — CSRF token compared with `!==` (non-constant-time)**
- `classes/BaseController.php:254`: `$token !== $_SESSION['csrf_token']`.
- Fix: Use `hash_equals($_SESSION['csrf_token'], $token)`.

**M3 — Weak password policy**
- `controllers/AuthController.php:465`: reset password requires only `strlen >= 6`. No complexity, no breach check.
- Fix: Enforce stronger minimum (≥12) and complexity.

**M4 — Full source backups inside webroot**
- `backups/` contains 643 `.php` source copies. Protected only by a single `backups/.htaccess` (`Deny from all`), which fails on Nginx/`AllowOverride None`.
- Fix: Move backups outside the document root.

**M5 — Session cookie hardening / lifetime handling**
- `config/config.php:78-89`: `session_start()` with no `session_set_cookie_params` (no `Secure`/`HttpOnly`/`SameSite` on PHPSESSID), and HTTPS redirect in `.htaccess` is commented out (lines 7-9).
- Fix: Set secure session cookie params and enforce HTTPS.

### 🟢 LOW

**L1 — `BaseModel` interpolates `$orderBy`/`$limit`/field names** (`classes/BaseModel.php:41,45,74,109,143`). No current caller passes raw user input, but the API is injection-prone if reused. Whitelist `$orderBy` and cast `$limit` to int inside BaseModel.

**L2 — `sanitize()` HTML-encodes at input** (`classes/BaseController.php:281`). Encoding on the way *in* corrupts stored data and is the wrong layer for XSS defense. Escape on output instead.

**L3 — `isLoginPage()` substring check** (`classes/BaseController.php:36-37`): auth bypass guard relies on `strpos($_SERVER['REQUEST_URI'], '/login')`. Any URL containing `/login` is treated as the login page and skips the auth check. Low impact (controllers re-check), but tighten to an exact route match.

**Notably NOT vulnerable (verified):** SQL injection (all PDO prepared statements; `Project::getPaginated` whitelists sort columns); command injection (`BackupManager`/`UploadedContract` use `escapeshellarg`); password storage (`password_hash` + `password_verify`); no `unserialize()` of user input; `UserController::show` correctly enforces ownership; newer upload controllers validate real MIME via `finfo`.

---

# 🗄️ Database & Performance Audit

**Summary:** 6 High · 10 Medium · 7 Low (23 findings)

> **Important context:** there are **three sources of truth** for the schema — `database/schema.sql` (oldest), `database/handycrm.sql` (later dump, has `slug` columns/indexes), and the live database (has `deleted_at` on core tables, plus tables like `daily_tasks`, `daily_task_technicians`, `task_photos`, `payments`, `roles` that exist **only** via migrations or runtime DDL). Several core tables (`daily_tasks`, `daily_task_technicians`) have **no DDL anywhere in the repo** — created at runtime, so indexing is almost certainly minimal.

### 🔴 HIGH

**H1 — No index on `deleted_at` on any core table, yet every list query filters `deleted_at IS NULL`**
`projects`, `customers`, `daily_tasks`, `project_tasks`, `quotes`, `invoices`. Filter appears in `models/Project.php:32,67,136,140`, `models/DailyTask.php:38,100`, `controllers/ProjectController.php:294`, and 15+ times in `database/useful_queries.sql`. Only the two newest tables have such an index (`migrations/020...:27`, `023...:24`). Core tables fall back to full scans + filter.
Fix (composite indexes, `deleted_at` first then common filters):
```sql
ALTER TABLE projects       ADD INDEX idx_del_status_customer (deleted_at, status, customer_id);
ALTER TABLE projects       ADD INDEX idx_del_created (deleted_at, created_at);
ALTER TABLE customers      ADD INDEX idx_del_created (deleted_at, created_at);
ALTER TABLE project_tasks  ADD INDEX idx_del_project (deleted_at, project_id);
ALTER TABLE daily_tasks    ADD INDEX idx_del_date (deleted_at, date);
```

**H2 — `project_tasks.deleted_at` is queried but the column is not in any DDL and is unindexed**
`migrations/add_project_tasks_system.sql:39-62` creates `project_tasks` with **no `deleted_at` column**; yet `models/Project.php:136,140,209` and `controllers/ProjectController.php:294` filter `pt.deleted_at IS NULL`. Column added by an out-of-band runtime ALTER → schema-drift hazard + missing index. Fix: add `deleted_at DATETIME DEFAULT NULL` + `INDEX idx_del_project (deleted_at, project_id)` to the canonical migration.

**H3 — No connection reuse: a new PDO connection per model/controller (no singleton)**
`classes/Database.php` caches PDO only within a single instance, but `classes/BaseModel.php:13` does `new Database()` in every model constructor — **91 `new Database()` calls across 41 files**. Each = its own TCP/auth handshake. Fix: make `Database` a singleton (static shared PDO):
```php
class Database {
    private static $pdo = null;
    public function connect() {
        if (self::$pdo === null) { self::$pdo = new PDO(...); }
        return self::$pdo;
    }
}
```

**H4 — `Project::getPaginated()` opens a *second* connection and uses per-row correlated subqueries**
`models/Project.php:61-62` does `new Database()` even though `$this->db` exists. `Project.php:133-140` runs **two correlated subqueries per result row** (`SUM(task_materials)` + `SUM(task_labor)`). 20 rows = 40 subquery executions per page. `Project::getAll()` (line 200-211) already does it correctly with a single derived-table `LEFT JOIN ... GROUP BY` — adopt that pattern.

**H5 — N+1 in project report materials loop**
`controllers/ProjectController.php:300-307`: loops `foreach ($tasksWithMaterials as $task)` running `SELECT ... FROM task_materials WHERE task_id = ?` per task. 20 tasks = 21 queries. Fix: one query `WHERE task_id IN (...)` grouped by `task_id` in PHP.

**H6 — Runtime `SHOW COLUMNS` schema introspection on every PDF/report generation**
`controllers/ProjectReportController.php:247,261,266` run **3+ `SHOW COLUMNS FROM task_labor`** every time `getAggregatedLabor()` is called. Columns are stable post-migration. Fix: remove the checks or cache in a `static` once per request.

### 🟡 MEDIUM

**M1 — `migrate_to_1.3.5.sql` records itself into wrong migrations-table columns → guaranteed failure**
`migrations/migrate_to_1.3.5.sql:133` inserts into `migrations (filename, executed_at)`, but `AutoMigration.php:26-31` creates `migrations(migration, executed_at[, version])` and `MigrationManager.php:24-31` creates `migrations(version, migration_name, executed_at)`. **Neither has a `filename` column** → "Unknown column 'filename'". Three conflicting schemas exist. Fix: standardize one migrations-table schema.

**M2 — Two migration runners + two directories with conflicting tracking/order**
`migrations/` (root, consumed by `AutoMigration.php`) vs `database/migrations/` (consumed by `MigrationManager.php`). Different tracking tables, different ordering. Filename sort means `019_...` runs before lettered migrations it depends on → order-dependent failures masked by error-swallowing. Fix: consolidate to one directory + one runner with explicit numeric ordering.

**M3 — Migration runner swallows real errors**
`AutoMigration.php:170-183` and `MigrationManager.php:157-168` catch and `continue` for anything containing "already exists"/"duplicate"/"Table", but **still mark the migration executed** even when statements failed. A broken statement leaves the schema half-applied but flagged "done". Fix: distinguish idempotent-safe errors from real ones; don't mark executed on real failure.

**M4 — No transaction wrapping in migration execution**
`AutoMigration.php:162-184` / `MigrationManager.php:147-169` execute statements one-by-one with no transaction. Failure midway leaves partial schema. Fix: stop-on-error and report.

**M5 — `SELECT *` throughout base model and detail queries**
`classes/BaseModel.php:20,28,127`; `models/Project.php:14,124,187,343`; `models/DailyTask.php:26,291`. Wide rows (projects ~25 cols incl. several `text`) fully materialized. Fix: optional `$columns` param; explicit columns on lists.

**M6 — `Project::getAll()` has no LIMIT — full-table load, used for dropdowns**
`models/Project.php:186-215` selects all projects (with cost-aggregation join). Grows unbounded. Fix: add `LIMIT` or a dedicated `getAllForSelect()` returning only `id,title`.

**M7 — Charset mismatch in `handycrm.sql` dump (utf8 vs utf8mb4) risks Greek-text corruption**
Dump mixes `SET character_set_client = utf8` with `utf8mb4` tables. Runtime is correctly forced to utf8mb4 (`Database.php:34,40-41`), but importing the dump on a fresh box can down-convert. Fix: replace `utf8`/`utf8_*` with `utf8mb4`/`utf8mb4_unicode_ci` throughout the dump.

**M8 — `appointments` lacks a composite index for technician + date**
`database/handycrm.sql:43-48` indexes columns separately. Calendar views filter technician within a date window.
```sql
ALTER TABLE appointments ADD INDEX idx_tech_date (technician_id, appointment_date);
```

**M9 — `daily_task_technicians` aggregation runs uncached derived-table subquery per list page**
`models/DailyTask.php:33-37` joins a `GROUP BY` derived table for `SUM(hours_worked)` on every paginated list; `find()` (294-298) repeats it as a correlated subquery. Fix: ensure `INDEX(daily_task_id)`; consider denormalizing `hours_worked` onto the parent (`updateHoursWorked()` at line 282 is half-built for this).

**M10 — `decimal(5,2)` on `hours_worked`/`estimated_hours` caps at 999.99**
`migrations/add_project_tasks_system.sql:97` and `handycrm.sql`. A multi-week date_range task can exceed 999.99 hours and truncate. (Money columns are correctly `decimal(10,2)`.) Fix: widen hours to `decimal(7,2)`.

### 🟢 LOW

**L1 — `BaseModel::findAll()/paginate()` interpolate `$orderBy` and `$limit` directly** (`BaseModel.php:41,45,143`). Callers must whitelist. Worth hardening centrally.

**L2 — `Project::getStats()` runs 4 separate aggregate queries** (`models/Project.php:250-283`) — status + active-count could merge; also ignore `deleted_at`. Cache on dashboard.

**L3 — Dashboard aggregations uncached** (`DashboardController.php:111-176`) — re-run every load. Cheap APCu/session cache.

**L4 — `count()` + data query rebuild the same WHERE twice in PHP** (`BaseModel.php:99-156`, `DailyTask.php`). Mitigated once H1 indexes land.

**L5 — `materials.category` is free-text `varchar(100)` while a normalized `material_categories` table exists** (`add_materials_system.sql:5`). Two category systems invite inconsistency.

**L6 — `notifications.is_read` single-column index is low-selectivity** (`handycrm.sql:333`). Real query is "unread for user X". Fix: `INDEX idx_user_unread (user_id, is_read)`.

**L7 — `task_labor.role_id` / `paid_by` referenced without FK/index.** 1.3.5 migration adds `idx_task_labor_tech_paid(technician_id, paid_at)` (good) but `paid_by`/`role_id` remain unindexed if filtered/joined.

**Already good:** Money consistently `decimal(10,2)` (no floats); PDO `ATTR_EMULATE_PREPARES=false` + exception mode; thorough FK constraints; `migrate_to_1.3.5.sql` uses idempotent `INFORMATION_SCHEMA` guards (the pattern others should adopt); `Project::getAll()` is the correct aggregation template.

---

# 🎨 UX/UI & Accessibility Audit

**Summary:** 8 High · 12 Medium · 7 Low (27 findings)
Server-rendered PHP/Bootstrap 5 CRM, Greek UI, field-technician audience (mobile matters a lot).

### 🔴 HIGH

**H1 — Destructive delete via GET link (bypasses CSRF + crawlable/prefetchable)**
`views/quotes/index.php:148-153`, `views/quotes/view.php:207`. Quote deletion is an `<a href=".../quotes/delete/<id>" onclick="return confirm(...)">`. GET is not CSRF-protected, triggerable by prefetch/scanners, JS-only guard fails open. Contrast `customers/index.php:156-163` (correct POST form + CSRF). Fix: convert all deletes to POST forms with CSRF hidden field.

**H2 — Broken JS: photo-upload submit handler never binds**
`views/projects/tasks/photos.php:498`. Form id is `uploadForm` (line 314) but handler calls `getElementById('photoUploadForm')` → `null` → `Cannot read properties of null`, aborting the rest of the inline script. Progress bar / disabled-state / AJAX never run. Fix: `getElementById('uploadForm')` + null check.

**H3 — Inconsistent theming: per-view inline `<style>` overrides the global design system**
`views/customers/create.php:321-358`, `views/projects/tasks/photos.php:10-237`. Global theme defines `--accent:#0ea5e9` (blue), but create.php redefines buttons with a **purple gradient `#667eea→#764ba2`**, gallery uses five more gradients. Same button blue on one screen, purple on the next. Fix: delete inline overrides; rely on shared CSS variables.

**H4 — Gallery images not lazy-loaded (perceived perf + mobile data)**
`views/projects/tasks/photos.php:381`, `views/daily-tasks/show.php:224`, `views/maintenances/view.php:278`, `views/projects/show.php:812`, `views/projects/tasks/view.php:219`. Only 1 of ~10 `<img>` uses `loading="lazy"`. A 30-photo task downloads all full-res immediately on a phone. Fix: add `loading="lazy"` + `decoding="async"`; serve server-side thumbnails.

**H5 — Large render-blocking inline scripts on every page**
`views/includes/footer.php:57-389`. ~330 lines inline JS run on every authenticated page, plus jQuery + Bootstrap from three CDNs, no `defer`. `loadNotifications()` + `checkForUpdates()` fire two XHRs every load. Fix: external cached file with `defer`; gate polling; lazy-load page-specific helpers.

**H6 — Hardcoded Greek strings throughout, defeating i18n**
`views/includes/header.php:704,712,720,752,818,834,798`; entire `views/daily-tasks/index.php`. App ships `languages/el.json` + `en.json` and uses `__()` in many views, but core nav items and whole views (daily-tasks, photos) are hardcoded Greek. Switching to English leaves a half-translated UI. Fix: replace literals with `__()` keys; add missing keys; add a CI check.

**H7 — Page title computed by fragile URL string-matching**
`views/includes/header.php:868-890`. Navbar title from a 20-branch `if/elseif strpos($uri, ...)` chain. Order-dependent; new routes silently fall through to "Dashboard". Fix: each controller passes an explicit `$pageTitle`.

**H8 — No keyboard accessibility on icon-only clickable elements**
`views/auth/login.php:272` (`<span onclick="togglePassword()">`), `views/daily-tasks/show.php:227` (`<img onclick="openLightbox()">`), `views/projects/tasks/photos.php:126`. Non-focusable elements with `onclick` — unreachable by keyboard, no `role`/`aria-pressed`. Fix: use `<button type="button">` with `aria-label`/`aria-pressed`; wrap image triggers in `<a>`/`<button>` (photos gallery already does this correctly — apply everywhere).

### 🟡 MEDIUM

**M1 — Client-side validation is the only required-field signal in some forms** — `views/customers/create.php:12` (`novalidate`) + JS gate. Server errors exist (good). Fix: keep server validation authoritative; ensure server errors render for every required field.

**M2 — Inconsistent flash/alert handling** — `header.php:939-958` centralizes flash, yet `quotes/index.php`, `projects/show.php`, `daily-tasks/index.php`, `photos.php` re-implement their own `$_SESSION['success']/['error']` blocks (two mechanisms). Fix: standardize on header's flash partial.

**M3 — Action buttons identified by icon + `title` only (no accessible name)** — `customers/index.php:130,253-262`, `quotes/index.php:140-153`. Kebab `<button><i fa-ellipsis-v></i></button>` has no `aria-label`. Fix: add `aria-label` to every icon-only control.

**M4 — Tables overflow on mobile; horizontal scroll is the only affordance** — `customers/index.php:199`, `quotes/index.php:73`, `dashboard/index.php:109`. `.table-responsive` used but a 7–8 column table on 380px becomes a tiny scroll strip. Fix: card/stacked layouts on mobile for high-traffic technician lists.

**M5 — Touch targets below 44px on dense action groups** — `customers/index.php:391-394`, `quotes/index.php:139`, photo delete 32×32px. Delete next to edit → mis-taps. Fix: enforce ≥44×44px; space destructive actions apart.

**M6 — Empty states inconsistent across lists** — customers/quotes have good ones; dashboard tables and daily-tasks have none/differ. Fix: reusable empty-state partial.

**M7 — Pagination duplicated and inconsistent** — `quotes/index.php:163-191` hand-rolls full markup and renders *every* page number (breaks at 100+ pages); `customers/index.php:304-307` just echoes `$pagination`. Fix: single pagination partial with ellipsis + result count.

**M8 — No breadcrumbs on most views; only photos.php has them** — `photos.php:241-253` has a proper `<nav aria-label="breadcrumb">`. Deep views (projects/show, daily-tasks/show, maintenances/view) have none. Fix: consistent breadcrumbs on detail views.

**M9 — Inline `style="..."` scattered** — `header.php:900,920,924`, `dashboard/index.php:14,47`, `daily-tasks/show.php:226,395`. Hard to theme; needs CSP `unsafe-inline`. Fix: utility/component classes.

**M10 — Auto-capitalize / input mangling can corrupt user data** — `customers/create.php:281` title-cases names on blur; `:269-273` rewrites phone digits. Alters legitimate input ("McDonald", "+30"). Fix: don't mutate input on the fly; validate on submit.

**M11 — Search filters auto-submit on `change`** — `customers/index.php:354-356`. Combined with a separate search button → inconsistent behavior. Fix: pick one model; debounce + loading indicator.

**M12 — Login button uses a 6-second `setTimeout` to clear loading state** — `auth/login.php:328`. Clears too early on slow connections, spins needlessly on fast. Fix: let navigation/response drive state.

### 🟢 LOW

**L1 — `confirm()` for all destructive actions** — unstyled, non-localizable, easy to click through. Consider a themed modal (one already exists for CSV import in `customers/index.php:398`).

**L2 — Heading levels skip around** (`customers/index.php` jumps h2→h5→h6; dashboard stat cards use `div.stat-value`). Inconsistent screen-reader outline.

**L3 — Color-only status encoding** — `quotes/index.php:95-102`, `projects/show.php:39-56`. Text labels present (good) but expired rows rely on `table-warning` alone. Keep non-color cues.

**L4 — Footer hardcodes author/email, non-translatable** (`footer.php:36-42`).

**L5 — Ad hoc asset strategy** — `data-formatter.js` loaded after all inline scripts (`footer.php:405`); 3 CDNs + 1 local file + inline.

**L6 — Sidebar active-state uses `strpos` URL matching** (`header.php:676,684,703,...`) with special-case exclusions — same brittleness class as H7, cosmetic impact.

**L7 — `placeholder` used as the only hint in some inputs** (`daily-tasks/index.php:39`). Placeholders disappear on focus, low contrast. Labels exist here (good); ensure placeholders aren't the sole instruction.

**Done well (keep):** `auth/login.php` (real `<label for>`, `autocomplete`, `autofocus`, focus rings); `customers/index.php` empty state + card/table toggle with `localStorage`; CSRF tokens in customer POST forms + AJAX; `photos.php` breadcrumb + `<a data-lightbox>`; global design system via CSS variables (the problem is views overriding it).

---

# 🧱 Code Quality & Architecture Audit

**Summary:** 7 High · 11 Medium · 9 Low (27 findings)

Genuinely MVC-structured with a consistent PDO layer (no legacy `mysql_*`), parameterized queries, clean BaseModel/BaseController. Dominant problems: a debug flag that disables security, inconsistent CSRF, a dead 159-line `Router.php` shadowed by a 1026-line hand-rolled router, several 1000+ line "god" controllers, copy-pasted image/PDF logic.

### 🔴 HIGH

**H1 — `DEBUG_MODE = true` in committed config, and it disables CSRF**
`config/config.php:61`; bypass at `AuthController.php:48`, `AppointmentController.php:165,302,347`, many others. Exposes SQL/stack traces (`Database.php:45,95`) and short-circuits CSRF. Fix: `DEBUG_MODE=false` (via `getenv('APP_DEBUG')`); remove the `if (!DEBUG_MODE)` guard so CSRF is always validated.

**H2 — Plaintext production DB credentials & SECRET_KEY** in `config/config.php:8-21`. `.gitignore`d but unencrypted on disk and in history. Fix: rotate; load via `getenv()`/`.env` outside web root.

**H3 — Inconsistent CSRF enforcement across POST controllers**
11 controllers handle POST with **zero** `validateCsrfToken()`: `DailyTaskController`, `PaymentsController`, `ProjectTasksController`, `MaterialsController`, `RoleController`, `ProfileController`, `TransformerMaintenanceController`, `QuoteExportController`, `ProjectReportController`, `UpdateController`, `DashboardController`. Fix: enforce CSRF centrally in `BaseController` for any `REQUEST_METHOD === POST`.

**H4 — `die()` / `exit('...')` in business logic leaks raw text & skips layout**
`ProjectReportController.php:110`, `ProjectTasksController.php:684`, `KnowledgeBaseController.php:192,198,204`, `PriceListsController.php:109,115,121`, `UploadedContractController.php:246,252`, `Database.php:46-48`. Fix: replace with `$this->redirect(...)` or `http_response_code()` + error view.

**H5 — `classes/Router.php` is dead code; routing is a 1026-line `index.php`**
`Router.php` (159 lines) never referenced. `index.php` hand-rolls **97 `elseif ($currentRoute ...)` branches** mixing exact-match, `strpos()`, and per-branch auth checks duplicated dozens of times. Fix: adopt `Router.php` with a declarative routes table (auth in middleware), or delete it. Duplicated login check belongs in `BaseController::checkAuth()`.

**H6 — Debug `error_log` / `print_r` left in production paths**
`ProjectReportController.php:234`, `PaymentsController.php:472`, `DailyTaskController.php:307-308` (`print_r` of POST technician data). Fix: delete; gate diagnostics behind `if (DEBUG_MODE)`.

**H7 — Runtime schema introspection on the hot report path**
`ProjectReportController.php:247,261,266` (3× `SHOW COLUMNS FROM task_labor`), `DashboardController.php:181` (`SHOW TABLES LIKE 'quotes'`). Fix: remove or cache in a `static`.

### 🟠 MEDIUM

**M1 — `resizeImage()` duplicated almost verbatim** — `DailyTaskController.php:682` & `TransformerMaintenanceController.php:546` (~55 lines each); `SettingsController.php:237` a third variant. Magic numbers `1920,1080,85` repeated. Fix: extract `classes/PhotoService.php::resize()`; move dims/quality to config.

**M2 — God controllers (>700 lines)** — `TransformerMaintenanceController` (1625), `DailyTaskController` (1208), `ProjectController` (1040), `ProjectTasksController` (1009), `ProjectReportController` (910), `MaterialsController` (761), `CustomerController` (744); models `ProjectTask` (808), `UploadedContract` (567). Fix: extract `PhotoUploadService`, `PdfGeneratorService`, `ExcelExportService`. Target <300 lines.

**M3 — HTML/presentation generated inside controllers** — `ProjectReportController.php` (~39 lines inline HTML), `TransformerMaintenanceController.php` (~14), others. Fix: move markup to view templates.

**M4 — Hardcoded VAT despite `DEFAULT_VAT_RATE` constant** — `ProjectReportController.php:861`: `$projectTotal * 0.24` ignores `DEFAULT_VAT_RATE` (config.php:53). `ContractController.php:91`, `ProjectController.php:743` do it correctly. Fix: use the constant everywhere.

**M5 — Two migration directories with no documented order** — `migrations/` and `database/migrations/`, plus `database/handycrm.sql` vs `schema.sql` (two full schemas). Fix: consolidate to one directory + one numbering scheme; pick one canonical schema.

**M6 — Inconsistent model base usage** — `models/Settings.php` and `models/Trash.php` don't extend `BaseModel`. `Settings` uses raw `$database->connect()` + static cache; `Trash` takes `$db` as constructor arg while others take none. Fix: extend `BaseModel`; standardize constructor.

**M7 — `@` error suppression hides IO failures** — `UpdateChecker.php:37,145`, `BackupManager.php:116`, `QuoteExportController.php:96,327,340,343`, `KnowledgeBaseController.php:181`, `PriceListsController.php:150`, `TransformerMaintenanceController.php:1565`. Fix: check return values and `error_log` on failure.

**M8 — `Project::getAll()` / paginated list performance** — no LIMIT on `getAll()` (used for dropdowns); correlated cost subqueries per row. Fix: `getAllForSelect()` with LIMIT; aggregate via JOIN/GROUP BY or cached column.

**M9 — Weak-token "run once" composer scripts in deploy tree** — `run_composer_install.php`, `run_composer_update.php`, `run_composer_audit.php` gated by `SECRET_TOKEN = 'handycrm2026'`. Fix: delete from server after use; never deploy.

**M10 — No automated test coverage / no CI** — `testing/` has a single Playwright exploratory agent; `.github/` has only `copilot-instructions.md`. Critical flows (project→task→invoice, role permissions, overlap detection, payment marking) untested. Fix: focused tests for money/permission paths; wire CI.

**M11 — `BaseModel` everywhere uses `SELECT *`** — `BaseModel.php:20,28,127`. Fix: optional `$columns = ['*']` param.

### 🟡 LOW

**L1 — Magic numbers/strings throughout** — image dims, VAT `0.24`, pagination defaults. Centralize in `config.php`.

**L2 — Large commented-out blocks** — `TransformerMaintenanceController.php` (165 lines), `DailyTaskController.php` (99), `ProjectController.php` (76), `ProjectReportController.php` (74). Review and delete dead code.

**L3 — Unresolved TODO/FIXME** — `MaintenanceOfferController.php` (3), `TransformerMaintenanceController.php` (4), `models/MaintenanceOffer.php`, `models/UploadedContract.php`. Triage into backlog.

**L4 — `BaseController::sanitize()` HTML-escapes on input** (`:281`) — corrupts stored data (double-encoding on edit). Escape at output.

**L5 — `validateRequired()` returns a hardcoded Greek string** (`BaseController.php:292`) bypassing `__()`.

**L6 — `redirect()` does `session_write_close()` then `session_start()`** (`BaseController.php:165-167`) on every redirect — fragile; can emit "headers already sent". Simplify.

**L7 — 404 handling inconsistent** — `index.php` emits bare `echo "<h1>404"` in several branches but `include 'views/errors/404.php'` in the final `else`. Route all 404s through the error view.

**L8 — Multiple version sources of truth** — `config.php:16` (`APP_VERSION`), `VERSION` file, `VersionManager.php`. Consolidate.

**L9 — `composer.json` has no `require-dev`, no autoload, no PHP version constraint** — only 4 runtime deps. Add a `php` platform constraint + PHPUnit under `require-dev`.

---

## 📋 tobefixed.md status

The existing `tobefixed.md` (dated 2026-06-19) is a thorough, well-prioritized 6-phase backlog — but **almost nothing in it has been implemented yet**; every Phase 1 security item is still live in code.

- **Still open (verified):** SEC-1 `DEBUG_MODE=true`, SEC-2 CSRF-disabled-in-debug, SEC-3 plaintext creds, CODE-1 debug logging, CODE-2 god controllers, CODE-3 `resizeImage` duplicated (3 copies), CODE-5 mega-router, CODE-6 `die()`, CODE-7 magic numbers, CODE-8 thin tests, PERF-2 `SHOW COLUMNS`, PERF-3 `getAll()` no LIMIT, DB-10 two migration dirs.
- **New beyond the doc:** customer IDOR (C4); broken upload JS (UX H2); the missing-CSRF problem spans **11** controllers, not the 2 documented (SEC-5); an extra debug log at `PaymentsController:472`.

**Net:** `tobefixed.md` is a solid, still-valid roadmap. The highest-leverage next step is its Phase 1 — flip `DEBUG_MODE` off and make CSRF unconditional and central — which closes the most critical findings simultaneously.
