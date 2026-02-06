-- ============================================================================
-- Migration: Add aliases column to materials_catalog
-- Version: 1.2.1
-- Date: 2025-10-16
-- Description: Add aliases field for better search (Greeklish, synonyms, codes)
-- ============================================================================

USE handycrm;

-- Add aliases column
ALTER TABLE materials_catalog 
ADD COLUMN aliases TEXT NULL COMMENT 'Comma-separated search aliases (auto-generated + manual)' 
AFTER notes;

-- Create index for faster search
ALTER TABLE materials_catalog 
ADD FULLTEXT INDEX idx_name_aliases (name, aliases);

-- Success message
SELECT 'Migration completed: aliases column added to materials_catalog' as message;
