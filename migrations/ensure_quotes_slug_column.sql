-- Ensure slug column exists in quotes table
-- This migration replaces the PREPARE/EXECUTE approach in add_slug_to_quotes.sql
-- which could fail silently on some MySQL/PDO configurations.
-- AutoMigration ignores "Duplicate column" errors so this is safe to run on any server.

ALTER TABLE quotes ADD COLUMN slug VARCHAR(255) DEFAULT NULL AFTER quote_number;

ALTER TABLE quotes ADD UNIQUE KEY quotes_slug_unique (slug);

UPDATE quotes SET slug = LOWER(quote_number) WHERE slug IS NULL AND quote_number IS NOT NULL;
