-- Update user roles to match new system
-- Roles: admin, supervisor, technician, assistant

-- First, let's see what roles currently exist
-- UPDATE users SET role = 'admin' WHERE role = 'admin';
-- UPDATE users SET role = 'supervisor' WHERE role = 'manager' OR role = 'υπευθυνος';
-- UPDATE users SET role = 'technician' WHERE role = 'technician' OR role = 'τεχνικος';
-- UPDATE users SET role = 'assistant' WHERE role = 'assistant' OR role = 'βοηθος';

-- Add a comment to role column to document the roles
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'supervisor', 'technician', 'assistant') 
    DEFAULT 'technician' 
    COMMENT 'User role: admin (full access), supervisor (projects & materials), technician (own profile), assistant (own profile)';
