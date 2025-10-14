# Project Tasks System - Testing Checklist

## Phase 10: Complete Testing & Validation

### 1. Database Verification ✅
```sql
-- Check all tables exist
SHOW TABLES LIKE '%task%';
SHOW TABLES LIKE 'technicians';

-- Verify sample data
SELECT * FROM technicians;

-- Check foreign keys
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'handycrm'
AND REFERENCED_TABLE_NAME IN ('technicians', 'projects', 'project_tasks');
```

### 2. Technicians Module Testing

#### 2.1 Create Technician
- [ ] Navigate to `/technicians`
- [ ] Click "Νέος Τεχνικός"
- [ ] Fill form:
  - Name: "Test Technician"
  - Role: Τεχνικός
  - Hourly Rate: 25.00
  - Phone: 6912345678
  - Email: test@example.com
  - Active: ✓
- [ ] Click "Αποθήκευση"
- [ ] Verify success message
- [ ] Verify technician appears in list

#### 2.2 Edit Technician
- [ ] Click "Edit" on a technician
- [ ] Change hourly rate to 30.00
- [ ] Click "Ενημέρωση"
- [ ] Verify changes saved

#### 2.3 View Technician Details
- [ ] Click "View" on a technician
- [ ] Verify all info displayed correctly
- [ ] Check statistics (will be 0 initially)
- [ ] Verify work history section

#### 2.4 Deactivate/Activate Technician
- [ ] In view page, click "Απενεργοποίηση"
- [ ] Verify status changes to "Ανενεργός"
- [ ] Click "Ενεργοποίηση"
- [ ] Verify status returns to "Ενεργός"

#### 2.5 Filter Technicians
- [ ] Go to technicians list
- [ ] Test filter dropdown:
  - [ ] All
  - [ ] Active only
  - [ ] Technicians only
  - [ ] Assistants only

### 3. Project Tasks Module Testing

#### 3.1 Navigate to Tasks
- [ ] Go to `/projects`
- [ ] Click on any project
- [ ] Verify new "Εργασίες" tab appears
- [ ] Click "Εργασίες" tab
- [ ] Verify tab content loads

#### 3.2 Create Single Day Task
- [ ] Click "Νέα Εργασία"
- [ ] Select "Μονοήμερη"
- [ ] Set date: Today
- [ ] Description: "Test Single Day Task"
- [ ] **Add Materials:**
  - Click "Προσθήκη Υλικού"
  - Description: "Τσιμέντο"
  - Unit Price: 10.50
  - Quantity: 20
  - Unit: Κιλά
  - Verify subtotal calculates: 210.00
- [ ] **Add Labor:**
  - Click "Προσθήκη Τεχνικού"
  - Select a technician from dropdown
  - Verify hourly rate auto-fills
  - Hours: 8
  - Verify subtotal calculates correctly
- [ ] Verify grand total updates automatically
- [ ] Click "Αποθήκευση Εργασίας"
- [ ] Verify success message
- [ ] Verify task appears in list with correct totals

#### 3.3 Create Date Range Task
- [ ] Click "Νέα Εργασία"
- [ ] Select "Εύρος Ημερομηνιών"
- [ ] From: Tomorrow
- [ ] To: +3 days from tomorrow
- [ ] Description: "Test Date Range Task"
- [ ] Add materials and labor
- [ ] Verify daily average calculation
- [ ] Click save
- [ ] Verify task shows duration (4 days)

#### 3.4 Overlap Detection
- [ ] Create new date range task
- [ ] Set dates that overlap with existing task
- [ ] Verify yellow warning appears:
  "Προσοχή! Υπάρχουν ήδη εργασίες σε αυτό το χρονικό διάστημα"
- [ ] Verify list of overlapping tasks shown
- [ ] Confirm can still save if needed

#### 3.5 Time-Based Hour Calculation
- [ ] Create task with labor entry
- [ ] Instead of entering hours, use time fields:
  - From: 09:00
  - To: 17:00
- [ ] Verify hours automatically calculates to 8.0
- [ ] Test overnight shift:
  - From: 22:00
  - To: 06:00
- [ ] Verify calculates 8.0 hours correctly

