<?php

require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/Logger.php';

class DoorlockService
{
    private $logger;
    private $apiUrl;
    private $token;

    public function __construct()
    {
        $this->logger = new Logger(Config::DOORLOCK_LOG);
        $this->apiUrl = Config::DOORLOCK_API_URL;
        $this->token = Config::DOORLOCK_TOKEN;
    }

    /**
     * Trigger Raspberry Pi doorlock
     */
    public function triggerOpen($kode, $status, $delay = null)
    {
        if ($delay === null) {
            $delay = Config::DOORLOCK_DEFAULT_DELAY;
        }

        $payload = [
            'token' => $this->token,
            'kode' => $kode,
            'status' => $status,
            'delay' => $delay
        ];

        $this->logger->info("Triggering doorlock", $payload);

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ]);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = [
            'success' => ($httpCode == 200 && empty($error)),
            'http_code' => $httpCode,
            'response' => $response,
            'error' => $error
        ];

        if ($result['success']) {
            $this->logger->info("Doorlock triggered successfully", [
                'kode' => $kode,
                'status' => $status,
                'http_code' => $httpCode
            ]);
        } else {
            $this->logger->error("Doorlock trigger failed", [
                'kode' => $kode,
                'status' => $status,
                'http_code' => $httpCode,
                'error' => $error,
                'response' => substr($response ?: '', 0, 200)
            ]);
        }

        return $result;
    }

    /**
     * Check if doorlock is reachable
     */
    public function checkConnection()
    {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode > 0;
    }
}
