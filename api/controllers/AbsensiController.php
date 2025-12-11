<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../services/AbsensiService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../classes/ApiResponse.php';

class AbsensiController
{
    private $db;
    private $absensiService;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->absensiService = new AbsensiService($this->db);
    }

    /**
     * POST /api/absen
     */
    public function absen()
    {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Fallback to POST data
        if (!$input) {
            $input = $_POST;
        }

        // 1. Validate token
        $token = $input['token'] ?? '';
        $tokenCheck = AuthMiddleware::validateToken($token);
        
        if (!$tokenCheck['valid']) {
            ApiResponse::error($tokenCheck['message'], 403);
        }

        // 2. Get parameters
        $kode = $input['kode'] ?? ($input['kodes'] ?? '');
        $status = strtolower($input['status'] ?? '');
        $deviceCode = $input['device_code'] ?? 'KIOSK-01';

        // 3. Validate input
        if (empty($kode)) {
            ApiResponse::error('Kode karyawan wajib diisi');
        }

        if (empty($status)) {
            ApiResponse::error('Status absensi wajib diisi');
        }

        // 4. Check rate limit
        $rateLimit = AuthMiddleware::checkRateLimit($kode, $this->db);
        if (!$rateLimit['allowed']) {
            ApiResponse::error($rateLimit['message'], 429);
        }

        // 5. Process attendance
        $result = $this->absensiService->processAttendance($kode, $status, $deviceCode);

        // 6. Return response
        if ($result['success']) {
            ApiResponse::success([
                'nama' => $result['nama'],
                'waktu' => $result['waktu'],
                'door_triggered' => $result['door_triggered']
            ], $result['message']);
        } else {
            ApiResponse::error($result['message']);
        }
    }

    /**
     * GET /api/absen/history
     */
    public function history()
    {
        // Validate token
        $token = $_GET['token'] ?? '';
        $tokenCheck = AuthMiddleware::validateToken($token);
        
        if (!$tokenCheck['valid']) {
            ApiResponse::error($tokenCheck['message'], 403);
        }

        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? null;
        $kode = $_GET['kode'] ?? null;

        $history = $this->absensiService->getAttendanceHistory($startDate, $endDate, $kode);

        ApiResponse::success($history, 'History retrieved successfully');
    }

    /**
     * GET /api/absen/today
     * Get today's attendance summary
     */
    public function today()
    {
        $token = $_GET['token'] ?? '';
        $tokenCheck = AuthMiddleware::validateToken($token);
        
        if (!$tokenCheck['valid']) {
            ApiResponse::error($tokenCheck['message'], 403);
        }

        $summary = $this->absensiService->getTodaySummary();
        ApiResponse::success($summary, 'Today summary retrieved');
    }

    /**
     * GET /api/absen/employee/{code}
     * Get specific employee attendance history
     */
    public function employeeHistory()
    {
        $token = $_GET['token'] ?? '';
        $tokenCheck = AuthMiddleware::validateToken($token);
        
        if (!$tokenCheck['valid']) {
            ApiResponse::error($tokenCheck['message'], 403);
        }

        // Get employee code from URL
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/\/employee\/([^\/\?]+)/', $uri, $matches);
        $kode = $matches[1] ?? null;

        if (!$kode) {
            ApiResponse::error('Employee code is required', 400);
        }

        $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
        $history = $this->absensiService->getEmployeeHistory($kode, $days);

        ApiResponse::success($history, "History for employee {$kode}");
    }

    /**
     * GET /api/absen/stats
     * Get attendance statistics
     */
    public function stats()
    {
        $token = $_GET['token'] ?? '';
        $tokenCheck = AuthMiddleware::validateToken($token);
        
        if (!$tokenCheck['valid']) {
            ApiResponse::error($tokenCheck['message'], 403);
        }

        $month = $_GET['month'] ?? date('Y-m');
        $stats = $this->absensiService->getMonthlyStats($month);

        ApiResponse::success($stats, 'Statistics retrieved');
    }

    /**
     * GET /api/absen/export
     * Export attendance data to CSV
     */
    public function export()
    {
        $token = $_GET['token'] ?? '';
        $tokenCheck = AuthMiddleware::validateToken($token);
        
        if (!$tokenCheck['valid']) {
            ApiResponse::error($tokenCheck['message'], 403);
        }

        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $data = $this->absensiService->getAttendanceHistory($startDate, $endDate);

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=attendance_' . $startDate . '_to_' . $endDate . '.csv');

        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, ['Tanggal', 'Waktu', 'Kode', 'Nama', 'Status', 'Device']);
        
        // Data rows
        foreach ($data as $row) {
            fputcsv($output, [
                date('Y-m-d', strtotime($row['event_time'])),
                date('H:i:s', strtotime($row['event_time'])),
                $row['employee_code'],
                $row['employee_name'],
                $row['status'],
                $row['device_code'] ?? 'N/A'
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * GET /api/absen/check/{code}
     * Check if employee has clocked in today
     */
    public function check()
    {
        $token = $_GET['token'] ?? '';
        $tokenCheck = AuthMiddleware::validateToken($token);
        
        if (!$tokenCheck['valid']) {
            ApiResponse::error($tokenCheck['message'], 403);
        }

        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/\/check\/([^\/\?]+)/', $uri, $matches);
        $kode = $matches[1] ?? null;

        if (!$kode) {
            ApiResponse::error('Employee code is required', 400);
        }

        $status = $this->absensiService->checkTodayStatus($kode);
        ApiResponse::success($status, 'Status checked');
    }
}