#### 3.6 Live Calculations
- [ ] Add material row
- [ ] Change unit price
- [ ] Verify subtotal updates immediately
- [ ] Change quantity
- [ ] Verify subtotal updates
- [ ] Verify materials total updates
- [ ] Add labor row
- [ ] Change hours
- [ ] Verify labor subtotal updates
- [ ] Verify grand total updates

#### 3.7 Edit Task
- [ ] Click "Edit" on a task
- [ ] Verify all data prepopulated:
  - [ ] Task type selected
  - [ ] Dates filled
  - [ ] Description filled
  - [ ] Materials rows loaded
  - [ ] Labor rows loaded
  - [ ] Totals calculated
- [ ] Modify materials
- [ ] Add new labor entry
- [ ] Click "Ενημέρωση"
- [ ] Verify changes saved

#### 3.8 View Task (Readonly)
- [ ] Click "View" (eye icon) on task
- [ ] Verify all data displayed readonly:
  - [ ] Materials table with totals
  - [ ] Labor table with totals
  - [ ] Cost summary card
  - [ ] Progress bars showing material/labor split
  - [ ] Additional info (type, duration, created/updated)

#### 3.9 Daily Breakdown (Date Range Only)
- [ ] Find a date range task
- [ ] Click "Ανάλυση" (chart icon)
- [ ] Verify:
  - [ ] Summary cards at top
  - [ ] Daily breakdown table
  - [ ] Each day shows date and weekday (in Greek)
  - [ ] Weekend days marked with "Σ/Κ" badge
  - [ ] Materials and labor split per day
  - [ ] Chart.js visualization at bottom
  - [ ] Stacked bar chart with materials (yellow) and labor (blue)

#### 3.10 Copy Task
- [ ] Click "Copy" (copy icon) on task
- [ ] Confirm dialog
- [ ] Verify new task created with:
  - [ ] Same materials
  - [ ] Same labor entries
  - [ ] Same description
  - [ ] Dates NOT copied (must be set manually)

#### 3.11 Delete Task
- [ ] Click "Delete" (trash icon)
- [ ] Verify confirmation dialog shows task description
- [ ] Confirm deletion
- [ ] Verify task removed from list
- [ ] Verify summary cards update

#### 3.12 Filters & Sorting
- [ ] Test Type Filter:
  - [ ] All
  - [ ] Μονοήμερες
  - [ ] Εύρος Ημερομηνιών
- [ ] Test Date Filters:
  - [ ] From Date
  - [ ] To Date
  - [ ] Both together
- [ ] Test Sorting:
  - [ ] Νεότερες Πρώτα (newest first)
  - [ ] Παλαιότερες Πρώτα (oldest first)
  - [ ] Κόστος Φθίνουσα (cost desc)
  - [ ] Κόστος Αύξουσα (cost asc)
- [ ] Click "Καθαρισμός" to clear filters

### 4. Integration Testing

#### 4.1 Technician Work History
- [ ] Create several tasks with same technician
- [ ] Go to technician view page
- [ ] Verify work history shows all tasks
- [ ] Verify statistics calculate correctly:
  - [ ] Total Hours
  - [ ] Total Earnings (hours × rate)
  - [ ] Project Count

#### 4.2 Technician in Task Dropdown
- [ ] Create/Edit task
- [ ] In labor section, open technician dropdown
- [ ] Verify shows: "Name (Role - Rate€/hour)"
- [ ] Select technician
- [ ] Verify hourly rate auto-fills

#### 4.3 Project View Integration
- [ ] Go to project view
- [ ] Verify 3 tabs: Γενικά, Εργασίες, Ραντεβού
- [ ] Click each tab
- [ ] Verify content loads correctly
- [ ] Verify badge counts on tabs

#### 4.4 Deactivated Technician Handling
- [ ] Deactivate a technician
- [ ] Try to create new task
- [ ] Verify deactivated technician NOT in dropdown
- [ ] Edit existing task with that technician
- [ ] Verify can still see/edit old tasks

### 5. API Endpoints Testing

#### 5.1 Technician API
```bash
# Get single technician
curl http://localhost/handycrm/api/technicians/1

# Get all active technicians
curl http://localhost/handycrm/api/technicians
```
- [ ] Verify JSON response
- [ ] Verify data structure

