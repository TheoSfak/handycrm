-- Migration: Add transformer type field (Oil vs Dry type)
-- Date: 2025-10-31

-- Add transformer_type column to support Oil/Dry transformer types
ALTER TABLE transformer_maintenances 
ADD COLUMN transformer_type ENUM('oil', 'dry') DEFAULT 'oil' COMMENT 'Transformer type: oil (Ελαίου) or dry (Ξηρού)' 
AFTER transformer_power;

-- Note: For multiple transformers, this will be stored in the transformers_data JSON field
-- The transformer_type column is kept for backward compatibility with single transformer records