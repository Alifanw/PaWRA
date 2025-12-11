-- Migration: Add columns to attendance_logs table
-- Date: 2025-11-21

USE walini_pj;

-- Add necessary columns
ALTER TABLE attendance_logs 
ADD COLUMN employee_id BIGINT UNSIGNED NOT NULL COMMENT 'FK to employees.id' AFTER id;

ALTER TABLE attendance_logs
ADD COLUMN device_code VARCHAR(50) NULL COMMENT 'Device identifier (e.g., KIOSK-01)' AFTER employee_id;

ALTER TABLE attendance_logs
ADD COLUMN event_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Actual attendance time' AFTER device_code;

ALTER TABLE attendance_logs
ADD COLUMN status VARCHAR(20) NOT NULL COMMENT 'masuk/pulang/lembur/pulang_lembur' AFTER event_time;

ALTER TABLE attendance_logs
ADD COLUMN raw_name VARCHAR(255) NULL COMMENT 'Original name from device' AFTER status;

-- Add indexes
CREATE INDEX idx_employee_id ON attendance_logs(employee_id);
CREATE INDEX idx_event_time ON attendance_logs(event_time);
CREATE INDEX idx_status ON attendance_logs(status);
CREATE INDEX idx_employee_date ON attendance_logs(employee_id, event_time);

-- Add foreign key
ALTER TABLE attendance_logs 
ADD CONSTRAINT fk_attendance_employee 
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;

-- Show structure
DESCRIBE attendance_logs;
