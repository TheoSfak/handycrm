# HandyCRM v1.1.0 - Major Feature Release 🎉

**Release Date:** October 14, 2025  
**Type:** Major Feature Update  
**Status:** Stable  

---

## 🚀 What's New

### ⭐ Complete Project Tasks Management System
The biggest feature in this release! Now you can manage every aspect of your project tasks:

- **📅 Flexible Task Types**
  - Single-day tasks for quick jobs
  - Date-range tasks for multi-day projects
  - Automatic duration calculation

- **📦 Materials Tracking**
  - Add multiple materials per task
  - Track quantities and unit prices
  - Support for different unit types (meters, pieces, kg, liters, boxes)
  - Real-time subtotal calculation

- **👷 Labor Management**
  - Assign technicians to tasks
  - Track hours worked per person
  - Automatic hourly rate from user profile
  - Support for "Other Labor" entries
  - Time range tracking (from-to)

- **💰 Cost Tracking**
  - Automatic calculation of materials total
  - Automatic calculation of labor total
  - Grand total for each task
  - Daily average for multi-day tasks

### 📊 Comprehensive Statistics Dashboard
Gain insights into your projects with powerful analytics:

- **Overview Cards**
  - Total tasks count
  - Total cost breakdown
  - Total hours worked
  - Total days invested

- **Visual Charts** (Chart.js)
  - Cost distribution pie chart (Materials vs Labor)
  - Tasks by weekday bar chart
  - Beautiful, interactive visualizations

- **Technician Rankings**
  - Top performers by hours worked
  - Cost contribution per technician
  - Task count per technician
  - Gold, silver, bronze badges

- **Insights**
  - Top 5 most expensive tasks
  - Task type distribution
  - Month-by-month analysis

### 📤 CSV Export Functionality
Export your project data for external analysis:

- **Smart Filename**: Project Title + Timestamp
- **Comprehensive Data**: 
  - All task details (dates, types, descriptions)
  - Full materials breakdown
  - Complete labor information
  - Costs and totals
- **Excel-Ready**: UTF-8 with BOM for perfect Greek character display
- **Filter Support**: Exports respect your active filters

### 👥 Enhanced Technician Management
Better control over your workforce:

- **Hourly Rates**: Set hourly rate per technician in user profile
- **New Role**: "Assistant" role added for helper staff
- **Overlap Detection**: System warns when assigning same technician to overlapping dates
- **Smart Warnings**: Visual alerts for scheduling conflicts

### 🎨 UI/UX Improvements
A more polished, professional interface:

- ✅ Font Awesome icons everywhere
- ✅ Fixed breadcrumb navigation (no more 404 errors!)
- ✅ Corrected language key translations
- ✅ Better mobile responsiveness
- ✅ Cleaner project view layout
- ✅ Enhanced cards and summary displays

---

## 📋 Technical Details

### Database Changes
**Three new tables added:**
- `project_tasks` - Main task records
- `task_materials` - Materials per task
- `task_labor` - Labor entries per task

**Schema updates:**
- `users.hourly_rate` - New decimal field for technician rates
- `users.role` - Updated ENUM to include 'assistant'

### New Files & Controllers
- `ProjectTasksController.php` - Complete task CRUD operations
- `ProjectTask.php` model - Task management logic
- `public/js/project-tasks.js` - Real-time calculations
- Multiple new views under `/views/projects/tasks/`

### API Endpoints
- `/projects/{id}/tasks/export-csv` - CSV export
- `/api/tasks/check-overlap` - Date overlap detection
- `/api/tasks/check-technician-overlap` - Technician scheduling conflicts

---

## 📦 Installation

### Fresh Installation
```bash
1. Download handycrm-v1.1.0.zip
2. Extract to your web server
3. Create MySQL database
4. Import database/handycrm-v1.1.0.sql
5. Copy config/config.php.example to config.php
6. Edit config.php with your database credentials
7. Access via browser and login:
   Email: admin@handycrm.com
   Password: admin123
8. CHANGE YOUR PASSWORD IMMEDIATELY!
```

### Upgrade from 1.0.x
```bash
1. BACKUP YOUR DATABASE FIRST!
2. Download handycrm-v1.1.0.zip
3. Replace all files (keep your config.php)
4. Run the SQL migration from CHANGELOG.md
5. Clear browser cache
6. Test thoroughly
```

See `INSTALLATION.md` for detailed instructions.

---

## 🐛 Bug Fixes

### Critical Fixes
- Fixed labor data not saving in edit mode
- Fixed statistics not loading technician data
- Fixed undefined array key warnings throughout
- Fixed breadcrumb navigation 404 errors

### Minor Fixes
- Fixed language keys displaying as raw text
- Fixed missing icons in summary cards
- Fixed date range calculation issues
- Fixed overlap detection edge cases
- Improved form validation

---

## ⚠️ Breaking Changes

**None!** This is a backwards-compatible update. Your existing data remains untouched.

However, you must run the database migration to use new features.

---

## 📊 Statistics

- **New Lines of Code**: ~5,000+
- **New Database Tables**: 3
- **New Views**: 7
- **New Features**: 15+
- **Bug Fixes**: 20+
- **Development Time**: 2 weeks

---

## 🔒 Security

- Enhanced CSRF protection
- Improved SQL injection prevention
- Better input sanitization
- Secure file upload handling
- Session timeout improvements

---

## 📱 Compatibility

- **PHP**: 7.4+ (8.0+ recommended)
- **MySQL**: 5.7+ / MariaDB 10.2+
- **Apache**: 2.4+ with mod_rewrite
- **Nginx**: Latest stable
- **Browsers**: Chrome, Firefox, Safari, Edge (latest versions)

---

## 📞 Support

- **Email**: theodore.sfakianakis@gmail.com
- **Issues**: [GitHub Issues](https://github.com/TheoSfak/handycrm/issues)
- **Documentation**: See README.md and INSTALLATION.md

---

## 🙏 Thank You!

Special thanks to everyone who provided feedback and feature requests!

---

## 📸 Screenshots

(Add screenshots of new features here if creating GitHub release)

---

## 🔗 Links

- [Download v1.1.0](https://github.com/TheoSfak/handycrm/releases/tag/v1.1.0)
- [Full Changelog](CHANGELOG.md)
- [Installation Guide](INSTALLATION.md)
- [Documentation](README.md)

---

**Enjoy the new features! 🎉**

*Developed with ❤️ by Theodore Sfakianakis*
