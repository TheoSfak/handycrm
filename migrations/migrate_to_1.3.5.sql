-- ============================================================
-- HandyCRM Migration to v1.3.5
-- Date: 2025-10-21
-- Description: Payment Management System & Role-Based Access Control
-- ============================================================

-- This migration is IDEMPOTENT - safe to run multiple times
-- It checks for existing structures before making changes

-- ============================================================
-- 1. Update User Roles System
-- ============================================================

-- Check if role column needs updating
-- This will add 'supervisor' to the ENUM if it doesn't exist
ALTER TABLE users 
MODIFY COLUMN role ENUM('admin', 'supervisor', 'technician', 'assistant') 
DEFAULT 'technician' 
COMMENT 'User role: admin (full access), supervisor (projects & materials), technician (own profile), assistant (own profile)';

-- Ensure all users have valid roles
UPDATE users 
SET role = 'technician' 
WHERE role IS NULL OR role NOT IN ('admin', 'supervisor', 'technician', 'assistant');

-- ============================================================
-- 2. Ensure task_labor table has payment tracking fields
-- ============================================================

-- Add paid_at column if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = "task_labor";
SET @columnname = "paid_at";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " DATETIME NULL COMMENT 'When this labor entry was marked as paid'")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add paid_by column if it doesn't exist
SET @columnname = "paid_by";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " INT UNSIGNED NULL COMMENT 'User ID who marked this as paid', ADD CONSTRAINT fk_task_labor_paid_by FOREIGN KEY (", @columnname, ") REFERENCES users(id) ON DELETE SET NULL")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================
-- 3. Create indexes for payment queries performance
-- ============================================================

-- Index on paid_at for filtering paid/unpaid entries
SET @indexname = "idx_task_labor_paid_at";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = @indexname)
  ) > 0,
  "SELECT 1",
  CONCAT("CREATE INDEX ", @indexname, " ON ", @tablename, " (paid_at)")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Index on technician_id and paid_at for technician payment queries
SET @indexname = "idx_task_labor_tech_paid";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = @indexname)
  ) > 0,
  "SELECT 1",
  CONCAT("CREATE INDEX ", @indexname, " ON ", @tablename, " (technician_id, paid_at)")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================
-- 4. Ensure users table has is_active column
-- ============================================================

SET @tablename = "users";
SET @columnname = "is_active";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " TINYINT(1) DEFAULT 1 COMMENT 'Whether user account is active'")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Ensure all existing users are marked as active
UPDATE users SET is_active = 1 WHERE is_active IS NULL;

-- ============================================================
-- 5. Migration Completion Log
-- ============================================================

-- Record this migration in the migrations table
INSERT INTO migrations (filename, executed_at) 
VALUES ('migrate_to_1.3.5.sql', NOW())
ON DUPLICATE KEY UPDATE executed_at = NOW();

-- ============================================================
-- Migration Complete!
-- ============================================================
-- You can now use:
-- - Role-Based Access Control with admin/supervisor/technician/assistant
-- - Payment tracking with paid_at and paid_by fields
-- - Optimized payment queries with new indexes
-- - Active/inactive user filtering
-- ============================================================
