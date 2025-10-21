# HandyCRM - Change Log

## [1.3.5] - 2025-10-21

### 🎯 Major Features

#### 1. Advanced Payment Management System
- **Summary Statistics Dashboard**:
  - Grand totals card at top of payments page
  - Shows overall earnings, paid amounts, unpaid amounts
  - Progress bar indicating payment completion percentage
  - Real-time calculation across all technicians
  - Gradient styling with modern UI

- **Quick Date Preset Buttons**:
  - 4 quick-select buttons: "Τρέχουσα Εβδομάδα", "Προηγούμενη Εβδομάδα", "Τρέχων Μήνας", "Προηγούμενος Μήνας"
  - Auto-fills start and end date fields
  - JavaScript-based instant date calculation
  - Located next to date filter inputs for easy access

- **CSV Export Functionality**:
  - Export button in payments toolbar
  - Exports all payment records with applied filters
  - Includes: technician name, date, project, task, hours, rate, amount, payment status
  - Proper CSV formatting with UTF-8 BOM for Greek characters
  - Downloads as `payments_YYYY-MM-DD.csv`

- **Bulk Payment Actions**:
  - "Επισήμανση Όλων ως Πληρωμένα" button
  - Shows confirmation modal with list of all unpaid technicians
  - Displays each technician's name and unpaid amount
  - Shows total amount to be marked as paid
  - AJAX endpoint `/payments/mark-all-paid` for bulk update
  - Supports ALL user roles (admin, supervisor, technician, assistant)
  - Uses `data-unpaid-amount` attribute for accurate amount detection

- **Visual Enhancements**:
  - Progress bar in each technician's card header showing payment percentage
  - Color-coded amounts: green for paid, red for unpaid
  - Enhanced tooltips with detailed information (hours, rate, calculation)
  - Role badges next to technician names (e.g., "ΤΕΧΝΙΚΟΣ", "ΥΠΕΥΘΥΝΟΣ ΣΥΝΕΡΓΕΙΟΥ")
  - Gradient header backgrounds (purple to blue gradient)
  - Improved filter section layout with better spacing

#### 2. Role-Based Access Control System
- **Four-Tier Role System**:
  - **Admin (Διαχειριστής)**: Full system access
  - **Supervisor (Υπεύθυνος Συνεργείου)**: Projects, materials, own profile
  - **Technician (Τεχνικός)**: Only own profile/timecard
  - **Assistant (Βοηθός Τεχνικού)**: Only own profile/timecard

- **BaseController Permission Methods**:
  - `isAdmin()`, `isSupervisor()`, `isTechnician()`, `isAssistant()` - Role checks
  - `canManageProjects()` - Returns true for Admin OR Supervisor
  - `canManageMaterials()` - Returns true for Admin OR Supervisor
  - `canViewUser($userId)` - Admin sees all, others only themselves
  - `requireAdmin()` - Guard method, redirects if not admin
  - `requireSupervisorOrAdmin()` - Guard for supervisor+ access

- **Dynamic Sidebar Menu**:
  - Menu items shown/hidden based on user role
  - Admin: All menu items visible
  - Supervisor: Dashboard, Projects, Materials, "Η Καρτέλα μου"
  - Technician/Assistant: Only "Η Καρτέλα μου"
  - Implemented in `views/includes/header.php` with `$_SESSION['role']` checks

- **Database Migration**:
  - Updated `users` table with new role ENUM
  - Migration: `migrations/update_user_roles.sql`
  - `ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'supervisor', 'technician', 'assistant')`
  - Default role remains 'technician'

- **Form Updates**:
  - User create/edit forms updated with all 4 role options
  - Dropdown in `views/users/edit.php` and `views/users/create.php`
  - Greek labels: Διαχειριστής, Υπεύθυνος Συνεργείου, Τεχνικός, Βοηθός Τεχνικού

### 🐛 Bug Fixes

#### Critical PHP Reference Bug
- **Issue**: Duplicate technician cards appearing in payments page
  - Example: "Σφακιανάκης Θεόδωρος" appeared twice, "Χάρης Μαγάκης" missing
  - Caused by PHP foreach loop with reference `&$tech` not being unset

- **Root Cause**: 
  ```php
  foreach ($weeklyData as &$tech) { 
      // modifications 
  }
  // Reference persists after loop!
  // Next array access overwrites last element
  ```

- **Fix**: Added `unset($tech)` after foreach loop in `PaymentsController.php`
  ```php
  foreach ($weeklyData as &$tech) { 
      // modifications 
  }
  unset($tech); // CRITICAL: Break reference
  ```

#### Payment Query Role Restriction
- **Issue**: Supervisors not appearing in payments list
- **Cause**: Payment model query had `WHERE u.role IN ('technician', 'assistant')`
- **Fix**: Removed role restriction, now queries all active users with labor entries
- **Result**: All roles (admin, supervisor, technician, assistant) now visible in payments

#### Bulk Payment Amount Detection
- **Issue**: Amounts showing incorrectly in bulk payment modal (e.g., 15000.00€ instead of 150.00€)
- **Cause**: JavaScript reading all `.text-danger` elements including table rows
- **Fix**: 
  - Added `data-unpaid-amount` attribute to unpaid amount element
  - JavaScript now reads from `unpaidElement.dataset.unpaidAmount` for accurate values
  - Only checks header elements, ignores table body

#### UI Color Contrast
- **Issue**: Blue text on purple gradient background unreadable
- **Fix**: Changed to white text (`text-white`) on gradient headers

### 🔧 Technical Improvements

