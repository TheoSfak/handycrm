-- ============================================================
-- HandyCRM v1.3.5 - HOTFIX Migration
-- EMERGENCY FIX: Adds missing paid_by column for payments
-- SAFE TO RUN MULTIPLE TIMES (Idempotent)
-- ============================================================

-- Check if paid_by column exists
SET @dbname = DATABASE();
SET @tablename = 'task_labor';
SET @columnname = 'paid_by';

SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND COLUMN_NAME = @columnname
);

-- Add paid_by column only if it doesn't exist
SET @sql = IF(@column_exists = 0,
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT UNSIGNED NULL COMMENT ''User ID who marked this as paid'''),
    'SELECT ''Column paid_by already exists'' as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key only if column was just added or FK doesn't exist
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND CONSTRAINT_NAME = 'fk_task_labor_paid_by'
);

SET @sql = IF(@fk_exists = 0 AND @column_exists = 0,
    CONCAT('ALTER TABLE ', @tablename, ' ADD CONSTRAINT fk_task_labor_paid_by FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE SET NULL'),
    'SELECT ''FK already exists or will be added separately'' as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Record migration
INSERT INTO migrations (filename, executed_at) 
VALUES ('hotfix_1.3.5_paid_by.sql', NOW())
ON DUPLICATE KEY UPDATE executed_at = NOW();

-- Show final status
SELECT 'HOTFIX COMPLETE!' as status,
       CASE 
           WHEN @column_exists = 0 THEN 'paid_by column ADDED'
           ELSE 'paid_by column ALREADY EXISTS'
       END as column_status,
       CASE 
           WHEN @fk_exists = 0 THEN 'Foreign key ADDED or PENDING'
           ELSE 'Foreign key ALREADY EXISTS'
       END as fk_status;

