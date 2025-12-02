<?php

require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../models/EmployeeModel.php';
require_once __DIR__ . '/../models/AttendanceModel.php';
require_once __DIR__ . '/../models/DoorEventModel.php';
require_once __DIR__ . '/../validators/AttendanceValidator.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/DoorlockService.php';

class AbsensiService
{
    private $db;
    private $employeeModel;
    private $attendanceModel;
    private $doorEventModel;
    private $doorlockService;
    private $logger;

    public function __construct($db)
    {
        $this->db = $db;
        $this->employeeModel = new EmployeeModel($db);
        $this->attendanceModel = new AttendanceModel($db);
        $this->doorEventModel = new DoorEventModel($db);
        $this->doorlockService = new DoorlockService();
        $this->logger = new Logger(Config::ABSEN_LOG);
    }

    /**
     * Process attendance with full validation
     */
    public function processAttendance($kode, $status, $deviceCode = 'KIOSK-01')
    {
        try {
            // 1. Validate status
            $statusValidation = AttendanceValidator::validateStatus($status);
            if (!$statusValidation['valid']) {
                return [
                    'success' => false,
                    'message' => $statusValidation['message']
                ];
            }

            // 2. Find employee
            $employee = $this->employeeModel->findByCode($kode);
            if (!$employee) {
                $this->logger->warning("Employee not found", ['kode' => $kode]);
                return [
                    'success' => false,
                    'message' => 'Kode karyawan tidak ditemukan'
                ];
            }

            $employeeId = $employee['id'];
            $employeeName = $employee['name'];

            // 3. Get last log today
            $lastLog = $this->attendanceModel->getLastLogToday($employeeId);
            $lastStatus = $lastLog ? $lastLog['status'] : null;

            $this->logger->info("Processing attendance", [
                'kode' => $kode,
                'name' => $employeeName,
                'status' => $status,
                'last_status' => $lastStatus
            ]);

            // 4. Validate attendance flow
            $flowValidation = AttendanceValidator::validateAttendanceFlow($status, $lastStatus, $employeeName);
            if (!$flowValidation['valid']) {
                $this->logger->warning("Validation failed", [
                    'message' => $flowValidation['message']
                ]);
                return [
                    'success' => false,
                    'message' => $flowValidation['message']
                ];
            }

            // 5. Insert attendance log
            $inserted = $this->attendanceModel->insert($employeeId, $deviceCode, $status, $employeeName);
            
            if (!$inserted) {
                throw new Exception('Gagal menyimpan log absensi');
            }

            $this->logger->info("Attendance saved", [
                'employee' => $employeeName,
                'status' => $status
            ]);

            // 6. Trigger doorlock
            $doorResult = $this->doorlockService->triggerOpen($kode, $status);

            // 7. Save door event
            $this->doorEventModel->insert(
                $deviceCode,
                $status,
                $doorResult['http_code'],
                $doorResult['response'],
                $doorResult['error'],
                $kode
            );

            // 8. Return success
            return [
                'success' => true,
                'message' => "Absensi $status berhasil",
                'nama' => $employeeName,
                'waktu' => date('Y-m-d H:i:s'),
                'door_triggered' => $doorResult['success']
            ];

        } catch (Exception $e) {
            $this->logger->error("Process failed", [
                'error' => $e->getMessage(),
                'kode' => $kode,
                'status' => $status
            ]);
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get attendance history
     */
    public function getAttendanceHistory($startDate, $endDate = null, $employeeCode = null)
    {
        $employeeId = null;
        
        if ($employeeCode) {
            $employee = $this->employeeModel->findByCode($employeeCode);
            if ($employee) {
                $employeeId = $employee['id'];
            }
        }

        return $this->attendanceModel->getLogsByDateRange($startDate, $endDate, $employeeId);
    }

    /**
     * Get today's attendance summary
     */
    public function getTodaySummary()
    {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT CASE WHEN status = 'masuk' THEN employee_id END) as total_masuk,
                        COUNT(DISTINCT CASE WHEN status = 'pulang' THEN employee_id END) as total_pulang,
                        COUNT(DISTINCT CASE WHEN status = 'lembur' THEN employee_id END) as total_lembur,
                        COUNT(DISTINCT employee_id) as total_activity,
                        (SELECT COUNT(*) FROM employees WHERE is_active = 1) as total_employees
                      FROM attendance_logs 
                      WHERE DATE(event_time) = CURDATE()";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get recent activities (last 10)
            $recentQuery = "SELECT 
                              e.code as employee_code,
                              e.name as employee_name,
                              a.status,
                              DATE_FORMAT(a.event_time, '%H:%i:%s') as time
                            FROM attendance_logs a
                            JOIN employees e ON a.employee_id = e.id
                            WHERE DATE(a.event_time) = CURDATE()
                            ORDER BY a.event_time DESC
                            LIMIT 10";
            
            $stmt = $this->db->prepare($recentQuery);
            $stmt->execute();
            $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'summary' => $summary,
                'recent_activities' => $recent,
                'date' => date('Y-m-d')
            ];
        } catch (PDOException $e) {
            $this->logger->error('Error getting today summary', ['error' => $e->getMessage()]);
            throw new Exception('Failed to get today summary');
        }
    }

    /**
     * Get employee attendance history for last N days
     */
    public function getEmployeeHistory($employeeCode, $days = 30)
    {
        try {
            $employee = $this->employeeModel->findByCode($employeeCode);
            if (!$employee) {
                throw new Exception('Karyawan tidak ditemukan');
            }

            $query = "SELECT 
                        DATE(event_time) as date,
                        GROUP_CONCAT(
                            CONCAT(status, ':', TIME_FORMAT(event_time, '%H:%i')) 
                            ORDER BY event_time 
                            SEPARATOR '|'
                        ) as activities,
                        MIN(CASE WHEN status = 'masuk' THEN event_time END) as masuk_time,
                        MAX(CASE WHEN status IN ('pulang', 'pulang_lembur') THEN event_time END) as pulang_time
                      FROM attendance_logs
                      WHERE employee_id = :employee_id
                        AND event_time >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                      GROUP BY DATE(event_time)
                      ORDER BY date DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':employee_id', $employee['id'], PDO::PARAM_INT);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate work hours
            foreach ($history as &$day) {
                if ($day['masuk_time'] && $day['pulang_time']) {
                    $masuk = strtotime($day['masuk_time']);
                    $pulang = strtotime($day['pulang_time']);
                    $hours = ($pulang - $masuk) / 3600;
                    $day['work_hours'] = round($hours, 2);
                } else {
                    $day['work_hours'] = null;
                }
            }
            
            return [
                'employee' => [
                    'code' => $employee['code'],
                    'name' => $employee['name']
                ],
                'period' => $days . ' days',
                'history' => $history
            ];
        } catch (PDOException $e) {
            $this->logger->error('Error getting employee history', ['error' => $e->getMessage()]);
            throw new Exception('Failed to get employee history');
        }
    }

    /**
     * Get monthly statistics
     */
    public function getMonthlyStats($month)
    {
        try {
            $monthStart = $month . '-01';
            $monthEnd = date('Y-m-t', strtotime($monthStart));
            
            $statusQuery = "SELECT 
                              status,
                              COUNT(*) as count
                            FROM attendance_logs
                            WHERE DATE(event_time) BETWEEN :start AND :end
                            GROUP BY status";
            
            $stmt = $this->db->prepare($statusQuery);
            $stmt->bindParam(':start', $monthStart);
            $stmt->bindParam(':end', $monthEnd);
            $stmt->execute();
            $byStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $empQuery = "SELECT 
                          e.code,
                          e.name,
                          COUNT(DISTINCT DATE(a.event_time)) as days_present,
                          COUNT(CASE WHEN a.status = 'lembur' THEN 1 END) as overtime_count
                        FROM employees e
                        LEFT JOIN attendance_logs a ON e.id = a.employee_id 
                          AND DATE(a.event_time) BETWEEN :start AND :end
                        WHERE e.is_active = 1
                        GROUP BY e.id, e.code, e.name
                        ORDER BY days_present DESC";
            
            $stmt = $this->db->prepare($empQuery);
            $stmt->bindParam(':start', $monthStart);
            $stmt->bindParam(':end', $monthEnd);
            $stmt->execute();
            $byEmployee = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $trendQuery = "SELECT 
                            DATE(event_time) as date,
                            COUNT(DISTINCT employee_id) as unique_employees,
                            COUNT(*) as total_records
                          FROM attendance_logs
                          WHERE DATE(event_time) BETWEEN :start AND :end
                          GROUP BY DATE(event_time)
                          ORDER BY date";
            
            $stmt = $this->db->prepare($trendQuery);
            $stmt->bindParam(':start', $monthStart);
            $stmt->bindParam(':end', $monthEnd);
            $stmt->execute();
            $dailyTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'period' => [
                    'month' => $month,
                    'start' => $monthStart,
                    'end' => $monthEnd
                ],
                'by_status' => $byStatus,
                'by_employee' => $byEmployee,
                'daily_trend' => $dailyTrend
            ];
        } catch (PDOException $e) {
            $this->logger->error('Error getting monthly stats', ['error' => $e->getMessage()]);
            throw new Exception('Failed to get monthly stats');
        }
    }

    /**
     * Check employee status today
     */
    public function checkTodayStatus($employeeCode)
    {
        try {
            $employee = $this->employeeModel->findByCode($employeeCode);
            if (!$employee) {
                throw new Exception('Karyawan tidak ditemukan');
            }

            $lastLog = $this->attendanceModel->getLastLogToday($employee['id']);
            
            return [
                'employee' => [
                    'code' => $employee['code'],
                    'name' => $employee['name']
                ],
                'has_clocked_in' => $lastLog && $lastLog['status'] === 'masuk',
                'last_status' => $lastLog ? $lastLog['status'] : null,
                'last_time' => $lastLog ? $lastLog['event_time'] : null,
                'can_clock_in' => !$lastLog || $lastLog['status'] !== 'masuk',
                'can_clock_out' => $lastLog && in_array($lastLog['status'], ['masuk', 'lembur']),
                'can_overtime' => $lastLog && $lastLog['status'] === 'pulang',
                'date' => date('Y-m-d')
            ];
        } catch (PDOException $e) {
            $this->logger->error('Error checking today status', ['error' => $e->getMessage()]);
            throw new Exception('Failed to check status');
        }
    }
}

