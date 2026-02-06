-- Migration: Add name and unit fields to task_materials
-- Date: 2025
-- Purpose: Add separate name field (different from description) and unit field (text instead of enum)

-- Add name column (will store material name from catalog or manual entry)
ALTER TABLE task_materials 
ADD COLUMN name VARCHAR(255) NULL AFTER catalog_material_id;

-- Add unit column (text field for flexible unit types like 'τεμάχια', 'μέτρα', 'κιλά', etc.)
ALTER TABLE task_materials 
ADD COLUMN unit VARCHAR(50) NULL AFTER quantity;

-- Copy existing description to name for existing records
UPDATE task_materials SET name = description WHERE name IS NULL;

-- Update name to NOT NULL after data migration
ALTER TABLE task_materials 
MODIFY COLUMN name VARCHAR(255) NOT NULL;