#### 5.2 Overlap Check API
```bash
curl -X POST http://localhost/handycrm/api/tasks/check-overlap \
  -d "project_id=1" \
  -d "date_from=2025-10-15" \
  -d "date_to=2025-10-20"
```
- [ ] Verify returns overlapping tasks
- [ ] Test with no overlaps
- [ ] Test excluding current task

### 6. Validation Testing

#### 6.1 Required Fields
- [ ] Try to create task without description → Error
- [ ] Try to create task without materials AND labor → Error
- [ ] Try to add material without description → Error
- [ ] Try to add labor without hours AND time → Error

#### 6.2 Date Validation
- [ ] Try date_to before date_from → Should show error
- [ ] Try invalid date format → Should prevent

#### 6.3 Numeric Validation
- [ ] Try negative unit price → Should prevent
- [ ] Try negative quantity → Should prevent
- [ ] Try negative hours → Should prevent
- [ ] Try text in numeric fields → Should prevent

### 7. Calculations Verification

#### 7.1 Material Calculations
```
Unit Price: 12.50
Quantity: 15
Expected Subtotal: 187.50
```
- [ ] Verify matches

#### 7.2 Labor Calculations
```
Hourly Rate: 20.00
Hours: 7.5
Expected Subtotal: 150.00
```
- [ ] Verify matches

#### 7.3 Time Calculations
```
From: 08:30
To: 16:45
Expected Hours: 8.25
```
- [ ] Verify calculates correctly

#### 7.4 Daily Average (Date Range)
```
Total Cost: 1200.00
Days: 5
Expected Average: 240.00
```
- [ ] Verify in breakdown view

### 8. UI/UX Testing

- [ ] All buttons have icons
- [ ] Color coding consistent (primary=blue, warning=yellow, info=cyan, success=green)
- [ ] Cards have hover effects
- [ ] Forms have validation feedback
- [ ] Success messages appear and dismiss
- [ ] Loading states visible (if applicable)
- [ ] Mobile responsive (test on small screen)
- [ ] All Greek translations correct
- [ ] No JavaScript console errors
- [ ] No PHP errors in logs

### 9. Performance Testing

- [ ] Create task with 10 materials → Should handle smoothly
- [ ] Create task with 10 labor entries → Should handle smoothly
- [ ] Load tasks list with 50+ tasks → Should paginate or load quickly
- [ ] Filter large dataset → Should respond quickly

### 10. Edge Cases

- [ ] Create task with 0 materials but labor only → Should work
- [ ] Create task with materials only but no labor → Should work
- [ ] Create task on Feb 29 (leap year)
- [ ] Create task spanning month boundary
- [ ] Create task spanning year boundary
- [ ] Delete project with tasks → Should cascade delete
- [ ] Delete technician with work history → Should prevent or set NULL

---

## Testing Completion Checklist

### Backend (Models & Controllers)
- [ ] All CRUD operations work
- [ ] Validation prevents invalid data
- [ ] Calculations accurate
- [ ] Transactions rollback on error
- [ ] Foreign keys enforced

### Frontend (Views)
- [ ] All forms submit correctly
- [ ] Dynamic rows add/remove
- [ ] Live calculations update
- [ ] Filters work
- [ ] Sorting works
- [ ] Responsive design

### Integration
- [ ] Technicians link to tasks
- [ ] Tasks link to projects
- [ ] Statistics calculate correctly
- [ ] Tabs navigate properly
- [ ] API endpoints respond

### Data Integrity
- [ ] No orphaned records
- [ ] Totals match sum of items
- [ ] Dates logical
- [ ] No negative values
- [ ] Decimal precision correct (2 places)

---

## Known Issues to Fix (If Found)

1. **Issue**: [Description]
   - **Severity**: High/Medium/Low
   - **Steps to Reproduce**: 
   - **Expected**: 
   - **Actual**: 
   - **Fix**: 

---

## Final Sign-Off

- [ ] All 10 phases completed
- [ ] All tests passed
- [ ] No critical bugs
- [ ] Documentation complete
- [ ] Ready for production

**Tested By**: _________________
**Date**: _________________
**Notes**: _________________
