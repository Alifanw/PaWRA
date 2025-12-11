-- ============================================
-- MIGRASI DATA LAMA KE TABEL BARU
-- ============================================

-- 1. Migrasi karyawan lama ke employees
INSERT INTO employees (code, name, is_active, created_at)
SELECT 
    kodes as code,
    nama as name,
    1 as is_active,
    NOW() as created_at
FROM karyawan
WHERE NOT EXISTS (
    SELECT 1 FROM employees WHERE employees.code = karyawan.kodes
);

-- 2. Migrasi log_absensi lama ke attendance_logs
INSERT INTO attendance_logs (employee_id, device_code, event_time, status, raw_name)
SELECT 
    e.id as employee_id,
    'LEGACY' as device_code,
    la.waktus as event_time,
    la.status as status,
    la.nama as raw_name
FROM log_absensi la
JOIN employees e ON la.kodes = e.code
WHERE NOT EXISTS (
    SELECT 1 FROM attendance_logs al 
    WHERE al.employee_id = e.id 
    AND al.event_time = la.waktus 
    AND al.status = la.status
)
ORDER BY la.waktus ASC;

-- 3. Verifikasi hasil migrasi
SELECT 
    'Employees migrated' as info,
    COUNT(*) as total
FROM employees
WHERE created_at >= CURDATE()

UNION ALL

SELECT 
    'Attendance logs migrated' as info,
    COUNT(*) as total
FROM attendance_logs
WHERE device_code = 'LEGACY';

-- 4. (Optional) Backup tabel lama
-- RENAME TABLE karyawan TO karyawan_backup;
-- RENAME TABLE log_absensi TO log_absensi_backup;
