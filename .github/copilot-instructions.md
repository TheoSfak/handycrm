# HandyCRM - AI Coding Instructions

## Architecture Overview

HandyCRM is a PHP MVC application for managing customers, projects, payments, and technicians. Greek/English bilingual.

### Core Structure
- **Entry Point**: [index.php](../index.php) - Handles routing via `?route=/path` query parameter
- **MVC Pattern**: `controllers/` → `models/` → `views/` with autoloading in index.php
- **Base Classes**: All controllers extend `BaseController`, models extend `BaseModel`
- **Database**: MySQL via PDO in `classes/Database.php`

### Key Components
| Directory | Purpose |
|-----------|---------|
| `classes/` | Core services (Router, Database, Permission, AuthMiddleware, LanguageManager) |
| `controllers/` | Request handlers extending `BaseController` |
| `models/` | Data models extending `BaseModel` (User, Project, Customer, etc.) |
| `views/` | PHP templates organized by feature (includes header.php/footer.php) |
| `languages/` | JSON translation files (el.json, en.json) |
| `migrations/` | SQL migration files with auto-execution system |

## Critical Patterns

### Permission System
```php
// In controllers - use BaseController methods
if (!$this->isAdmin() && !can('customers.view')) {
    $this->redirect('/dashboard?error=unauthorized');
}

// Global helper for views
<?php if (can('projects.edit')): ?>
```
- Roles: `admin`, `supervisor`, `technician`, `assistant`
- Permission format: `module.action` (e.g., `customers.view`, `projects.edit`)
- Admin bypasses all permission checks

### Translation System
```php
// Use __() helper everywhere for i18n
__('customers.title')      // Key from languages/el.json
__('menu.dashboard')       // Nested: {"menu": {"dashboard": "..."}}
```

### Controller Pattern
```php
class ExampleController extends BaseController {
    public function __construct() {
        parent::__construct();  // Required - sets up DB and auth
        $this->model = new Example();
    }
    
    public function index() {
        $data = ['title' => __('example.title') . ' - ' . APP_NAME];
        $this->view('example/index', $data);
    }
}
```

### Model Pattern
```php
class Example extends BaseModel {
    protected $table = 'examples';     // Required
    protected $primaryKey = 'id';      // Default
    
    // Inherited: find(), findAll(), create(), update(), delete(), count()
}
```

### View Rendering
```php
// In controller
$this->view('folder/template', ['var' => $value]);

// Views include header/footer automatically via:
require_once 'views/includes/header.php';
// ... content ...
require_once 'views/includes/footer.php';
```

## Routing

Routes defined in [index.php](../index.php) using simple switch-case pattern:
```php
$currentRoute = $_GET['route'] ?? '/';
// Pattern: $router->add('/path/{id}', 'Controller', 'method');
```
- GET params: `?route=/customers/show/5`
- Route params: `{id}` extracted and passed to controller method

## Database Migrations

- Auto-run on app load via `AutoMigration` class
- SQL files in `migrations/` tracked in `migrations` table
- Naming: `NNN_description.sql` (e.g., `007_create_payments_table.sql`)

## Configuration

Copy `config/config.example.php` to `config/config.php`:
- `DB_*` constants for database
- `APP_NAME`, `BASE_URL`, `DEBUG_MODE`
- `DEFAULT_LANGUAGE` (el/en)
- `CURRENCY_SYMBOL` (€)

## Conventions

- **Dates**: Greek format `d/m/Y` via `formatDate()` helper
- **Currency**: European format `1.234,56 €` via `formatCurrency()` helper  
- **Soft deletes**: Check `deleted_at IS NULL` in queries
- **CSRF**: Use `$this->generateCsrfToken()` and `$this->validateCsrfToken()`
- **Pagination**: `ITEMS_PER_PAGE` constant, use model's `getPaginated()` method

## External Dependencies

- **Bootstrap 5**: CSS framework (CDN)
- **Font Awesome 6**: Icons (CDN)
- **Chart.js**: Dashboard charts
- **PHPMailer**: Email sending (`vendor/phpmailer/`)
- **TCPDF/PHPWord/PHPSpreadsheet**: PDF/Word/Excel export (`vendor/`)
