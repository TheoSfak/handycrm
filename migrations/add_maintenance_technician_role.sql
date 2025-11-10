-- Migration: Add maintenance_technician role for transformer maintenance specialists
-- Date: 2025-11-03
-- Description: Creates a specialized role for technicians who only have access to transformer maintenances and their profile

-- Add new role to the enum
ALTER TABLE users MODIFY COLUMN role ENUM('admin','supervisor','technician','assistant','maintenance_technician') DEFAULT 'technician';

-- Optional: Create a sample maintenance technician user (uncomment and modify as needed)
-- INSERT INTO users (username, email, password, first_name, last_name, role, is_active) VALUES 
-- ('maintenance_tech', 'maintenance@company.com', '$2y$10$example_hash_here', 'Maintenance', 'Technician', 'maintenance_technician', 1);