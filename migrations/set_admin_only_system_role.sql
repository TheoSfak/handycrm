-- Update roles to set only 'admin' as system role
-- All other roles can be deleted if no users are assigned

UPDATE roles 
SET is_system = 0 
WHERE name != 'admin';

UPDATE roles 
SET is_system = 1 
WHERE name = 'admin';
