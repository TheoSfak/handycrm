-- ============================================================
-- HandyCRM v1.3.5 - Migration Verification Script
-- ============================================================
-- Run this to check if your database is ready for v1.3.5
-- This does NOT make changes, only checks!
-- ============================================================

SELECT 
    '=====================================' AS '';
SELECT 
    'HandyCRM v1.3.5 Migration Check' AS '';
SELECT 
    '=====================================' AS '';

-- Check 1: Users table role column
SELECT 
    CONCAT('✓ Check 1: Users role column') AS 'Status',
    CASE 
        WHEN COLUMN_TYPE LIKE '%supervisor%' THEN 'PASS - supervisor role exists'
        ELSE 'FAIL - supervisor role missing'
    END AS 'Result'
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'users'
  AND COLUMN_NAME = 'role';

-- Check 2: task_labor paid_at column
SELECT 
    CONCAT('✓ Check 2: task_labor paid_at column') AS 'Status',
    CASE 
        WHEN COUNT(*) > 0 THEN 'PASS - paid_at exists'
        ELSE 'FAIL - paid_at missing'
    END AS 'Result'
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'task_labor'
  AND COLUMN_NAME = 'paid_at';

-- Check 3: task_labor paid_by column
SELECT 
    CONCAT('✓ Check 3: task_labor paid_by column') AS 'Status',
    CASE 
        WHEN COUNT(*) > 0 THEN 'PASS - paid_by exists'
        ELSE 'FAIL - paid_by missing'
    END AS 'Result'
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'task_labor'
  AND COLUMN_NAME = 'paid_by';

-- Check 4: users is_active column
SELECT 
    CONCAT('✓ Check 4: users is_active column') AS 'Status',
    CASE 
        WHEN COUNT(*) > 0 THEN 'PASS - is_active exists'
        ELSE 'FAIL - is_active missing'
    END AS 'Result'
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'users'
  AND COLUMN_NAME = 'is_active';

-- Check 5: Indexes for performance
SELECT 
    CONCAT('✓ Check 5: task_labor payment indexes') AS 'Status',
    CASE 
        WHEN COUNT(*) >= 2 THEN 'PASS - payment indexes exist'
        ELSE 'WARN - some indexes may be missing'
    END AS 'Result'
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'task_labor'
  AND INDEX_NAME IN ('idx_task_labor_paid_at', 'idx_task_labor_tech_paid');

-- Check 6: migrations table
SELECT 
    CONCAT('✓ Check 6: migrations tracking table') AS 'Status',
    CASE 
        WHEN COUNT(*) > 0 THEN 'PASS - migrations table exists'
        ELSE 'FAIL - migrations table missing'
    END AS 'Result'
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'migrations';

-- Summary: Count role distribution
SELECT 
    '=====================================' AS '';
SELECT 
    'User Roles Summary' AS '';
SELECT 
    '=====================================' AS '';

SELECT 
    role AS 'Role',
    COUNT(*) AS 'Count',
    CONCAT(ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM users), 1), '%') AS 'Percentage'
FROM users
GROUP BY role
ORDER BY COUNT(*) DESC;

-- Summary: Payment tracking stats
SELECT 
    '=====================================' AS '';
SELECT 
    'Payment Tracking Summary' AS '';
SELECT 
    '=====================================' AS '';

SELECT 
    COUNT(*) AS 'Total Labor Entries',
    SUM(CASE WHEN paid_at IS NOT NULL THEN 1 ELSE 0 END) AS 'Paid Entries',
    SUM(CASE WHEN paid_at IS NULL THEN 1 ELSE 0 END) AS 'Unpaid Entries',
    CONCAT(ROUND(SUM(CASE WHEN paid_at IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1), '%') AS 'Payment Rate'
FROM task_labor
WHERE task_id IS NOT NULL;

-- Final recommendation
SELECT 
    '=====================================' AS '';
SELECT 
    'Recommendation' AS '';
SELECT 
    '=====================================' AS '';

SELECT 
    CASE 
        WHEN (
            SELECT COUNT(*) 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'users' 
              AND COLUMN_NAME = 'role' 
              AND COLUMN_TYPE LIKE '%supervisor%'
        ) > 0 THEN '✓ Database is ready for v1.3.5!'
        ELSE '✗ Run migrate_to_1.3.5.sql before upgrading'
    END AS 'Status';

-- ============================================================
-- End of verification script
-- ============================================================
