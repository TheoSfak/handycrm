-- Migration: Add language column to users table
-- Date: 2025-10-09
-- Description: Add language preference column for multi-language support

-- Check if column exists before adding
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'language'
);

-- Add language column if it doesn't exist
SET @query = IF(@col_exists = 0,
    'ALTER TABLE users ADD COLUMN language VARCHAR(2) DEFAULT ''el'' AFTER email',
    'SELECT ''Column language already exists'' AS message'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing users to have default language
UPDATE users SET language = 'el' WHERE language IS NULL OR language = '';

SELECT 'Migration completed successfully' AS status;
