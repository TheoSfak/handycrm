-- Remove duplicate maintenances permissions
-- Keep only transformer_maintenance.* permissions

-- First, remove role_permissions entries for maintenances.*
DELETE FROM role_permissions 
WHERE permission_id IN (
    SELECT id FROM permissions 
    WHERE module = 'maintenances'
);

-- Then delete the maintenances permissions themselves
DELETE FROM permissions 
WHERE module = 'maintenances';

-- Verify what's left
SELECT * FROM permissions WHERE module LIKE '%maintenance%' ORDER BY module, action;