- **Code Quality**:
  - Added comprehensive debug logging to PaymentsController
  - Error logging in `mark-all-paid` endpoint for troubleshooting
  - Better separation of concerns in payment detection logic

- **Data Integrity**:
  - Payment model now includes `u.is_active = 1` check
  - Only active users shown in payment lists
  - Consistent query structure across payment endpoints

- **Frontend Performance**:
  - Optimized DOM queries in bulk payment JavaScript
  - Single attribute read instead of multiple element scans
  - Faster modal population with direct data access

### 📝 Files Changed

#### New Files
- `migrations/update_user_roles.sql` - Role system migration

#### Modified Files
- `controllers/PaymentsController.php` - Fixed foreach bug, added bulk payment endpoint
- `models/Payment.php` - Removed role restriction, added role field to query
- `classes/BaseController.php` - Added permission methods
- `views/includes/header.php` - Implemented role-based menu
- `views/payments/index.php` - Added all payment enhancements, visual improvements
- `views/users/edit.php` - Added supervisor role option
- `views/users/create.php` - Added supervisor role option
- `README.md` - Updated to v1.3.5 with new features
- `CHANGELOG.md` - This file

### 🎨 UI/UX Improvements

- Gradient header backgrounds (purple to blue)
- Role badges with light background next to names
- Progress bars with color coding (green=100%, blue=>50%, yellow=<50%)
- Enhanced tooltips with detailed payment information
- Improved spacing and alignment in filter section
- Better visual hierarchy with summary statistics at top

### ✅ Completed Tasks

1. ✅ Summary Statistics Card - Grand Totals
2. ✅ Quick Date Presets Buttons  
3. ✅ Export to Excel/CSV Button
4. ✅ Bulk Actions - Mark All Paid
5. ✅ Visual Improvements - Progress Bars & Tooltips
6. ✅ Role-Based Access Control

---

## [1.3.0] - 2025-10-17

### 🎯 Major Features

#### 1. PDF Project Reports with Advanced Customization
- **Professional PDF Generation**:
  - Complete project report with logo, company details, and customer information
  - Three main sections: Tasks (Εργασίες), Materials (Υλικά), Labor (Ημερομίσθια)
  - Summary section with total calculations (Materials, Labor, Grand Total)
  - Custom footer with company contact details on every page
  - TCPDF 6.7.5 integration with custom styling

- **Date Range Filtering**:
  - "All Dates" checkbox for complete project history (default)
  - Custom date range with from/to date pickers
  - Filters tasks, materials, and labor within selected period
  - Maintains data integrity across related tables

- **Price Hiding Option**:
  - Checkbox to hide all pricing information
  - Technical reports without financial details
  - Conditional table columns (shows only description/quantity when hiding prices)
  - Adjusted summary cards (shows counts instead of totals)

- **Report Notes Field**:
  - Multi-line textarea (4 rows) in report modal
  - Appears at end of PDF with title "ΠΑΡΑΤΗΡΗΣΕΙΣ"
  - Clean gray background box with blue top border
  - nl2br conversion for proper line breaks
  - htmlspecialchars for security

- **Advanced PDF Styling**:
  - Multi-page support with automatic table splitting
  - Headers repeat on every page (TCPDF `nobr="true"` on `<thead>`)
  - Optimized column widths: Tasks (25%/75%), Materials (40%/20%/20%/20%), Labor (30%/15%/15%/20%/20%)
  - Equal-height summary cards (80px) with vertical centering
  - Black header text on blue gradient background for readability
  - Minimal blue accents (only horizontal lines under section titles)
  - Proper spacing: 100px top margin for summary section, 40px for notes

- **Table Structure**:
  - HTML5 semantic tags (`<thead>`, `<tbody>`) for proper rendering
  - `page-break-inside: auto` on tables for splitting
  - `display: table-header-group` on thead for repetition
  - 1px borders for clear cell separation
  - Responsive to content length (no overflow issues)

- **Translations**:
  - 31 new translation keys for reports (el.json, en.json)
  - Keys: report_title, report_date_range, hide_prices, report_notes, etc.
  - Complete Greek and English support

#### 2. DD/MM/YYYY Date Format System
- **Automatic Date Input Conversion**:
  - JavaScript `date-formatter.js` (252 lines, v2.0)
  - Converts all `input[type="date"]` to text fields with dd/mm/yyyy format
  - Applies to ALL forms system-wide (tasks, projects, reports, customers, etc.)

- **Input Mask & Validation**:
  - Auto-formatting while typing (15102024 → 15/10/2024)
  - Automatic "/" insertion at positions 2 and 5
  - Pattern validation: `\d{2}/\d{2}/\d{4}`
  - Custom validity messages in Greek
  - Placeholder: "ηη/μμ/εεεε"

- **Custom Calendar Picker**:
  - Greek month names (Ιανουάριος, Φεβρουάριος, etc.)
  - Greek day abbreviations (Κυ, Δε, Τρ, Τε, Πε, Πα, Σα)
  - Calendar button with Font Awesome icon
  - Modal overlay with month navigation
  - "Σήμερα" and "Άκυρο" buttons
  - Highlights current day and selected date
  - Click anywhere on overlay to close

- **Backend Integration**:
  - Form submission intercept before send
  - Converts dd/mm/yyyy → yyyy-mm-dd for MySQL
  - Creates hidden inputs with converted values
  - Removes name attribute from visible inputs to avoid duplicates
  - Seamless database compatibility

- **Bootstrap Integration**:
  - Wraps inputs in `.input-group` class
  - Calendar button with `.btn.btn-outline-secondary`
  - Maintains Bootstrap form styling
  - Responsive design (grid system for calendar)

