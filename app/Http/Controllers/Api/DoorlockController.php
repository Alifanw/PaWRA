<?php

namespace App\Http\Controllers\Api;

use App\Models\DoorlockLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class DoorlockController extends Controller
{
    /**
     * Verify API key from request
     */
    private function verifyApiKey(Request $request)
    {
        $apiKey = $request->header('X-API-KEY');
        $expectedKey = env('DOORLOCK_API_KEY', 'SECURE_KEY_IGASAR');
        return $apiKey === $expectedKey;
    }

    /**
     * Log RFID scan to database
     * POST /api/attendance/doorlock/scan
     */
    public function scan(Request $request)
    {
        // Verify API key
        if (!$this->verifyApiKey($request)) {
            Log::warning('Unauthorized doorlock scan attempt', ['ip' => $request->ip()]);
            return response()->json(['error' => 'unauthorized'], 401);
        }

        try {
            $validated = $request->validate([
                'device_id' => 'required|string|max:50',
                'rfid' => 'nullable|string|max:100',
                'rfid_code' => 'nullable|string|max:100',
                'action' => 'required|in:scan,open,close',
                'status' => 'sometimes|in:success,failed,pending',
                'trigger_by' => 'sometimes|string|max:50',
                'notes' => 'nullable|string|max:500',
            ]);

            // Use rfid if provided, otherwise rfid_code
            $rfidValue = $validated['rfid'] ?? $validated['rfid_code'] ?? null;

            // Log the scan
            $log = DoorlockLog::create([
                'device_id' => $validated['device_id'],
                'rfid' => $rfidValue,
                'rfid_code' => $rfidValue,
                'action' => $validated['action'],
                'status' => $validated['status'] ?? 'success',
                'trigger_by' => $validated['trigger_by'] ?? 'api',
                'notes' => $validated['notes'] ?? null,
                'ip_address' => $request->ip(),
            ]);

            Log::info('Doorlock scan logged', ['device_id' => $validated['device_id'], 'rfid' => $rfidValue]);

            return response()->json([
                'message' => 'Scan logged successfully',
                'log_id' => $log->id,
                'timestamp' => $log->created_at,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'validation_failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Doorlock scan error: ' . $e->getMessage());
            return response()->json(['error' => 'server_error'], 500);
        }
    }

    /**
     * Manual door open trigger
     * POST /api/attendance/doorlock/door/open
     */
    public function doorOpen(Request $request)
    {
        if (!$this->verifyApiKey($request)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        try {
            $validated = $request->validate([
                'device_id' => 'required|string|max:50',
                'reason' => 'nullable|string|max:200',
                'duration' => 'nullable|integer|min:1',
            ]);

            // Log the manual opening
            $log = DoorlockLog::create([
                'device_id' => $validated['device_id'],
                'action' => 'open',
                'status' => 'success',
                'trigger_by' => 'manual',
                'door_duration' => $validated['duration'] ?? env('DOORLOCK_OPEN_DURATION', 5),
                'notes' => $validated['reason'] ?? 'Manual door trigger via API',
                'ip_address' => $request->ip(),
            ]);

            Log::info('Manual door open triggered', ['device_id' => $validated['device_id']]);

            return response()->json([
                'message' => 'Door opened',
                'log_id' => $log->id,
                'duration' => $validated['duration'] ?? env('DOORLOCK_OPEN_DURATION', 5),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Door open error: ' . $e->getMessage());
            return response()->json(['error' => 'server_error'], 500);
        }
    }

    /**
     * Get device status
     * GET /api/attendance/doorlock/status?device_id=RPI-01
     */
    public function status(Request $request)
    {
        try {
            $deviceId = $request->query('device_id', 'RPI-01');

            // Get last scan
            $lastScan = DoorlockLog::where('device_id', $deviceId)
                ->whereNotNull('rfid')
                ->orderBy('created_at', 'desc')
                ->first();

            // Count scans today
            $scansToday = DoorlockLog::where('device_id', $deviceId)
                ->whereDate('created_at', now())
                ->count();

            // Count successful scans
            $successfulScans = DoorlockLog::where('device_id', $deviceId)
                ->where('status', 'success')
                ->whereDate('created_at', now())
                ->count();

            return response()->json([
                'device_id' => $deviceId,
                'status' => 'online',
                'last_scan' => $lastScan?->rfid,
                'last_scan_at' => $lastScan?->created_at,
                'scans_today' => $scansToday,
                'successful_scans_today' => $successfulScans,
                'uptime' => 'N/A',
            ]);

        } catch (\Exception $e) {
            Log::error('Status check error: ' . $e->getMessage());
            return response()->json(['error' => 'server_error'], 500);
        }
    }

    /**
     * Get doorlock logs
     * GET /api/attendance/doorlock/logs?device_id=RPI-01&limit=50&hours=24
     */
    public function logs(Request $request)
    {
        try {
            $deviceId = $request->query('device_id', 'RPI-01');
            $limit = intval($request->query('limit', 50));
            $hours = intval($request->query('hours', 24));

            $query = DoorlockLog::where('device_id', $deviceId);

            if ($hours > 0) {
                $query->where('created_at', '>=', now()->subHours($hours));
            }

            $logs = $query->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'device_id' => $deviceId,
                'count' => $logs->count(),
                'hours' => $hours,
                'logs' => $logs->map(function($log) {
                    return [
                        'id' => $log->id,
                        'rfid' => $log->rfid,
                        'action' => $log->action,
                        'status' => $log->status,
                        'trigger_by' => $log->trigger_by,
                        'door_duration' => $log->door_duration,
                        'notes' => $log->notes,
                        'ip_address' => $log->ip_address,
                        'created_at' => $log->created_at,
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            Log::error('Logs retrieval error: ' . $e->getMessage());
            return response()->json(['error' => 'server_error'], 500);
        }
    }

    /**
     * Get doorlock statistics
     * GET /api/attendance/doorlock/stats?device_id=RPI-01
     */
    public function stats(Request $request)
    {
        try {
            $deviceId = $request->query('device_id', 'RPI-01');

            // Scans by day (last 7 days)
            $scansByDay = DoorlockLog::where('device_id', $deviceId)
                ->where('created_at', '>=', now()->subDays(7))
                ->whereNotNull('rfid')
                ->get()
                ->groupBy(function ($item) {
                    return $item->created_at->format('Y-m-d');
                })
                ->map(function ($group) {
                    return $group->count();
                });

            // Total scans (with rfid only)
            $totalScans = DoorlockLog::where('device_id', $deviceId)
                ->whereNotNull('rfid')
                ->count();

            // Success rate
            $successfulScans = DoorlockLog::where('device_id', $deviceId)
                ->where('status', 'success')
                ->whereNotNull('rfid')
                ->count();
            $successRate = $totalScans > 0 ? round(($successfulScans / $totalScans) * 100, 2) : 0;

            // Unique RFIDs
            $uniqueRfids = DoorlockLog::where('device_id', $deviceId)
                ->whereNotNull('rfid')
                ->distinct('rfid')
                ->count('rfid');

            return response()->json([
                'device_id' => $deviceId,
                'total_scans' => $totalScans,
                'successful_scans' => $successfulScans,
                'failed_scans' => $totalScans - $successfulScans,
                'success_rate' => $successRate . '%',
                'unique_rfids' => $uniqueRfids,
                'scans_by_day' => $scansByDay,
                'period' => 'last 7 days',
            ]);

        } catch (\Exception $e) {
            Log::error('Stats error: ' . $e->getMessage());
            return response()->json(['error' => 'server_error'], 500);
        }
    }
}
