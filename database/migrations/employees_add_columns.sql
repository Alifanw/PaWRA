-- Migration: Add columns to employees table for attendance system
-- Date: 2025-11-21

USE walini_pj;

-- Add necessary columns
ALTER TABLE employees 
ADD COLUMN code VARCHAR(50) NOT NULL COMMENT 'Employee unique code' AFTER id;

ALTER TABLE employees
ADD COLUMN name VARCHAR(255) NOT NULL COMMENT 'Employee full name' AFTER code;

ALTER TABLE employees
ADD COLUMN is_active TINYINT(1) DEFAULT 1 COMMENT 'Active status' AFTER name;

-- Add unique index on code
CREATE UNIQUE INDEX idx_employee_code ON employees(code);

-- Add index on is_active for filtering
CREATE INDEX idx_is_active ON employees(is_active);

-- Insert test data
INSERT INTO employees (code, name, is_active, created_at, updated_at) 
VALUES 
('TEST123', 'Test Employee', 1, NOW(), NOW()),
('EMP001', 'John Doe', 1, NOW(), NOW()),
('EMP002', 'Jane Smith', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Show results
SELECT * FROM employees;
