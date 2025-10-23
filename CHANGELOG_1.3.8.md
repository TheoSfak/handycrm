# Changelog

All notable changes to HandyCRM will be documented in this file.

## [1.3.8] - 2025-10-23

### Added
- **VAT Display Settings Feature**
  - New admin settings for VAT display configuration
  - `display_vat_notes` - Toggle to show/hide VAT notes on prices
  - `prices_include_vat` - Setting to indicate if prices include VAT
  - `default_vat_rate` - Default VAT rate (24%)
  - Live preview in settings page showing how prices will appear
  - Helper functions in config.php:
    - `formatCurrencyWithVAT()` - Format currency with VAT note
    - `withoutVatLabel()` - Add VAT note to form labels
    - `getVatNote()` - Get full VAT disclaimer text
  
- **Dashboard Enhancements**
  - Display count of completed/invoiced projects in revenue card
  - Better error handling for missing database tables (invoices, quotes)

### Fixed
- **Critical Project Cost Calculation Bug**
  - Projects now calculate total_cost from actual materials + labor instead of stale database value
  - Added `calculated_total_cost` subquery in Project::getPaginated()
  - Fixed projects index page to show correct costs
  - Dashboard revenue now calculates from real project costs

- **Database Schema Issues**
  - Fixed ProjectReportController to use `technician_id` instead of non-existent `user_id`
  - Added backward compatibility check for column names
  - Fixed DashboardController to use correct column names:
    - `completion_date` instead of `completed_at`
    - `invoiced_at` for invoiced projects

- **PDF Report Generation**
  - Fixed missing config.php include in ProjectReportController
  - All prices in PDF now show VAT notes using formatCurrencyWithVAT()
  - Fixed JOIN queries to work with current database schema

- **Settings Controller**
  - Added VAT settings to allowed settings list
  - Special handling for checkbox values (1/0)
  - Proper defaults for new settings

### Changed
- Projects list now shows calculated costs with VAT notes
- Dashboard active projects now includes 'invoiced' status
- Dashboard revenue calculation uses dynamic queries based on available tables
- All currency displays now respect VAT display settings

### Database Migrations
- `migrations/migrate_to_1.3.8.sql` - Adds VAT settings to settings table

### Modified Files
- `config/config.php` - Added VAT helper functions
- `controllers/SettingsController.php` - Added VAT settings support
- `controllers/ProjectController.php` - Uses calculated costs
- `controllers/ProjectReportController.php` - Fixed schema issues, added VAT support
- `controllers/DashboardController.php` - Fixed calculations and missing tables
- `models/Project.php` - Added calculated_total_cost subquery
- `views/settings/index.php` - Added VAT settings UI with live preview
- `views/projects/index.php` - Uses formatCurrencyWithVAT()
- `views/projects/show.php` - Fixed total cost calculation
- `views/dashboard/index.php` - Shows project count, uses formatCurrencyWithVAT()

---

## [1.3.7] - Previous Version
(Previous changelog entries...)
