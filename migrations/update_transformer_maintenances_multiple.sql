-- Migration: Support multiple transformers per maintenance
-- Date: 2025-10-30

-- Add new column for storing multiple transformers as JSON
ALTER TABLE transformer_maintenances 
ADD COLUMN transformers_data JSON DEFAULT NULL COMMENT 'Array of transformer data (power, insulation, coil resistance, etc.)' 
AFTER photo;

-- Note: We keep the old single transformer fields for backward compatibility
-- The app will use transformers_data if available, otherwise falls back to single fields
