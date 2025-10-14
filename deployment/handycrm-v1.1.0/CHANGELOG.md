# Changelog

All notable changes to HandyCRM will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-10-14

### Added
- **Complete Project Tasks Management System**
  - Single-day and date-range task types
  - Materials tracking with quantities, unit prices, and automatic subtotal calculation
  - Labor tracking with technicians, hours worked, and hourly rates
  - Real-time cost calculations in forms
  - Task duplication functionality
  - Daily breakdown view for multi-day tasks
  
- **Comprehensive Statistics Dashboard**
  - Project-level statistics with charts (Chart.js integration)
  - Technician performance rankings
  - Cost breakdown visualization (materials vs labor)
  - Tasks distribution by weekday
  - Top 5 most expensive tasks
  - Total hours worked analytics
  
- **Technician Management Enhancements**
  - Hourly rate field in user profiles
  - Assistant role added to user roles
  - Technician overlap detection (same technician, same dates)
  - Warning system for scheduling conflicts
  
- **CSV Export Functionality**
  - Export all project tasks to CSV
  - Filename includes project title and export timestamp
  - Comprehensive data export (dates, materials details, labor details, costs)
  - UTF-8 encoding with BOM for Excel compatibility
  - Respects active filters when exporting
  
- **UI/UX Improvements**
  - Font Awesome icons throughout the interface
  - Improved breadcrumb navigation with correct links
  - Fixed language keys display (common.title, common.status, common.created)
  - Enhanced cards and summary displays
  - Better mobile responsiveness

### Changed
- **Database Schema Updates**
  - New table: `project_tasks` for task management
  - New table: `task_materials` for materials tracking
  - New table: `task_labor` for labor/technician work entries
  - Updated `users` table: added `hourly_rate` field
  - Updated `users` table: role ENUM now includes 'assistant'
  
- **Models Enhancements**
  - ProjectTask model with complete CRUD operations
  - Enhanced statistics calculation methods
  - Improved overlap detection algorithms
  - Better data relationships and foreign key handling
  
- **Controllers Improvements**
  - ProjectTasksController with full task management
  - API endpoints for overlap detection
  - CSV export functionality
  - Enhanced form validation
  
- **Views Refinements**
  - Cleaned up project view (removed unnecessary cards)
  - Added "Ιστορικό Εργασιών" summary card
  - Improved task list with icons and better formatting
  - Enhanced statistics view with charts and insights
  - Fixed breadcrumb links across all task views

### Fixed
- Fixed undefined array key warnings in project tasks
- Fixed labor data not saving in edit mode
- Fixed validation to ensure labor rows have required fields
- Fixed date range task warnings (undefined total_days, daily_average)
- Fixed breakdown view undefined key warnings
- Fixed technician statistics not loading (added explicit labor data loading)
- Fixed breadcrumb navigation returning 404 errors
- Fixed language keys showing as raw text instead of translations
- Fixed missing icons in summary cards
- Fixed icon compatibility issues (replaced fa-user-hard-hat with fa-users)

### Security
- Enhanced CSRF protection
- Improved input sanitization
- Better SQL injection prevention
- Session timeout handling
- Secure file upload validation

### Performance
- Optimized database queries
- Reduced redundant data loading
- Improved JavaScript calculations
- Better caching strategies

## [1.0.6] - Previous Version

### Features
- Customer management
- Project management
- User management
- Basic appointments
- Quotes and invoices
- Dashboard with overview
- Basic reporting

---

## Upgrade Guide from 1.0.x to 1.1.0

### Database Migration Required

**IMPORTANT**: This update includes significant database schema changes.

#### Option 1: Fresh Installation (Recommended for new deployments)
1. Backup your current database
2. Create a new database
3. Import `database/handycrm-v1.1.0.sql`
4. Manually transfer your existing data if needed

#### Option 2: Update Existing Database (For live systems with data)
Run these SQL commands on your existing database:

```sql
-- Add hourly_rate to users
ALTER TABLE `users` 
ADD COLUMN `hourly_rate` decimal(10,2) DEFAULT 0.00 COMMENT 'Hourly rate for labor costs' 
AFTER `password`;

-- Update users role ENUM
ALTER TABLE `users` 
MODIFY COLUMN `role` enum('admin','technician','assistant') NOT NULL DEFAULT 'technician';

-- Create project_tasks table
CREATE TABLE `project_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `task_type` enum('single_day','date_range') NOT NULL DEFAULT 'single_day',
  `task_date` date DEFAULT NULL COMMENT 'For single day tasks',
  `date_from` date DEFAULT NULL COMMENT 'For date range tasks',
  `date_to` date DEFAULT NULL COMMENT 'For date range tasks',
  `description` varchar(500) NOT NULL,
  `materials_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `labor_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `daily_total` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Materials + Labor',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `created_by` (`created_by`),
  KEY `task_type` (`task_type`),
  KEY `task_date` (`task_date`),
  KEY `date_from` (`date_from`),
  KEY `date_to` (`date_to`),
  CONSTRAINT `project_tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_tasks_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create task_materials table
CREATE TABLE `task_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_type` enum('meters','pieces','kg','liters','boxes','other') NOT NULL DEFAULT 'pieces',
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  CONSTRAINT `task_materials_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `project_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create task_labor table
CREATE TABLE `task_labor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `technician_id` int(11) DEFAULT NULL COMMENT 'NULL for other labor',
  `technician_name` varchar(100) DEFAULT NULL COMMENT 'Cached name or Other',
  `technician_role` enum('technician','assistant','other') DEFAULT 'other',
  `time_from` time DEFAULT NULL,
  `time_to` time DEFAULT NULL,
  `hours_worked` decimal(5,2) NOT NULL DEFAULT 0.00,
  `hourly_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `technician_id` (`technician_id`),
  CONSTRAINT `task_labor_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `project_tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_labor_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### File Updates Required
1. Replace all files in `/controllers/`, `/models/`, `/views/`
2. Update `/public/js/project-tasks.js`
3. Update `/index.php` with new routes
4. Update `/config/config.php.example` to `config.php` with version 1.1.0

### Configuration Changes
No breaking configuration changes. Your existing `config.php` will work.

### Post-Upgrade Steps
1. Clear browser cache
2. Test login functionality
3. Test creating a new task
4. Verify statistics display
5. Test CSV export
6. Check technician hourly rates in user profiles

---

## Support

For issues, questions, or feature requests:
- Email: theodore.sfakianakis@gmail.com
- GitHub: https://github.com/TheoSfak/handycrm/issues

---

**Developed with ❤️ by Theodore Sfakianakis**
