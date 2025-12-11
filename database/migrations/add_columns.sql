-- Add missing columns to users table
-- Check and add full_name column
SET @column_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'full_name'
);

SET @sql = IF(@column_exists = 0, 'ALTER TABLE users ADD COLUMN full_name VARCHAR(255) NULL AFTER name', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add role_id column
SET @column_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role_id'
);

SET @sql = IF(@column_exists = 0, 'ALTER TABLE users ADD COLUMN role_id SMALLINT UNSIGNED NULL AFTER email', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add is_active column
SET @column_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'is_active'
);

SET @sql = IF(@column_exists = 0, 'ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT true AFTER role_id', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Populate full_name dari name kolom
UPDATE users SET full_name = name WHERE full_name IS NULL;

-- Populate role_id untuk admin user (dari role_user table)
UPDATE users u
SET role_id = (
    SELECT role_id FROM role_user WHERE user_id = u.id LIMIT 1
)
WHERE role_id IS NULL AND id IN (SELECT DISTINCT user_id FROM role_user);

-- Set admin users role_id = 2 if not set
UPDATE users SET role_id = 2, is_active = 1 WHERE username = 'admin' AND role_id IS NULL;

-- Set is_active = 1 for all users
UPDATE users SET is_active = 1 WHERE is_active = 0 OR is_active IS NULL;