#### 3. Automated Database Migration System
- **Silent Auto-Migration on Login**:
  - Runs in `index.php` after database connection test
  - Checks for pending migrations on every page load
  - Non-blocking execution (logs errors but doesn't break app)
  - `AutoMigration` class handles detection and execution

- **Migration Detection**:
  - Scans `migrations/` directory for `.sql` files
  - Compares with `migrations` table to find pending
  - Checks file modification time for changes
  - Skips already-executed migrations

- **Safe Execution**:
  - Transaction-wrapped for rollback on error
  - Try-catch blocks for exception handling
  - Error logging to PHP error_log
  - Continues app execution even if migration fails

- **Migration Table Tracking**:
  - Records filename, execution time, status
  - Prevents duplicate execution
  - Maintains migration history
  - Easy rollback identification

- **Developer-Friendly**:
  - Drop `.sql` files in `migrations/` folder
  - Automatic execution on next page load
  - No manual intervention required
  - Full error details in logs

### 🔧 Technical Improvements

#### PDF Generation (TCPDF)
- Custom `CustomPDF` class extends `TCPDF`
- Override `Footer()` method for company details
- UTF-8 encoding for Greek characters
- HTML/CSS styling within PDF content
- Image embedding (company logo)

#### Date Handling
- Client-side: JavaScript Date object parsing
- Server-side: PHP DateTime conversions
- MySQL: DATE type (yyyy-mm-dd format)
- Display: dd/mm/yyyy via JavaScript formatter

#### Auto-Migration Architecture
- Class: `classes/AutoMigration.php`
- Trigger: `index.php` after DB connection
- Storage: `migrations` table (filename, executed_at, status)
- Files: `migrations/*.sql`

### 🐛 Bug Fixes
- Fixed table headers hidden behind blue lines in PDFs
- Fixed table splitting without headers on new pages
- Fixed unequal summary card heights (added fixed 80px height)
- Fixed column alignment issues (optimized widths)
- Fixed blue border overflow on notes section

### 📝 Documentation Updates
- README.md updated to v1.3.0
- CHANGELOG.md with complete feature documentation
- Inline code comments for PDF generation logic
- Migration system documentation

### 🎨 UI/UX Enhancements
- Report modal with clear sections (dates, prices, notes)
- Clean PDF styling with minimal decorative elements
- Responsive summary cards (33.33% width each)
- Professional color scheme (blue accents on white)

---

## [1.2.5] - 2025-10-17

### 🎯 Production-Ready Features

#### 1. Automated Database Update System
- **Update Detection**: Automatically checks if database needs updates
  - Compares VERSION file with migrations table
  - Shows current app version vs database version
  - Lists all pending updates with details
  
- **One-Click Updates**: Apply all updates from Settings page
  - Professional UI with update cards
  - Progress modal with real-time tracking
  - Transaction-safe with automatic rollback on error
  - Admin-only access with authentication
  
- **Update Management**:
  - View list of pending updates
  - See migration files and scripts to be run
  - Detailed success/error messages
  - Update history tracking in migrations table
  
- **Developer-Friendly**:
  - Easy to add new updates in UpdateController
  - Support for SQL migrations
  - Support for PHP post-migration scripts
  - Comprehensive error handling
  - Full documentation included

#### 2. CSV Export/Import/Demo for Materials Catalog
- **Export CSV**: Bulk export all materials to Excel-ready CSV format
  - UTF-8 BOM encoding for proper Greek character display in Excel
  - Greek column headers (Όνομα, Περιγραφή, Κατηγορία, Μονάδα, Τιμή, etc.)
  - Filename format: `materials_export_YYYY-MM-DD_HHmmss.csv`
  - Exports all fields: ID, name, description, category, unit, price, stock, supplier, supplier code, notes, status
  
- **Import CSV**: Bulk import materials from spreadsheet
  - Client-side CSV parsing with proper handling of quoted fields
  - Server-side validation and error collection
  - Category matching during import (finds existing or skips)
  - Line-by-line error reporting with specific messages
  - Confirmation dialog showing import count before processing
  - JSON-based transport for clean data handling
  
- **Demo Template CSV**: Downloadable template with 5 sample materials
  - Pre-filled with realistic electrical materials examples
  - Shows correct format for all columns
  - Ready to edit in Excel and re-import
  - UTF-8 BOM for Excel compatibility

#### 2. Enhanced Materials Catalog

- **Pagination System**: 
  - Default 25 items per page (optimal for page load)
  - Adjustable per-page selector: 10, 25, 50, 100 items
  - Bootstrap pagination with prev/next navigation
  - Maintains filters across page changes
  - Shows current page and total pages
  
- **Unit Measurement Dropdown**: 
  - Replaced free-text input with standardized dropdown
  - 18 predefined units: τεμ, μ, μ², μ³, κιλά, λίτρα, κιβώτιο, σετ, κουτί, ρολό, παλέτα, τόνος, πακέτο, δέσμη, φύλλο, τρέχον μέτρο, ώρα, ημέρα
  - Prevents inconsistent unit names (τεμ vs τεμάχια vs τεμ.)
  - Improves data quality and reporting
  
- **100 Electrical Materials Pre-loaded**:
  - Comprehensive catalog of real-world electrical materials
  - Materials 1-50: Cables (NYM, NYA, LIYY), conduits (CONDUR, FLEX), switches, outlets, breakers, junction boxes, LEDs, grounding equipment, WAGO connectors, cable ties
  - Materials 51-100: Cable ducts, relays, timers, PIR sensors, LED power supplies, LED strips, adapters, smoke detectors, batteries, extension cords, DIN rails, heat-shrink tubing
  - All with proper Greek names, descriptions, categories, units, realistic prices
  - Ready for immediate production use
  
- **Auto-Generated Aliases for All Materials**:
  - Batch regeneration script processes all 100 materials
  - Generates Greeklish aliases (καλώδιο → kalodio)
  - Generates English synonyms (cable, wire, cord)
  - Extracts product codes (NYM, LED, IP65)
  - Enables comprehensive search: type "kalodio", "cable", or "nym" to find "Καλώδιο NYM"
  - 100% success rate (100/100 materials updated)

#### 3. UI/UX Improvements

- **4-Column Labor Layout**: 
  - Changed project labor tab from 3 to 4 columns (col-lg-3)
  - Better space utilization on large screens
  - Maintains responsive design (2 columns on medium, 1 on small)
  - Improved readability of labor cards
  
- **CSV Operations Button Group**:
  - Three action buttons in header: Export CSV, Import CSV, Demo CSV
  - Bootstrap btn-group styling for visual cohesion
  - Hidden file input for clean import trigger
  - Icons and clear labels for each action

#### 4. Internationalization

- **English Translations Added**:
  - Unit measurements: piece, meter, square meter, cubic meter, kilograms, liters, box, set, case, roll, pallet, ton, package, bundle, sheet, linear meter, hour, day
  - Date formats: dd/mm/yyyy format strings
  - Month names: January through December
  - Day names: Monday through Sunday
  - All new features have dual language support

### 🗄️ Database Changes

**Data Additions:**
- 100 electrical materials loaded via SQL migrations
  - `load_electrical_materials.sql` (Materials 1-50)
  - `load_electrical_materials_part2.sql` (Materials 51-100)
- All materials have auto-generated aliases in `aliases` column

**Schema Enhancements:**
- No new tables (uses existing `materials_catalog` from v1.2.0)
- Enhanced data quality with standardized units
- Full-text search optimization via aliases column

### 🚀 Technical Improvements

#### CSV Processing
- **Client-Side**:
  - `parseCSVLine()`: Handles quoted fields with embedded commas/newlines
  - `parseAndImportCSV()`: Splits into rows, maps headers, validates
  - File API + FileReader for file handling
  - Blob API for demo CSV generation and download
  
- **Server-Side**:
  - `exportCSV()`: Uses fputcsv() for proper escaping
  - `importCSV()`: JSON input, header mapping (Greek→English), validation
  - Category matching: Finds existing categories or skips gracefully
  - Error collection: Continues processing, returns all errors at end
  - HTTP headers: Proper Content-Type and Content-Disposition for downloads

#### Pagination Logic
- SQL LIMIT/OFFSET for efficient queries
- Separate count query for total pages calculation
- URL parameter persistence (page, perPage, search, category)
- Frontend: Bootstrap pagination component
- Backend: MaterialCatalog::getAll() and getCount() methods

#### Alias Generation
- `MaterialAliasGenerator::generate()`: Core generation logic
- Batch processing script: `regenerate_material_aliases.php`
- Processes existing materials without aliases
- Updates database in single transaction
- Progress reporting (X/Y materials updated)

### 🎨 UI/UX Polish

- **Materials Index Page**:
  - Added CSV button group to header
  - Enhanced pagination with per-page selector
  - Maintains clean, professional layout
  
- **Materials Forms** (Create/Edit):
  - Replaced unit text input with select dropdown
  - 18 options with user-friendly Greek names
  - Consistent form validation
  
- **Project Show Page**:
  - Labor tab now uses 4-column grid
  - Better utilization of screen space
  - Improved card readability

### 📝 Code Quality

- **New Controller Methods**:
  - `MaterialsController::exportCSV()` - CSV generation with proper headers
  - `MaterialsController::importCSV()` - JSON-based import with validation
  
- **New JavaScript Functions**:
  - `exportMaterialsCSV()` - Triggers export by adding query parameter
  - `downloadDemoCSV()` - Generates and downloads template
  - `handleImportCSV()` - File input change handler
  - `parseAndImportCSV()` - CSV parsing and server communication
  - `parseCSVLine()` - Robust CSV field parser
  
- **Routing Enhancements**:
  - `/materials?export=csv` - Triggers CSV export
  - `POST /materials/import` - Accepts CSV import data
  
- **Migration Scripts**:
  - Two SQL files for 100 materials
  - One PHP script for alias regeneration
  - All documented and version-controlled

### 🐛 Bug Fixes

- Fixed pagination not showing on initial materials load
- Fixed unit field accepting inconsistent values
- Fixed search not working for newly added materials (alias generation)
- Fixed labor tab spacing issues on large screens
- Fixed CSV export including deleted/inactive materials

### 📦 Deployment Notes

- **Files Modified**: 
  - `index.php` (routing)
  - `controllers/MaterialsController.php` (export/import methods)
  - `views/materials/index.php` (CSV UI and JavaScript)
  - `views/materials/form.php`, `create.php`, `edit.php` (unit dropdown)
  - `views/projects/show.php` (4-column labor layout)
  - `languages/en.json`, `languages/el.json` (translations)
  - `models/MaterialCatalog.php` (pagination support)
  
- **Migration Files**:
  - `migrations/load_electrical_materials.sql`
  - `migrations/load_electrical_materials_part2.sql`
  - `migrations/regenerate_material_aliases.php`
  
- **Dependencies**: No new external dependencies
- **Backward Compatibility**: Fully compatible with v1.2.0 database
- **Upgrade Path**: Copy files, run alias regeneration script (optional if keeping existing materials)

### 🎯 Production Readiness

This release focuses on making the Materials Catalog production-ready:
- ✅ Bulk operations (export/import) for efficient data management
- ✅ 100 real electrical materials ready to use
- ✅ Standardized units for consistency
- ✅ Comprehensive search via auto-aliases
- ✅ Pagination for performance
- ✅ Professional CSV handling with Excel compatibility
- ✅ Full internationalization support

Perfect for deployment to production environments handling real electrical/plumbing projects.

---

## [1.2.0] - 2025-10-16

### 🎉 Major Features

#### 1. Project Photos System
- **Photo Upload**: Upload multiple photos per project with drag & drop support
- **Smart Storage**: Organized by project (uploads/projects/{project_id}/)
- **Photo Gallery**: Beautiful grid layout with lightbox viewer (Lightbox2)
- **Photo Management**: View, delete photos with confirmation dialogs
- **Database Schema**: New `project_photos` table with proper indexing
- **Automatic Cleanup**: Deletes photos when project is deleted

#### 2. Materials Catalog with Intelligent Search
- **Full CRUD**: Complete materials catalog with categories
- **Smart Autocomplete**: Real-time search with keyboard navigation
- **Greeklish Support**: Search "kalodio" finds "Καλώδιο"
- **Synonym Matching**: Search "cable" finds "Καλώδιο"
- **Code Extraction**: Search "nym" finds "Καλώδιο NYM 3x1.5"
- **Auto-Aliases**: Automatic generation of search keywords
- **Manual Override**: Edit aliases manually in material form
- **Accent Handling**: Properly converts τονισμένα (ά→a, έ→e, etc.)
- **Clean Greeklish**: No mixing Greek and Latin letters in same word

#### 3. Enhanced Project Tasks System
- **Task Materials**: Link materials from catalog to tasks
- **Labor Tracking**: Add labor rows with hours and rates
- **Material Autocomplete**: Smart dropdown for material selection
- **Auto-Fill**: Unit and price auto-populate from catalog
- **Cost Calculations**: Real-time cost calculations for materials and labor

#### 4. Automatic Migration System
- **Auto-Migrations**: All database changes applied automatically on install
- **Migration Tracking**: Prevents duplicate migrations
- **Smart Install**: New installations include all migrations
- **Version Control**: Track which migrations have been applied

### 🗄️ Database Changes

**New Tables:**
- `project_photos` - Photo storage and metadata
  - Fields: id, project_id (FK), filename, file_path, file_size, uploaded_by, uploaded_at
  - Indexes on project_id for fast lookups
  
- `material_categories` - Category management
  - Fields: id, name (UNIQUE), description, timestamps
  - 6 default categories included
  
- `materials_catalog` - Master materials database
  - Fields: id, category_id, name, description, unit, default_price, supplier, notes, aliases, is_active
  - **NEW**: `aliases` TEXT field for search optimization
  - FULLTEXT index on (name, aliases) for fast searching

**Enhanced Tables:**
- `task_materials` - Now linked to catalog
  - Added: catalog_material_id, name, unit fields
- `task_labor` - Labor tracking
  - Fields: task_id, description, hours, rate, cost

### 🚀 Major Technical Improvements

#### Smart Search Algorithm
- **Word-Level Processing**: Each word processed separately (no mixed Greek-Latin)
- **Priority Ordering**: Exact match → Starts with → In aliases → Contains
- **Case-Insensitive**: Works with any capitalization
- **Accent-Aware**: Handles all Greek diacritics properly

#### Auto-Generation Intelligence
- **Greeklish Conversion**: Full mapping including diphthongs (ου→ou, αι→ai)
- **Synonym Dictionary**: 15+ common materials with English translations
- **Code Extraction**: Regex-based extraction of product codes (NYM, PVC, 3x1.5)
- **Duplicate Removal**: Smart filtering of redundant aliases

#### Installation Enhancements
- **One-Click Setup**: Single install.php handles everything
- **Auto-Migration**: All SQL files in /migrations executed automatically
- **Error Resilience**: Skips existing tables/columns gracefully
- **Progress Feedback**: Shows count of executed statements

### 🎨 UI/UX Improvements
- **Photo Lightbox**: Click any project photo for full-screen view
- **Drag & Drop**: Modern file upload interface
- **Live Preview**: Alias generation preview as you type
- **Keyboard Navigation**: Full keyboard support in autocomplete
- **Visual Feedback**: Loading states, success/error messages
- **Mobile Responsive**: All new features work on mobile

### 🔧 API Enhancements
- `GET /api/materials/search` - Material search with aliases
- Returns: name, category, unit, price, aliases

### 📝 Code Quality
- **MaterialAliasGenerator**: Standalone utility class
- **MaterialsController**: RESTful controller pattern
- **BaseModel Enhancements**: execute(), lastInsertId(), rowCount() methods
- **Clean Separation**: Materials vs Materials-Inventory (old system preserved)

### 🐛 Bug Fixes
- Fixed routing conflict between old and new materials systems
- Fixed autocomplete not initializing (XAMPP sync issues)
- Fixed form action URLs for material edit
- Fixed project-tasks.js integration
- Fixed Greek letter mixing with Latin in Greeklish conversion
- Fixed accented vowels (ά, έ, ή, ί, ό, ύ, ώ) not converting properly

### 🔒 Security
- All file uploads validated by type and size
- SQL injection protection via prepared statements
- XSS protection with htmlspecialchars()
- Authentication required for all API endpoints

---

## [1.1.0] - 2025-10-15 (Previous Release)

### 🎉 Major Feature: Materials Catalog System with Autocomplete

#### ✨ New Features

**Materials Catalog Management**
- Created comprehensive materials catalog system with category organization
- Material management with full CRUD operations (Create, Read, Update, Delete)
- Category management with nested material counts and deletion protection
- Statistics dashboard showing total materials, active materials, and categories
- Advanced filtering by category, search query, and active/inactive status
- Smart soft delete: Materials used in tasks become inactive instead of being deleted
- Default categories: Ηλεκτρολογικά, Υδραυλικά, Οικοδομικά, Χρώματα & Βερνίκια, Μηχανολογικά, Άλλα

**Intelligent Autocomplete in Tasks**
- Real-time autocomplete for material selection when creating/editing tasks
- Dropdown displays: Material name, category badge, unit of measurement, default price
- Full keyboard navigation support (↑↓ arrows for navigation, Enter to select, Escape to close)
- Auto-populate: When material selected, automatically fills unit and price fields
- Debounced API calls (300ms delay) to optimize performance and reduce server load
- Minimum query length: 2 characters before search begins
- Visual feedback with category color-coded badges and Font Awesome icons
- Click outside to close, focus management for smooth UX

**RESTful API Endpoint**
- `GET /api/materials/search?q={query}&limit={limit}` - Search materials catalog
- Returns JSON with material details for autocomplete consumption
- Authentication required, returns 401 for unauthorized access

#### 🗄️ Database Changes

**New Tables:**
- `material_categories` - Central category management
  - Fields: id, name (UNIQUE), description, created_at, updated_at
  - 6 default categories auto-inserted during migration
  
- `materials_catalog` - Master materials catalog
  - Fields: id, category_id (FK), name, description, unit, default_price, supplier, notes, is_active, created_at, updated_at
  - Indexes on category_id and is_active for performance
  - Foreign key constraint with ON DELETE RESTRICT to prevent orphaned materials

**Enhanced Tables:**
- `task_materials` - Linked to catalog for data consistency
  - Added: `catalog_material_id` INT NULL (FK to materials_catalog.id)
  - Added: `name` VARCHAR(255) NOT NULL - Material name
  - Added: `unit` VARCHAR(50) NULL - Flexible unit of measurement
  - Retained: `description` field for backward compatibility with existing tasks
  - Index on catalog_material_id for JOIN performance

#### 📁 New Files Created

**Backend Models:**
- `models/MaterialCategory.php` (95 lines) - Category CRUD with material counting
- `models/MaterialCatalog.php` (190 lines) - Material CRUD, search, statistics

**Controllers:**
- `controllers/MaterialsController.php` (350+ lines)
  - Material CRUD: index(), add(), store(), edit(), update(), delete()
  - Category CRUD: categories(), addCategory(), updateCategory(), deleteCategory()
  - API: search() endpoint returning JSON for autocomplete

**Frontend Views:**
- `views/materials/index.php` - Materials listing with filters, statistics, and actions
- `views/materials/form.php` (260+ lines) - Add/edit material form with validation and help text
- `views/materials/categories.php` (245+ lines) - Category management interface with Bootstrap modals

**JavaScript:**
- `public/js/material-autocomplete.js` (300+ lines)
  - Fetch API integration with error handling
  - Dynamic dropdown rendering with category badges
  - Keyboard navigation (ArrowUp, ArrowDown, Enter, Escape)
  - Auto-population of unit and price fields on selection
  - Debouncing (300ms) to prevent excessive API calls
  - XSS prevention with HTML escaping

**Database Migrations:**
- `database/migrations/add_materials_system.sql` - Creates catalog tables and default categories
- `database/migrations/add_name_unit_to_task_materials.sql` - Enhances task_materials table

**Documentation:**
- `TESTING_GUIDE.md` - Comprehensive testing scenarios with 10 test cases and success criteria

#### 🔧 Modified Files

**Models:**
- `models/TaskMaterial.php`
  - Enhanced `create()` method to support name, unit, catalog_material_id
  - Enhanced `update()` method with same fields
  - Fully backward compatible: Legacy tasks using "description" still work

**Views:**
- `views/projects/tasks/add.php`
  - Added HTML5 datalist for common Greek unit types (τεμάχια, μέτρα, κιλά, etc.)
  - Included material-autocomplete.js script
  - Changed material input from "description" to "name" with autocomplete attributes
  - Added hidden catalog_material_id field for linking
  
- `views/projects/tasks/edit.php`
  - Applied same changes as add.php for consistency

**JavaScript:**
- `public/js/project-tasks.js`
  - Refactored `addMaterialRow()` function to support new structure
  - Changed from "Περιγραφή Υλικού" to "Ονομασία Υλικού" with autocomplete hint
  - Replaced unit_type dropdown with flexible text input + datalist
  - Initialize autocomplete for each material row dynamically
  - Store and pass catalog_material_id in hidden field

**Routing:**
- `index.php`
  - Added comprehensive materials routing section
  - Material routes: /materials, /materials/add, /materials/{id}/edit, /materials/{id}/delete
  - Category routes: /materials/categories, /materials/categories/add, /materials/categories/{id}/update, /materials/categories/{id}/delete
  - API route: /api/materials/search with JSON response

#### 🎨 UI/UX Enhancements

- Bootstrap 5.3 modals for category add/edit/delete with smooth animations
- Color-coded category badges (bg-secondary) for visual categorization
- Icon-rich interface using Font Awesome (fa-boxes, fa-tags, fa-check-circle, etc.)
- Responsive statistics cards with large numbers and icons
- Card-based layouts for clean, modern appearance
- Inline action buttons with icon-only design for compact tables
- Professional autocomplete dropdown with:
  - Hover effects for better visual feedback
  - Category badges inline with material names
  - Price and unit information in muted text
  - Smooth transitions and shadows
- Help cards with usage instructions for admins
- Toggle switches for is_active status (green for active, gray for inactive)

#### 🔒 Security Measures

- Authentication checks on all routes (redirect to login if not authenticated)
- CSRF token validation on all POST forms
- SQL injection prevention via PDO prepared statements
- XSS prevention via htmlspecialchars() on all user input display
- Input validation and sanitization on both client and server side
- JSON responses set proper Content-Type headers
- Foreign key constraints ensure referential integrity

#### 📊 Sample Test Data

8 real-world materials inserted across 4 categories:
- **Ηλεκτρολογικά**: Καλώδιο NYM 3x1.5 (1.20€/μέτρα), Καλώδιο NYM 3x2.5 (1.80€/μέτρα), Πρίζα Σούκο (2.50€/τεμάχια)
- **Υδραυλικά**: Σωλήνας PVC Φ32 (3.20€/μέτρα), Κολάρο Φ32 (0.80€/τεμάχια)
- **Οικοδομικά**: Τσιμέντο 25kg (5.50€/τεμάχια), Άμμος (25.00€/κ.μ.)
- **Χρώματα & Βερνίκια**: Χρώμα Πλαστικό Λευκό (8.50€/λίτρα)

#### 🧪 Testing Results

All 10 comprehensive test scenarios passed successfully:
1. ✅ View materials catalog with filters and statistics
2. ✅ Manage categories (add, edit, delete with protection)
3. ✅ Add new material to catalog
4. ✅ **Autocomplete in tasks** (main feature - full workflow tested)
5. ✅ Verify catalog_material_id correctly saved in database
6. ✅ Edit existing task with materials preserved
7. ✅ Edit catalog material and verify autocomplete updates
8. ✅ Delete used material (correctly soft-deletes to inactive)
9. ✅ Keyboard navigation in autocomplete dropdown
10. ✅ Performance & edge cases (debounce, empty results, short queries)

#### 🚀 Performance Optimizations

- Debounced autocomplete with 300ms delay prevents excessive API calls
- Database indexes on foreign keys for fast JOIN operations
- Lazy loading of autocomplete results (only on user input)
- Maximum 10 results per search to keep dropdown manageable
- Efficient SQL queries using LEFT JOINs and proper WHERE clauses
- Client-side caching of selected materials to reduce redundant calls

#### 📝 Technical Implementation Notes

- **Minimum autocomplete query:** 2 characters (prevents too broad searches)
- **Default price behavior:** Suggested from catalog but always overridable by user
- **Soft delete logic:** Materials used in tasks set to is_active=0 instead of DELETE
- **Category protection:** Categories containing materials cannot be deleted
- **Backward compatibility:** Old task_materials records with only "description" field continue to work
- **Unit flexibility:** Changed from ENUM to VARCHAR to support any Greek unit type
- **Data migration:** Existing task_materials.description copied to new name field

#### 🔄 Workflow Integration

**New Material → Task Workflow:**
1. Admin creates category via /materials/categories
2. Admin adds material with unit and default price via /materials/add
3. User creates new task and starts typing material name
4. Autocomplete suggests materials from catalog
5. User selects material → unit and price auto-populate
6. catalog_material_id links task to catalog entry
7. Future catalog updates (e.g., price changes) immediately reflect in autocomplete

**Edit/Delete Protection:**
- Editing catalog material: Updates appear in autocomplete immediately
- Deleting unused material: Permanently removes from database
- Deleting used material: Sets is_active=0, preserves existing task links
- Deleting category with materials: Blocked with user-friendly error message

---

## [1.1.0] - 2025-01-15

### ✨ New Features
- **Photo Gallery System**: Complete photo management system for project tasks
  - Upload multiple photos with drag-and-drop interface
  - 5 photo types: Before, After, During Work, Issue/Damage, Other
  - Automatic image resizing (max 1920px) with aspect ratio preservation
  - Photo captions and metadata (uploader, upload date)
  - Lightbox integration for full-screen viewing
  - Mobile camera support for direct photo capture
  - Organized storage by year/month in `uploads/task_photos/`
  - Statistics dashboard showing photo counts by type
  - Color-coded photo type sections with custom icons
  - Responsive grid layout adapting to all screen sizes

### 🗄️ Database Changes
- **New Table**: `task_photos` with fields:
  - `id`, `task_id`, `filename`, `original_filename`, `file_path`
  - `file_size`, `mime_type`, `photo_type` (enum)
  - `caption`, `sort_order`, `uploaded_by`, `created_at`
  - Foreign keys with CASCADE delete to `project_tasks` and `users`
  - Indexes on `task_id`, `photo_type`, `uploaded_by`, `created_at`

### 📝 Files Added (3 files)
1. `models/TaskPhoto.php` - Complete photo management model with image processing
2. `views/projects/tasks/photos.php` - Photo gallery interface (570 lines)
3. `migrations/add_task_photos.sql` - Database migration script

### 📝 Files Modified (4 files)
1. `controllers/ProjectTasksController.php` - Added 4 photo methods (photos, uploadPhoto, deletePhoto, updatePhotoDetails)
2. `index.php` - Added 4 photo routes
3. `views/projects/tasks/view.php` - Added "Φωτογραφίες" button
4. `languages/el.json` + `languages/en.json` - Added task_photos translations (20+ keys)

### 🛠️ Technical Details
- Image formats supported: JPEG, PNG, GIF, WebP
- Maximum file size: 10MB per photo
- Automatic transparency preservation for PNG/GIF
- Thumbnail generation capability (square crop)
- Lightbox2 v2.11.3 library integration

## [1.0.6] - 2025-10-13

### 🐛 Bug Fixes
- **Delete Buttons**: Fixed delete functionality across all pages (projects, invoices, appointments, materials, users, customers)
  - Root cause: Global `confirmDelete()` function in footer.php conflicting with page-specific functions
  - Solution: Renamed global function to `confirmAction()` to avoid naming conflicts
- **Form Actions**: Fixed form action URLs from relative `?route=` to absolute `index.php?route=` for proper POST submission
- **CSRF Tokens**: Added missing CSRF token to projects delete form
- **Dashboard Links**: Fixed customer activity links (changed `/customers/view` to `/customers/show`)
- **Dashboard Buttons**: Fixed appointments and new appointment buttons to use proper routing
- **Translations**: Added missing translation keys:
  - `common.profile` → "Προφίλ" / "Profile"
  - `common.user` → "Χρήστης" / "User"
  - `common.notifications` → "Ειδοποιήσεις" / "Notifications"
  - `common.no_notifications` → "Δεν υπάρχουν ειδοποιήσεις" / "No notifications"

### ⚡ Improvements
- **URL Routing**: Standardized all delete forms to use `index.php?route=` pattern
- **Session Management**: Improved session handling in redirects to preserve flash messages
- **Code Consistency**: All view files now use consistent URL patterns

### 📝 Files Modified (17 files)
1. `views/includes/footer.php` - Renamed confirmDelete to confirmAction
2. `views/projects/index.php` - Added CSRF token, fixed form action
3. `views/invoices/index.php` - Fixed form action URL
4. `views/appointments/index.php` - Fixed form action URL
5. `views/materials/index.php` - Fixed form action URL
6. `views/users/index.php` - Fixed form action URL
7. `views/customers/index.php` - Fixed form action URL
8. `views/dashboard/index.php` - Fixed all dashboard links
9. `controllers/DashboardController.php` - Fixed customer activity link
10. `controllers/ProjectController.php` - Previous redirect fixes
11. `controllers/CustomerController.php` - Previous redirect fixes
12. `classes/BaseController.php` - Session fix
13. `index.php` - Route fixes
14. `models/Project.php` - Removed is_active bug
15. `models/Customer.php` - Previous fixes
16. `languages/el.json` - Added CSV and common translations
17. `languages/en.json` - Added CSV and common translations

---

## [1.0.5] - 2025-10-10

### 🆕 New Features
- **Projects CSV Export**: Export all projects to CSV with 17 columns
- **Projects CSV Import**: Import projects from CSV with validation and duplicate detection
- **Projects Demo CSV**: Download sample CSV file with Greek data for testing
- **Customers CSV Export**: Export customers to CSV
- **Customers CSV Import**: Import customers from CSV
- **Customers Demo CSV**: Download sample CSV file for customers

### 🐛 Bug Fixes
- Fixed project `is_active` column error (removed non-existent column reference)
- Fixed project save/update 404 redirect issues
- Fixed customer redirect URLs from path parameters to query parameters

### ⚡ Improvements
- Added UTF-8 BOM support for Greek characters in CSV files
- Improved CSV import validation
- Better error handling for CSV operations
- Added success/error flash messages for CSV operations

### 🌍 Translations
- Added 8 new translation keys for projects CSV operations (Greek + English)
- Added 8 new translation keys for customers CSV operations (Greek + English)

---

## [1.0.4] - 2025-10-08

### 🐛 Bug Fixes
- Fixed login error messages not displaying
- Added `session_write_close()` in BaseController redirect method
- Improved session data persistence

---

## [1.0.3] - 2025-10-05

### 🆕 New Features
- Complete bilingual support (Greek/English)
- Language switcher in header
- 700+ translation keys

### ⚡ Improvements
- Improved UI/UX consistency
- Better responsive design
- Enhanced mobile experience

---

## [1.0.2] - 2025-10-01

### 🆕 New Features
- Materials inventory management
- Stock tracking
- Material categories

### 🐛 Bug Fixes
- Various UI fixes
- Database query optimizations

---

## [1.0.1] - 2025-09-25

### 🆕 New Features
- Quotes management
- Invoice generation
- PDF export functionality

### ⚡ Improvements
- Better dashboard statistics
- Improved date handling

---

## [1.0.0] - 2025-09-20

### 🎉 Initial Release

#### Core Features
- **Customer Management**: Support for both individuals and companies
- **Project Management**: Track projects with costs, dates, and status
- **Appointments**: Schedule and manage appointments with calendar view
- **User Management**: Admin and technician roles
- **Dashboard**: Overview of recent activities and statistics
- **Authentication**: Secure login system with CSRF protection
- **Responsive Design**: Works on desktop, tablet, and mobile devices

#### Technical Features
- PHP 7.4+ with PDO for database access
- MySQL/MariaDB database
- MVC architecture pattern
- Bootstrap 5 UI framework
- FontAwesome icons
- Session-based authentication
- CSRF token protection
- File upload support

---

## Legend

- 🎉 **Initial Release**
- 🆕 **New Features**
- ⚡ **Improvements**
- 🐛 **Bug Fixes**
- 🔒 **Security**
- 📝 **Documentation**
- 🌍 **Translations**
- 💥 **Breaking Changes**

---

**HandyCRM** © 2024-2025 Theodore Sfakianakis
