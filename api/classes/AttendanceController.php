<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/EmployeeModel.php';
require_once __DIR__ . '/AttendanceModel.php';
require_once __DIR__ . '/DoorService.php';
require_once __DIR__ . '/ApiResponse.php';

class AttendanceController
{
    private $db;
    private $employeeModel;
    private $attendanceModel;
    private $doorService;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->employeeModel = new EmployeeModel($this->db);
        $this->attendanceModel = new AttendanceModel($this->db);
        $this->doorService = new DoorService();
    }

    /**
     * Proses absensi dengan validasi lengkap
     */
    public function processAttendance($kode, $status, $deviceCode = 'KIOSK-01')
    {
        try {
            // 1. Validasi input
            if (empty($kode) || empty($status)) {
                ApiResponse::error('Kode dan status wajib diisi');
            }

            // 2. Cek apakah employee terdaftar
            $employee = $this->employeeModel->findByCode($kode);
            if (!$employee) {
                $this->log("Kode employee tidak ditemukan: $kode");
                ApiResponse::error('Kode karyawan tidak ditemukan');
            }

            $employeeId = $employee['id'];
            $employeeName = $employee['name'];

            // 3. Ambil log terakhir hari ini
            $lastLog = $this->attendanceModel->getLastLogToday($employeeId);
            $lastStatus = $lastLog ? $lastLog['status'] : null;

            $this->log("Proses absensi: Kode=$kode, Nama=$employeeName, Status=$status, LastStatus=" . ($lastStatus ?? 'NULL'));

            // 4. Validasi logika absensi (sama seperti kode lama)
            $validationResult = $this->validateAttendanceLogic($status, $lastStatus, $employeeName);
            if (!$validationResult['valid']) {
                $this->log("Validasi gagal: " . $validationResult['message']);
                ApiResponse::error($validationResult['message']);
            }

            // 5. Simpan ke attendance_logs
            $inserted = $this->attendanceModel->insert($employeeId, $deviceCode, $status, $employeeName);
            
            if (!$inserted) {
                throw new Exception('Gagal menyimpan log absensi');
            }

            $this->log("Absensi $status untuk $employeeName berhasil disimpan ke DB");

            // 6. Insert ke door_events
            $this->doorService->insertDoorEvent($this->db, $deviceCode, $status);

            // 7. Trigger doorlock (jika status valid untuk buka pintu)
            $doorResult = null;
            if (in_array($status, ['Masuk', 'Pulang', 'Lembur', 'Pulang Lembur'])) {
                $this->log("Mencoba trigger doorlock untuk status $status...");
                $doorResult = $this->doorService->triggerOpen($kode, $status, 3);
                
                if ($doorResult['success']) {
                    $this->log("Doorlock API triggered successfully");
                } else {
                    $this->log("ERROR: Door trigger failed - " . ($doorResult['error'] ?? 'Unknown error'));
                }
            }

            // 8. Response sukses
            ApiResponse::success([
                'employee_name' => $employeeName,
                'employee_code' => $kode,
                'status' => $status,
                'time' => date('Y-m-d H:i:s'),
                'door_triggered' => $doorResult ? $doorResult['success'] : false
            ], "Absensi $status berhasil untuk $employeeName");

        } catch (Exception $e) {
            $this->log("ERROR: " . $e->getMessage());
            ApiResponse::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Validasi logika absensi - sama seperti kode PHP lama
     */
    private function validateAttendanceLogic($status, $lastStatus, $nama)
    {
        switch ($status) {
            case 'Masuk':
                if ($lastStatus === 'Masuk') {
                    return ['valid' => false, 'message' => "$nama sudah absen masuk hari ini."];
                }
                break;

            case 'Pulang':
                if (!$lastStatus) {
                    return ['valid' => false, 'message' => "$nama belum absen masuk hari ini."];
                }
                if (!in_array($lastStatus, ['Masuk', 'Lembur'])) {
                    return ['valid' => false, 'message' => "$nama belum absen masuk atau lembur sebelum pulang."];
                }
                if ($lastStatus === 'Pulang') {
                    return ['valid' => false, 'message' => "$nama sudah absen pulang hari ini."];
                }
                break;

            case 'Lembur':
                if (!$lastStatus || $lastStatus !== 'Pulang') {
                    return ['valid' => false, 'message' => "$nama harus absen pulang terlebih dahulu sebelum lembur."];
                }
                break;

            case 'Pulang Lembur':
                if (!$lastStatus || $lastStatus !== 'Lembur') {
                    return ['valid' => false, 'message' => "$nama belum absen lembur hari ini."];
                }
                break;

            default:
                return ['valid' => false, 'message' => 'Status absensi tidak valid.'];
        }

        return ['valid' => true, 'message' => 'Validasi berhasil'];
    }

    /**
     * Logging untuk debugging
     */
    private function log($message)
    {
        $logFile = __DIR__ . '/../../storage/logs/attendance_debug.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        error_log("[" . date('Y-m-d H:i:s') . "] $message\n", 3, $logFile);
    }
}
