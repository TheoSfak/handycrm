-- Add slug column to quotes table (if missing) and backfill from quote_number
-- Migration: add_slug_to_quotes

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'quotes'
      AND COLUMN_NAME = 'slug'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE quotes ADD COLUMN slug VARCHAR(255) DEFAULT NULL AFTER quote_number',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add unique index if it doesn't already exist
SET @idx_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'quotes'
      AND INDEX_NAME = 'slug'
);

SET @sql2 = IF(@idx_exists = 0,
    'ALTER TABLE quotes ADD UNIQUE KEY slug (slug)',
    'SELECT 1'
);

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Backfill slug from quote_number for rows where slug is NULL
UPDATE quotes SET slug = LOWER(quote_number) WHERE slug IS NULL AND quote_number IS NOT NULL;
