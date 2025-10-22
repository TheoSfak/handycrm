# HandyCRM v1.3.6 - Release Notes

## 🚀 Major Changes

### Invoices Module Removal
This release **removes the entire invoices module** from HandyCRM. Projects now handle invoicing directly through status changes.

**What changed:**
- ❌ Removed Invoices menu and all invoice-related pages
- ❌ Deleted invoice controllers, models, and views
- ✅ Projects now track invoicing via `status = 'invoiced'` and `invoiced_at` timestamp
- ✅ Migration script included to safely remove invoice tables from database

### Task-Based Cost Calculation
Projects no longer require manual cost entry. Costs are **automatically calculated from tasks**.

**How it works:**
- Material costs: Sum of all `task_materials.subtotal` for project tasks
- Labor costs: Sum of all `task_labor.subtotal` for project tasks
- Total cost: Calculated with VAT when project status changes to "Τιμολογημένο"
- Real-time display: Projects list shows calculated totals from task data

**Benefits:**
- 📊 More accurate costing based on actual work
- ⚡ No manual data entry needed
- 🔄 Automatic updates when tasks change
- 💰 Consistent billing across all projects

## 🐛 Bug Fixes

### UI & UX Improvements
- **Toast notifications** now persist permanently (removed auto-dismiss timer)
- **Cost display** fixed - properly converts database strings to numbers for comparison
- **Greek translations** - Added missing "Αρ. Προσφοράς" label

### Database & Performance
- **MySQL Aria recovery** - Improved handling of storage engine issues
- **Query optimization** - Switched to subquery approach for cost calculations to avoid GROUP BY conflicts
- **User permissions** - Better handling of MySQL privilege restoration

## 📊 Updated Modules

### Projects
- Removed manual cost input fields from create/edit forms
- Auto-calculation of costs when marked as "Τιμολογημένο"
- Enhanced list view with task-based cost display

### Customers
- `getInvoices()` → `getInvoicedProjects()`
- Shows projects with invoiced status instead of separate invoices

### Reports
- All revenue calculations now use `projects` table
- Query by `invoiced_at` date and `total_cost` fields
- Faster performance without invoice joins

## 🔧 Technical Details

### Database Schema Changes
**Tables to be dropped** (via migration):
- `invoices`
- `invoice_items`

**New cost calculation logic**:
```sql
SELECT 
    SUM(task_labor.subtotal) as labor_cost,
    SUM(task_materials.subtotal) as material_cost
FROM project_tasks
LEFT JOIN task_labor ON project_tasks.id = task_labor.task_id
LEFT JOIN task_materials ON project_tasks.id = task_materials.task_id
WHERE project_tasks.project_id = ?
```

### Migration Required
**Important:** After uploading files, run the migration script:
```
http://yourdomain.com/migrate_remove_invoices.php
```
This will safely drop the invoice tables. **Backup your database first!**

## 📦 Installation

### Fresh Installation
1. Upload `HandyCRM-v1.3.6.zip` to your server
2. Extract to your web directory
3. Visit `http://yourdomain.com/install.php`
4. Follow installation wizard

### Upgrade from Previous Version
1. **Backup your database** (important!)
2. Upload all files from the ZIP, overwriting existing ones
3. Keep your `config/config.php` file (don't overwrite)
4. Run migration: `http://yourdomain.com/migrate_remove_invoices.php`
5. Delete the migration script after successful run
6. Clear browser cache and reload

## ⚠️ Breaking Changes

### Invoices Removed
- **All invoice data will be deleted** when migration runs
- Export your invoice data before upgrading if you need historical records
- Reports now use project data instead of invoices

### Project Forms
- Manual cost input fields no longer available
- Costs must come from task labor and materials entries
- Update your workflow to enter costs via tasks

### API Changes
- `Invoice` model removed
- `InvoiceController` removed
- Customer `getInvoices()` → `getInvoicedProjects()`

## 🎯 What's Next

After installation:
1. ✅ Verify projects display costs correctly
2. ✅ Test marking projects as "Τιμολογημένο"
3. ✅ Check reports show revenue data
4. ✅ Review customer invoiced projects lists
5. 🗑️ Delete temporary migration script

## 📝 System Requirements

- PHP 7.4 or higher (8.x recommended)
- MySQL 5.7+ or MariaDB 10.2+
- Apache with mod_rewrite enabled
- Minimum 50MB disk space

## 🆘 Support

If you encounter issues:
1. Check the CHANGELOG.md for detailed changes
2. Review migration script output for errors
3. Verify MySQL user has proper permissions
4. Clear browser cache after upgrade

---

**Full Changelog:** https://github.com/TheoSfak/handycrm/blob/main/CHANGELOG.md
