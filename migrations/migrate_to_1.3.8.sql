-- HandyCRM Migration to v1.3.8
-- VAT Display Settings Feature
-- Date: 2025-10-23

-- Add VAT display settings to settings table
INSERT INTO settings (setting_key, setting_value, description, created_at, updated_at) 
VALUES 
    ('display_vat_notes', '1', 'Display VAT notes next to prices (1=Yes, 0=No)', NOW(), NOW()),
    ('prices_include_vat', '0', 'Whether prices include VAT (1=Yes, 0=No)', NOW(), NOW()),
    ('default_vat_rate', '24', 'Default VAT rate percentage', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    updated_at = NOW();

-- Migration completed successfully
