-- Migration: Add additional columns to door_events table for attendance API
-- Date: 2025-11-21
-- Description: Adds columns needed for doorlock API integration

USE walini_pj;

-- Add new columns for API tracking
ALTER TABLE door_events 
ADD COLUMN device_code VARCHAR(50) NULL COMMENT 'Device identifier (e.g., KIOSK-01)' AFTER event_type;

ALTER TABLE door_events
ADD COLUMN status VARCHAR(20) NULL COMMENT 'Attendance status (masuk/pulang/lembur/pulang_lembur)' AFTER device_code;

ALTER TABLE door_events
ADD COLUMN processed TINYINT(1) DEFAULT 0 COMMENT 'Whether event has been processed' AFTER status;

ALTER TABLE door_events
ADD COLUMN http_code INT NULL COMMENT 'HTTP response code from doorlock API' AFTER processed;

ALTER TABLE door_events
ADD COLUMN response_message TEXT NULL COMMENT 'Success response from doorlock' AFTER http_code;

ALTER TABLE door_events
ADD COLUMN error_message TEXT NULL COMMENT 'Error message if request failed' AFTER response_message;

ALTER TABLE door_events
ADD COLUMN employee_code VARCHAR(50) NULL COMMENT 'Employee code for reference' AFTER error_message;

-- Add indexes for better query performance
CREATE INDEX idx_device_code ON door_events(device_code);
CREATE INDEX idx_employee_code ON door_events(employee_code);
CREATE INDEX idx_processed ON door_events(processed, event_time);

-- Show updated structure
DESCRIBE door_events;
