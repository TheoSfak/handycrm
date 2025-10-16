# 🔧 HOTFIX: Materials & Labor Not Saving (v1.2.0)

## 📋 Issue Summary

**Problem**: When creating or editing project tasks, materials and labor entries were not being saved to the database.

**Affected Version**: HandyCRM v1.2.0 (initial release)

**Status**: ✅ FIXED in commit `1001b9e`

---

## 🐛 Root Cause

**Field Name Mismatch** between frontend form and backend controller:

| Component | Expected Field | Actual Field Sent |
|-----------|---------------|-------------------|
| Materials Name | `description` | `name` ✅ |
| Materials Unit | `unit_type` | `unit` ✅ |
| Materials Catalog ID | N/A | `catalog_material_id` ✅ (new) |

### Technical Details

**File**: `controllers/ProjectTasksController.php`
**Function**: `collectMaterials()` (line 186-204)

**Before** (Broken):
```php
private function collectMaterials() {
    $materials = [];
    
    if (!empty($_POST['materials'])) {
        foreach ($_POST['materials'] as $index => $material) {
            if (!empty($material['description'])) {  // ❌ Looking for 'description'
                $materials[] = [
                    'description' => trim($material['description']),
                    'unit_price' => floatval($material['unit_price'] ?? 0),
                    'quantity' => floatval($material['quantity'] ?? 0),
                    'unit_type' => $material['unit_type'] ?? 'pieces'  // ❌ Looking for 'unit_type'
                ];
            }
        }
    }
    
    return $materials;
}
```

**After** (Fixed):
```php
private function collectMaterials() {
    $materials = [];
    
    if (!empty($_POST['materials'])) {
        foreach ($_POST['materials'] as $index => $material) {
            // Check for 'name' field (new form) or 'description' field (old form)
            $materialName = trim($material['name'] ?? $material['description'] ?? '');
            
            if (!empty($materialName)) {
                $materials[] = [
                    'name' => $materialName,  // ✅ Accepts 'name' from form
                    'catalog_material_id' => !empty($material['catalog_material_id']) ? intval($material['catalog_material_id']) : null,
                    'unit' => trim($material['unit'] ?? ''),  // ✅ Accepts 'unit' from form
                    'unit_type' => trim($material['unit'] ?? $material['unit_type'] ?? 'other'),
                    'unit_price' => floatval($material['unit_price'] ?? 0),
                    'quantity' => floatval($material['quantity'] ?? 0)
                ];
            }
        }
    }
    
    return $materials;
}
```

---

## 🎯 What Was Fixed

1. ✅ **Materials now save** - Controller accepts `name` field from form
2. ✅ **Unit field now saves** - Controller accepts `unit` field from form
3. ✅ **Catalog integration** - Added support for `catalog_material_id` from autocomplete
4. ✅ **Backward compatible** - Still works with old `description` and `unit_type` fields
5. ✅ **Labor hours/costs** - Already working (no changes needed)

---

## 📦 Deployment Instructions

### Option 1: Re-upload from GitHub (Recommended)

1. **Download latest v1.2.0**:
   ```bash
   wget https://github.com/TheoSfak/handycrm/archive/refs/tags/v1.2.0.zip
   unzip v1.2.0.zip
   ```

2. **Upload ONLY this file to production**:
   ```
   Source: handycrm-1.2.0/controllers/ProjectTasksController.php
   Destination: /public_html/controllers/ProjectTasksController.php
   ```

3. **Test immediately**:
   - Create new task with materials
   - Verify materials appear in task details
   - Check database: `SELECT * FROM task_materials ORDER BY id DESC LIMIT 5;`

### Option 2: Use Hotfix Script

1. **Upload hotfix script**:
   ```
   Source: HOTFIX_v1.2.0_materials_labor.php
   Destination: /public_html/HOTFIX_v1.2.0_materials_labor.php
   ```

2. **Visit in browser**:
   ```
   https://yoursite.com/HOTFIX_v1.2.0_materials_labor.php
   ```

3. **Apply hotfix**:
   - Password: `handycrm2025`
   - Click "Apply Hotfix Now"
   - Wait for success message

4. **Delete hotfix script** (security):
   ```bash
   rm /public_html/HOTFIX_v1.2.0_materials_labor.php
   ```

### Option 3: Manual File Upload (Your Case)

1. **Copy from desktop to production**:
   ```
   Local: C:\Users\user\Desktop\handycrm\controllers\ProjectTasksController.php
   Production: /home/u858321845/domains/1stop.gr/public_html/controllers/ProjectTasksController.php
   ```

2. **Upload via**:
   - FTP (FileZilla, WinSCP)
   - cPanel File Manager
   - SFTP

3. **Test**:
   - Go to project tasks
   - Add new task with materials
   - Save and verify materials appear

---

## ✅ Verification Steps

1. **Create Test Task**:
   - Go to any project → Tasks → New Task
   - Fill in task details (date, description)
   - Click "Προσθήκη Υλικού" (Add Material)
   - Enter: "Τσιμέντο 25kg", Unit: "τεμάχια", Price: 5.50, Quantity: 10
   - Click "Προσθήκη Τεχνικού" (Add Technician)
   - Enter technician with hours worked
   - Save task

2. **Verify in UI**:
   - Task should show in list
   - Open task details
   - Materials section should show "Τσιμέντο 25kg" with 10 τεμάχια @ €5.50 = €55.00
   - Labor section should show technician with hours

3. **Verify in Database** (optional):
   ```sql
   -- Check latest materials
   SELECT * FROM task_materials ORDER BY id DESC LIMIT 5;
   
   -- Check latest labor
   SELECT * FROM task_labor ORDER BY id DESC LIMIT 5;
   ```

---

## 🚨 Affected Sites

1. **1stop.gr** (Production)
   - Status: ⚠️ **NEEDS UPDATE**
   - Action: Upload fixed `ProjectTasksController.php`

2. **localhost XAMPP** (Development)
   - Status: ✅ **FIXED** (updated automatically)

---

## 📝 Changelog

### v1.2.0-hotfix2 (2025-10-16)
- **Fixed**: Materials not saving due to field name mismatch
- **Fixed**: Unit field not saving correctly
- **Added**: Catalog material ID support for autocomplete integration
- **Added**: Backward compatibility with old form field names

### Files Changed
- `controllers/ProjectTasksController.php` (lines 186-204)

### Commits
- `1001b9e` - FIX: Materials and labor not saving - field name mismatch

---

## 📞 Support

If you encounter issues after applying this hotfix:

1. **Check backup**: Backup created at `ProjectTasksController.php.backup_YYYY-MM-DD_HHMMSS`
2. **Restore backup**: `mv ProjectTasksController.php.backup_* ProjectTasksController.php`
3. **Contact**: theodore.sfakianakis@gmail.com

---

## 🎉 Summary

This was a simple but critical bug - the form was sending `materials[X][name]` but the controller was looking for `materials[X][description]`. The fix makes the controller accept both field names, ensuring:

- ✅ New v1.2.0 forms work correctly
- ✅ Old forms (if any) still work
- ✅ Autocomplete integration works
- ✅ Materials and labor save properly

**Estimated fix time**: 2 minutes (just upload one file)

---

**HandyCRM v1.2.0** | © 2025 Theodore Sfakianakis | All rights reserved
