<?php

class DoorService
{
    private $piApiUrl;
    private $piApiToken;
    private $logFile;

    public function __construct()
    {
        // Konfigurasi Door API - sesuaikan dengan setup Anda
        $this->piApiUrl = 'http://127.0.0.1:10000/door/open';
        $this->piApiToken = 'SECURE_KEY_IGASAR';
        $this->logFile = __DIR__ . '/../../storage/logs/doorlock_error.log';
    }

    /**
     * Trigger Raspberry Pi doorlock untuk membuka pintu
     */
    public function triggerOpen($kode, $status, $delay = 3)
    {
        $payload = json_encode([
            'token' => $this->piApiToken,
            'kode' => $kode,
            'status' => $status,
            'delay' => $delay
        ]);

        $ch = curl_init($this->piApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log hasil trigger
        $this->logDoorTrigger($kode, $status, $httpCode, $response, $error);

        return [
            'success' => ($httpCode == 200 && empty($error)),
            'http_code' => $httpCode,
            'response' => $response,
            'error' => $error
        ];
    }

    /**
     * Insert door event ke database
     */
    public function insertDoorEvent($db, $deviceCode, $status)
    {
        try {
            $query = "INSERT INTO door_events (device_code, status, event_time, processed) 
                      VALUES (:device_code, :status, NOW(), 0)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':device_code', $deviceCode);
            $stmt->bindParam(':status', $status);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Error insert door_events: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log aktivitas door trigger
     */
    private function logDoorTrigger($kode, $status, $httpCode, $response, $error)
    {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logMessage = sprintf(
            "[%s] Door trigger -> %s | Kode: %s | Status: %s | HTTP: %s | Response: %s | Error: %s\n",
            date('Y-m-d H:i:s'),
            $this->piApiUrl,
            $kode,
            $status,
            $httpCode,
            substr($response ?: '', 0, 400),
            $error ?: 'none'
        );

        error_log($logMessage, 3, $this->logFile);
    }
}
