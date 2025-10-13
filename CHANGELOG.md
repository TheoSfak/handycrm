# HandyCRM - Change Log

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
