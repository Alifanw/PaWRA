<?php

require_once __DIR__ . '/../config/Config.php';

class AuthMiddleware
{
    public static function validateToken($token)
    {
        if (empty($token)) {
            return [
                'valid' => false,
                'message' => 'Token is required'
            ];
        }

        if ($token !== Config::API_TOKEN) {
            return [
                'valid' => false,
                'message' => 'Invalid token'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Token valid'
        ];
    }

    public static function checkRateLimit($kode, $db)
    {
        try {
            // Set timezone to match MySQL
            date_default_timezone_set('Asia/Jakarta');
            
            $query = "SELECT event_time FROM attendance_logs 
                      WHERE employee_id = (SELECT id FROM employees WHERE code = :kode LIMIT 1)
                      ORDER BY event_time DESC LIMIT 1";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':kode', $kode);
            $stmt->execute();
            
            $lastLog = $stmt->fetch();
            
            if ($lastLog) {
                $lastTime = strtotime($lastLog['event_time']);
                $now = time();
                $diff = $now - $lastTime;
                
                if ($diff < Config::RATE_LIMIT_SECONDS) {
                    return [
                        'allowed' => false,
                        'message' => 'Terlalu cepat. Tunggu ' . (Config::RATE_LIMIT_SECONDS - $diff) . ' detik.',
                        'wait_seconds' => Config::RATE_LIMIT_SECONDS - $diff
                    ];
                }
            }
            
            return [
                'allowed' => true,
                'message' => 'Rate limit OK'
            ];
            
        } catch (PDOException $e) {
            return [
                'allowed' => true,
                'message' => 'Rate limit check skipped'
            ];
        }
    }
}
