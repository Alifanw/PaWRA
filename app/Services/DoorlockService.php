<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DoorlockService
{
    /**
     * Raspberry Pi API Configuration
     */
    private string $apiUrl;
    private string $apiToken;
    private int $timeout;
    private bool $enabled;

    public function __construct()
    {
        // Langsung ke Pi tanpa SSH tunnel (lebih reliable)
        $this->apiUrl = env('DOORLOCK_API_URL', 'http://192.168.30.108:5000');
        $this->apiToken = env('DOORLOCK_API_TOKEN', 'SECURE_KEY_IGASAR');
        $this->timeout = (int) env('DOORLOCK_TIMEOUT', 5);
        $this->enabled = env('DOORLOCK_ENABLED', true);
    }

    /**
     * Trigger doorlock untuk absensi
     * 
     * @param string $employeeCode Kode karyawan
     * @param string $status Status absensi (masuk, pulang, lembur, pulang_lembur)
     * @param int $delay Delay auto-lock dalam detik
     * @return array Result dengan status, message, dan response
     */
    public function triggerForAttendance(string $employeeCode, string $status, int $delay = 5): array
    {
        if (!$this->enabled) {
            Log::info('Doorlock disabled in config', [
                'employee_code' => $employeeCode,
                'status' => $status
            ]);
            return [
                'success' => false,
                'message' => 'Doorlock disabled',
                'triggered' => false
            ];
        }

        // Hanya trigger untuk status tertentu
        if (!in_array($status, ['masuk', 'pulang', 'lembur', 'pulang_lembur'])) {
            return [
                'success' => false,
                'message' => 'Status tidak memicu doorlock',
                'triggered' => false
            ];
        }

        return $this->openDoor($delay, [
            'employee_code' => $employeeCode,
            'status' => $status,
            'triggered_by' => 'attendance_system'
        ]);
    }

    /**
     * Buka pintu manual (dari admin panel)
     * 
     * @param int $delay Delay auto-lock dalam detik
     * @return array Result dengan status dan response
     */
    public function openDoorManual(int $delay = 5): array
    {
        return $this->openDoor($delay, [
            'triggered_by' => 'manual_admin'
        ]);
    }

    /**
     * Trigger buka pintu ke Raspberry Pi
     * 
     * @param int $delay Delay dalam detik (1-30)
     * @param array $metadata Metadata tambahan untuk logging
     * @return array
     */
    private function openDoor(int $delay = 5, array $metadata = []): array
    {
        $delay = max(1, min($delay, 30)); // Batasi 1-30 detik
        $endpoint = $this->apiUrl . '/door/open';

        $payload = [
            'token' => $this->apiToken,
            'delay' => $delay
        ];

        $startTime = microtime(true);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, $payload);

            $duration = round((microtime(true) - $startTime) * 1000, 2); // ms

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Doorlock triggered successfully', array_merge([
                    'endpoint' => $endpoint,
                    'http_code' => $response->status(),
                    'delay_used' => $delay,
                    'duration_ms' => $duration,
                    'response' => $data
                ], $metadata));

                return [
                    'success' => true,
                    'message' => 'Pintu dibuka untuk ' . $delay . ' detik',
                    'triggered' => true,
                    'delay' => $delay,
                    'response' => $data
                ];
            }

            // HTTP error (4xx, 5xx)
            Log::error('Doorlock API returned error', array_merge([
                'endpoint' => $endpoint,
                'http_code' => $response->status(),
                'duration_ms' => $duration,
                'response_body' => $response->body()
            ], $metadata));

            return [
                'success' => false,
                'message' => 'Doorlock API error: ' . $response->status(),
                'triggered' => false,
                'error' => $response->body()
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Doorlock connection failed', array_merge([
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'timeout' => $this->timeout
            ], $metadata));

            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke Raspberry Pi',
                'triggered' => false,
                'error' => $e->getMessage()
            ];

        } catch (\Exception $e) {
            Log::error('Doorlock unexpected error', array_merge([
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], $metadata));

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'triggered' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cek status doorlock
     * 
     * @return array
     */
    public function getStatus(): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'Doorlock disabled',
                'data' => null
            ];
        }

        $endpoint = $this->apiUrl . '/door/status';

        try {
            $response = Http::timeout($this->timeout)->get($endpoint);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Status retrieved',
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get status',
                'data' => null
            ];

        } catch (\Exception $e) {
            Log::error('Doorlock status check failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Health check Raspberry Pi API
     * 
     * @return array
     */
    public function healthCheck(): array
    {
        if (!$this->enabled) {
            return [
                'healthy' => false,
                'message' => 'Doorlock disabled in config',
                'data' => null
            ];
        }

        $endpoint = $this->apiUrl . '/health';

        try {
            $response = Http::timeout($this->timeout)->get($endpoint);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'healthy' => true,
                    'message' => 'Raspberry Pi online',
                    'data' => $data
                ];
            }

            return [
                'healthy' => false,
                'message' => 'API returned error: ' . $response->status(),
                'data' => null
            ];

        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}
